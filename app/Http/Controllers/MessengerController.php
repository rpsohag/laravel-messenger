<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Models\Message;
use App\Events\Message as EventMessage;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessengerController extends Controller
{
    use FileUploadTrait;
    public function index()
    {
        $favouriteList = Favourite::with('users:id,name,avatar')->where('user_id', Auth::user()->id)->get();
        return view('messenger.index', ['favouriteList' => $favouriteList]);
    }

    public function userSearch(Request $request)
    {
        $getRecords = null;
        $queryInput = $request['query'];
        $searchResults = User::where('id', '!=', Auth::user()->id)
            ->where('name', 'LIKE', "%{$queryInput}%")
            ->orWhere('username', 'LIKE', "%{$queryInput}%")
            ->paginate(10);

        if ($searchResults->total() < 1) {
            $getRecords .= "<p class='text-center'>No Records Found! </p>";
        }

        foreach ($searchResults as $result) {
            $getRecords .= view('messenger.components.search_items', compact('result'))->render();
        }

        return response()->json([
            'results' => $getRecords,
            'last_page' => $searchResults->lastPage()
        ]);
    }

    public function fetchIdInfo(Request $request)
    {
        $user = User::where('id', $request['id'])->first();
        $favourite = Favourite::where(['user_id' => Auth::user()->id, 'favourite_id'=> $user->id])->exists();
        $sharedAttachments = Message::where('from_id', Auth::user()->id)->where('to_id', $request->id)->whereNotNull('attachment')
        ->orWhere('from_id', $request->id)->where('to_id', Auth::user()->id)->whereNotNull('attachment')
        ->latest()->get();

        $contents = "";

        foreach($sharedAttachments as $photo){
            $contents .= view('messenger.components.gallery_item',['photo' => json_decode($photo->attachment, true)])->render();
        }


        return response()->json([
            'user' => $user,
            'favourite' => $favourite,
            'sharedAttachments' => $contents
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            // 'message' => ['required'],
            'temporaryMsgId' => ['required'],
            'attachment' => ['nullable', 'max:4096']
        ]);

        $attachmentPath = $this->uploadFile($request, 'attachment');

        $message = new Message();
        $message->from_id = Auth::user()->id;
        $message->to_id = $request->id;
        $message->body = $request->message;
        if ($attachmentPath) $message->attachment = json_encode($attachmentPath);
        $message->save();

        // broadcast event

        EventMessage::dispatch($message);

        return response()->json([
            'message' => $message->attachment ?  $this->messageCard($message->id, $request->message, $message->from_id, $message->created_at, $message->attachment) : $this->messageCard($message->id, $request->message, $message->from_id, $message->created_at),
            'tempID' => $request->temporaryMsgId
        ]);
    }

    public function messageCard($id, $message, $from_id, $created_at, $attachment = null)
    {
        return view('messenger.components.message_card', ['id' => $id, 'message' => $message, 'from_id' => $from_id, 'created_at' => $created_at, 'attachment' => json_decode($attachment)])->render();
    }

    public function fetchMessages(Request $request)
    {
        $messages = Message::where('from_id', Auth::user()->id)->where('to_id', $request->id)
            ->orWhere('from_id', $request->id)->where('to_id', Auth::user()->id)
            ->latest()->paginate(13);
        $response = [
            'last_page' => $messages->lastPage(),
            'last_message' => $messages->last(),
            'messages' => ''
        ];

        if (count($messages) < 1) {
            $response["messages"] = "<div class='d-flex justify-content-center no_message align-items-center h-100'><p>Say Hi and start conversation!</p></div>";
            return response()->json($response);
        }

        $allMessages = '';
        foreach ($messages->reverse() as $message) {
            $allMessages .= $message->attachment ?  $this->messageCard($message->id, $message->body, $message->from_id, $message->created_at, $message->attachment) : $this->messageCard($message->id, $message->body, $message->from_id, $message->created_at);
        }

        $response["messages"] = $allMessages;

        return response()->json($response);
    }

    public function fetchContacts(Request $request)
    {
        $users = Message::join("users", function($join){
            $join->on('messages.from_id', '=', 'users.id')
            ->orOn('messages.to_id', '=', 'users.id');
        })
        ->where(function($q){
            $q->where('messages.from_id', Auth::user()->id)
            ->orWhere('messages.to_id', Auth::user()->id);
        })
        ->where('users.id', '!=', Auth::user()->id)
        ->select('users.*', DB::raw('MAX(messages.created_at) max_created_at'))
        ->orderBy("max_created_at", 'desc')
        ->groupBy('users.id')
        ->paginate(7);

        $contacts = "";
        if(count($users) > 0){
            foreach($users as $user){
                $contacts .= $this->getContactItem($user);
            }
        }else{
            $contacts = "<p class='text-center no_contact'>Your contact list is empty!</p>";
        }

        return response()->json([
            'contacts' => $contacts,
            'last_page' => $users->lastPage()
        ]);
    }

    function getContactItem($user){
        $lastMessage = Message::where('from_id', Auth::user()->id)->where('to_id', $user->id)
        ->orWhere('from_id', $user->id)->where('to_id', Auth::user()->id)
        ->latest()->first();
        $unseenCounter = Message::where('from_id', $user->id)->where('to_id', Auth::user()->id)->where('seen', 0)->count();

        return view('messenger.components.contact_list_item', ['lastMessage' => $lastMessage, 'unseenCounter' => $unseenCounter, 'user'=> $user])->render();
    }

    public function updateContacts(Request $request){
        $user = User::where('id', $request->user_id)->first();
        if(!$user){
            return response()->json([
                "message" => "User not found"
            ], 401);
        }
        
        $contactItem = $this->getContactItem($user);
        return response()->json([
            "contact_item" => $contactItem
        ]);
    }

    public function makeSeen(Request $request){
        Message::where('from_id', $request->id)
        ->where('to_id', Auth::user()->id)
        ->where('seen', 0)->update(['seen' => 1]);

        return true;

    }

    public function favourite(Request $request){
        $query = Favourite::where(['user_id' => Auth::user()->id, 'favourite_id' => $request->id]);
        $favouriteStatus = $query->exists();
        if(!$favouriteStatus){
            $star = new Favourite();
            $star->user_id = Auth::user()->id;
            $star->favourite_id = $request->id;
            $star->save();
            return response(["status" => "added"]);
        }else{
            $query->delete();
            return response(["status" => "removed"]);
        }
    }

    public function deleteMessage(Request $request){
        $message = Message::findOrFail($request->message_id);
        if($message->from_id == Auth::user()->id){
            $message->delete();
            return response()->json([
                "id" => $request->message_id
            ], 200);
        }
        return ;
    }
}
