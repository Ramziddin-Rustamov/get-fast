<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastPush;
use App\Models\BroadcastMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    /**
     * Yuborilgan e'lonlar ro'yxati.
     */
    public function index()
    {
        $broadcasts = BroadcastMessage::latest()->paginate(10);

        return view('admin-views.broadcasts.index', compact('broadcasts'));
    }

    /**
     * Yangi e'lon yozish formasi.
     */
    public function create()
    {
        return view('admin-views.broadcasts.create');
    }

    /**
     * E'lonni saqlash va push jo'natishni navbatga qo'yish.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // uz — majburiy (asosiy til)
            'title_uz' => ['nullable', 'string', 'max:255'],
            'body_uz'  => ['required', 'string', 'max:2000'],
            // ru / en — ixtiyoriy
            'title_ru' => ['nullable', 'string', 'max:255'],
            'body_ru'  => ['nullable', 'string', 'max:2000'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'body_en'  => ['nullable', 'string', 'max:2000'],
            'audience' => ['required', 'in:all,driver,client'],
        ]);

        // Faqat matni to'ldirilgan tillarni saqlaymiz
        $translations = [];
        foreach (['uz', 'ru', 'en'] as $lang) {
            if (! empty($data["body_{$lang}"])) {
                $translations[$lang] = [
                    'title' => $data["title_{$lang}"] ?? null,
                    'body'  => $data["body_{$lang}"],
                ];
            }
        }

        $broadcast = BroadcastMessage::create([
            'title'        => $data['title_uz'] ?? null, // asosiy (fallback) nusxa
            'body'         => $data['body_uz'],
            'translations' => $translations,
            'audience'     => $data['audience'],
            'sender_id'    => Auth::id(),
            'status'       => 'pending',
        ]);

        SendBroadcastPush::dispatch($broadcast->id);

        return redirect()
            ->route('broadcasts.index')
            ->with('success', 'E\'lon navbatga qo\'yildi va foydalanuvchilarga yuborilmoqda.');
    }

    /**
     * Bitta e'lonni ko'rish.
     */
    public function show($id)
    {
        $broadcast = BroadcastMessage::findOrFail($id);

        return view('admin-views.broadcasts.show', compact('broadcast'));
    }

    /**
     * E'lonni o'chirish.
     */
    public function destroy($id)
    {
        BroadcastMessage::findOrFail($id)->delete();

        return redirect()
            ->route('broadcasts.index')
            ->with('success', 'E\'lon o\'chirildi.');
    }
}
