<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserMessageController extends Controller
{
    public function send(Request $request, Chat $chat)
    {
        abort_if($chat->status === 'closed', 403);

        $message = $chat->messages()->create([
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'is_read_by_user' => true,
            'is_read_by_support' => false,
        ]);

        $chat->update([
            'last_message_id' => $message->id
        ]);

        // broadcast(new NewMessage($message))->toOthers();

        return response()->json($message);
    }
}
