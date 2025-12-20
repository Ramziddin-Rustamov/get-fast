<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Chat;
class SupportMessageController extends Controller
{
    public function send(Request $request, Chat $chat)
    {
        $message = $chat->messages()->create([
            'sender_type' => 'support',
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'is_read_by_support' => true,
            'is_read_by_user' => false,
        ]);

        $chat->update([
            'last_message_id' => $message->id
        ]);

        // broadcast(new NewMessage($message))->toOthers();

        return response()->json($message);
    }
}
