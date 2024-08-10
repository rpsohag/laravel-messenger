<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    use FileUploadTrait;
    public function index(){
        return view('messenger.index');
    }

    public function userSearch(Request $request){
        $getRecords = null;
        $queryInput = $request['query'];
        $searchResults = User::where('id', '!=', Auth::user()->id)
        ->where('name', 'LIKE', "%{$queryInput}%")
        ->orWhere('username', 'LIKE', "%{$queryInput}%")
        ->paginate(10);

        if($searchResults->total() < 1){
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

    public function fetchIdInfo(Request $request){
        $user = User::where('id', $request['id'])->first();
        return response()->json([
            'user' => $user
        ]);
    }

    public function sendMessage(Request $request){
        $request->validate([
            'id' => ['required'],
            // 'message' => ['required'],
            'temporaryMsgId' => ['required'],
            'attachment' => ['nullable', 'max:1024']
        ]);

        $attachmentPath = $this->uploadFile($request, 'attachment');

        $message = new Message();
        $message->from_id = Auth::user()->id;
        $message->to_id = $request->id;
        $message->body = $request->message;
        if($attachmentPath) $message->attachment = json_encode($attachmentPath);
        $message->save();

        return response()->json([
            'message' => $message->attachment ?  $this->messageCard($message->id, $request->message, $message->from_id, $message->created_at, $message->attachment) : $this->messageCard($message->id, $request->message, $message->from_id, $message->created_at) ,
            'tempID' => $request->temporaryMsgId
        ]);
    }

    public function messageCard($id, $message, $from_id, $created_at, $attachment = null){
        return view('messenger.components.message_card', ['id' => $id, 'message' => $message, 'from_id' => $from_id, 'created_at' => $created_at, 'attachment' => json_decode($attachment)])->render();
    }

    public function fetchMessages(Request $request){
        $messages = Message::where('from_id', Auth::user()->id)->where('to_id', $request->id)
                            ->orWhere('from_id', $request->id)->where('to_id', Auth::user()->id)
                            ->latest()->paginate(13);
        $response = [
            'last_page' => $messages->lastPage(),
            'last_message' => $messages->last(),
            'messages' => ''
        ];

        if(count($messages) < 1){
            $response["messages"] = "<div class='d-flex justify-content-center align-items-center h-100'><p>Say Hi and start conversation!</p></div>";
            return response()->json($response);
        }

        $allMessages = '';
        foreach($messages->reverse() as $message){
            $allMessages .= $message->attachment ?  $this->messageCard($message->id, $message->body, $message->from_id, $message->created_at, $message->attachment) : $this->messageCard($message->id, $message->body, $message->from_id, $message->created_at);
        }

        $response["messages"] = $allMessages;

        return response()->json($response);

    }
}
