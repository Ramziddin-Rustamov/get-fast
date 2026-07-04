<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegalController extends Controller
{
    private const LANGS = ['uz', 'ru', 'en'];

    private const TITLES = [
        'terms' => [
            'uz' => 'Foydalanish shartlari',
            'ru' => 'Условия использования',
            'en' => 'Terms & Conditions',
        ],
        'privacy' => [
            'uz' => 'Maxfiylik siyosati',
            'ru' => 'Политика конфиденциальности',
            'en' => 'Privacy Policy',
        ],
        'rules' => [
            'uz' => 'Qonun-qoidalar va foydalanish tartibi',
            'ru' => 'Правила и порядок использования',
            'en' => 'Rules & Usage',
        ],
    ];

    public function terms(Request $request)
    {
        return $this->render('terms', $request);
    }

    public function privacy(Request $request)
    {
        return $this->render('privacy', $request);
    }

    public function rules(Request $request)
    {
        return $this->render('rules', $request);
    }

    private function render(string $type, Request $request)
    {
        // Til: ?lang= > ilova lokali > uz
        $lang = $request->query('lang', app()->getLocale());
        if (!in_array($lang, self::LANGS, true)) {
            $lang = 'uz';
        }

        $path = resource_path("legal/{$type}.{$lang}.md");
        if (!is_file($path)) {
            $lang = 'uz';
            $path = resource_path("legal/{$type}.uz.md");
        }

        $html = Str::markdown(file_get_contents($path));

        return view('legal.show', [
            'type'  => $type,
            'lang'  => $lang,
            'title' => self::TITLES[$type][$lang] ?? self::TITLES[$type]['uz'],
            'html'  => $html,
        ]);
    }
}
