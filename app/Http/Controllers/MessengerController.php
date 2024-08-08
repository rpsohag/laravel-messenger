<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
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
}
