<?php

namespace App\Console\Commands;

use App\Services\V1\FcmService;
use Illuminate\Console\Command;
use Throwable;

class TestFcmCommand extends Command
{
    /**
     * Token bermasangiz — faqat Firebase ulanishini (credentials) tekshiradi.
     * Token bersangiz — o'sha qurilmaga sinov push yuboradi.
     *
     * Misol:
     *   php artisan fcm:test
     *   php artisan fcm:test "DEVICE_FCM_TOKEN"
     */
    protected $signature = 'fcm:test {token? : Sinov yuboriladigan device token}';

    protected $description = 'Firebase FCM sozlamasini va push yuborishni tekshiradi';

    public function handle(FcmService $fcm): int
    {
        $token = $this->argument('token');

        // 1-bosqich: agar token berilmasa, faqat credentials/ulanishни sinaymiz.
        if (! $token) {
            $this->info('1-bosqich: Firebase credentials tekshirilmoqda...');
            try {
                // Yolg'on token bilan yuboramiz — muvaffaqiyat kutmaymiz, faqat
                // access token olinsa credentials to'g'ri degani.
                $fcm->sendToToken('test-invalid-token', 'test', 'test');
                $this->info('✅ Credentials va Firebase ulanishi ISHLAYAPTI.');
                $this->line('   (Yolg\'on token rad etildi — bu normal holat.)');
            } catch (Throwable $e) {
                $this->error('❌ Xatolik: ' . $e->getMessage());
                return self::FAILURE;
            }

            $this->newLine();
            $this->comment('Endi haqiqiy telefon tokeni bilan sinang:');
            $this->comment('  php artisan fcm:test "REAL_DEVICE_TOKEN"');
            return self::SUCCESS;
        }

        // 2-bosqich: haqiqiy tokenga push yuboramiz.
        $this->info('Sinov push yuborilmoqda...');
        try {
            $ok = $fcm->sendToToken(
                $token,
                '🔔 Sinov xabari',
                'Bu Get-Fast admin paneldan test push notification.',
                ['type' => 'test']
            );

            if ($ok) {
                $this->info('✅ Push muvaffaqiyatli yuborildi! Telefonni tekshiring.');
                return self::SUCCESS;
            }

            $this->error('❌ Push yuborilmadi. Token noto\'g\'ri yoki eskirgan bo\'lishi mumkin.');
            $this->line('   Batafsil xato uchun: storage/logs/laravel.log');
            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('❌ Xatolik: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
