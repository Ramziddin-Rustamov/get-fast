<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BroadcastMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'translations',
        'audience',
        'sender_id',
        'status',
        'recipients_count',
        'sent_count',
    ];

    protected $casts = [
        'translations' => 'array',
    ];

    /** Standart (fallback) til */
    public const FALLBACK_LANG = 'uz';

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Berilgan til uchun sarlavha+matn qaytaradi. Til topilmasa uz'ga qaytadi.
     *
     * @return array{title: ?string, body: string}
     */
    public function forLang(?string $lang): array
    {
        $t = $this->translations ?? [];

        foreach ([$lang, self::FALLBACK_LANG] as $code) {
            if ($code && ! empty($t[$code]['body'])) {
                return [
                    'title' => $t[$code]['title'] ?? $this->title,
                    'body'  => $t[$code]['body'],
                ];
            }
        }

        // Hech qanday tarjima yo'q bo'lsa — asosiy ustunlar
        return ['title' => $this->title, 'body' => $this->body];
    }
}
