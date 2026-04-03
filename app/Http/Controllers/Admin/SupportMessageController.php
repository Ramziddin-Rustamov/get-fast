<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    /**
     * Admin uchun barcha xabarlar
     */
    public function index()
    {
        $messages = SupportMessage::latest()->paginate(10);

        return view('admin-views.support-messages.index', compact('messages'));
       
    }

    /**
     * Bitta xabarni ko‘rish
     */
    public function show($id)
    {
        $message = SupportMessage::findOrFail($id);

        return view('admin-views.support-messages.show', compact('message'));
    }

    /**
     * Admin javob berishi
     */
    public function markAsAnswered(Request $request, $id)
    {
        $message = SupportMessage::findOrFail($id);
        $message->status = 'answered';
        $message->save();

        return redirect()->back()->with('success', 'Xabaringiz muvaffaqiyatli javob berildi');
    }

    /**
     * O‘chirish (Admin)
     */
    public function destroy($id)
    {
        $message = SupportMessage::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'Xabaringiz muvaffaqiyatli o‘chirildi');
    }
}
