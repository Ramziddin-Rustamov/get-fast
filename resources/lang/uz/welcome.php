<?php

return [
    // Auth (kirgan foydalanuvchi)
    'auth_panel'       => 'Boshqaruv paneli',
    'auth_hello'       => 'Assalomu alaykum',
    'auth_welcome_msg' => 'Hisobingizga muvaffaqiyatli kirdingiz. Yaxshi safar!',
    'logout'           => 'Chiqish',

    // Umumiy
    'download' => 'Yuklab oling',
    'register' => 'Ro‘yxatdan o‘tish',
    'login'    => 'Kirish',
    'app'      => 'Ilova',
    'rights'   => 'Barcha huquqlar himoyalangan.',

    // Hero
    'hero_badge'        => 'Butun O‘zbekiston va Markaziy Osiyo bo‘ylab',
    'hero_title_before' => 'Bir yo‘nalish — ',
    'hero_title_accent' => 'birga safar',
    'hero_title_after'  => ', birga tejamkorlik',
    'hero_sub'          => 'ketamiz.com — viloyatlar aro, shaharlar va qishloqlar o‘rtasidagi safarlarni bog‘laydi. Allaqachon yo‘lga chiqayotgan haydovchi bo‘sh o‘rindig‘ini taklif qiladi — yo‘lovchi arzon yetib boradi, haydovchi yoqilg‘i pulini qoplaydi.',
    'tag_save'          => 'Tejamkor',
    'tag_eco'           => 'Ekologik',
    'tag_safe'          => 'Xavfsiz',

    // Trip card
    'planned_trip'  => 'Rejali safar',
    'tomorrow_time' => 'Ertaga 08:00',
    'from'          => 'Qayerdan',
    'to'            => 'Qayerga',
    'trip_from'     => 'Chinor qishlog‘i',
    'trip_to'       => 'Shahar markazi — Universitet',
    'driver'        => 'Haydovchi',
    'passenger'     => 'Yo‘lovchi',
    'teacher_short' => 'Domla',
    'student_short' => 'Talaba',
    'one_seat'      => 'Bir o‘rindiq',

    // Story
    'story_badge' => 'Hayotiy misol',
    'story_title' => 'Domla ham, talaba ham yutadi',
    'story_sub'   => 'Har kuni minglab odam bir xil yo‘nalishda alohida-alohida yo‘l yuradi. ketamiz.com ularni bitta mashinaga birlashtiradi — natijada hamma tejaydi.',
    'scene_route'    => '~18 km · 25 daqiqa',
    'scene_from'     => 'Chinor qishlog‘i',
    'scene_from_sub' => 'Domlaning uyi',
    'scene_to'       => 'Shahar markazi',
    'scene_to_sub'   => 'Universitet',
    'journey' => [
        ['t' => 'Domla yo‘lga chiqadi',    'd' => 'Har kuni uyidan markazga dars berishga boradi.'],
        ['t' => '3 o‘rindiq bo‘sh',        'd' => 'Mashinada joy bor — lekin yolg‘iz ketyapti.'],
        ['t' => 'Ilovada rejalashtiradi',  'd' => 'Yo‘nalish va vaqtni oldindan e’lon qiladi.'],
        ['t' => 'Talaba qo‘shiladi',       'd' => 'Shu yo‘nalishdagi talaba o‘rindiqni band qiladi.'],
    ],
    'without_title' => 'ketamiz.com’siz',
    'without' => [
        '2 ta alohida mashina yo‘lda',
        'Talaba qimmat taksi/transport qidiradi',
        'Domla benzinni yolg‘iz to‘laydi',
        'Ko‘proq tirbandlik va CO₂',
    ],
    'with_title' => 'ketamiz.com bilan',
    'with' => [
        '1 ta mashina — birga safar',
        'Talaba arzon va qulay yetib boradi',
        'Domlaning benzin puli qoplanadi',
        'Kamroq mashina — toza havo',
    ],
    'num_student'   => 'Talaba to‘lovi',
    'num_fuel'      => 'Domla benzin xarajati',
    'num_covered'   => 'qoplandi',
    'num_month'     => 'Oyiga (taxminan)',
    'num_month_sub' => 'birgalikda tejaladi',
    'region_banner_strong' => 'Xuddi shu mantiq viloyatlar o‘rtasida ham:',
    'region_banner'        => 'Samarqand → Toshkent, Buxoro → Navoiy... uzoq yo‘lda tejamkorlik yanada katta bo‘ladi.',

    // Scenarios
    'scenarios_badge' => 'Hayotiy vaziyatlar',
    'scenarios_title' => 'ketamiz.com qaysi vaziyatlarda kerak?',
    'scenarios_sub'   => 'Domla va talaba — bitta misol. Mana hayotdan yana vaziyatlar — har birida ikki tomon ham yutadi.',
    'scenarios' => [
        ['title' => 'O‘qituvchi va talaba',  'desc' => 'Domla har kuni shahar markazidagi universitetga boradi, talaba bo‘sh o‘rindiqda birga qatnaydi.',        'win' => 'Ikkisi ham tejaydi'],
        ['title' => 'Ishga qatnov',          'desc' => 'Bir hududdan bitta ofisga boradigan hamkasblar har kuni bitta mashinada yo‘lga chiqadi.',             'win' => 'Yoqilg‘i bo‘linadi'],
        ['title' => 'Viloyatlar aro',         'desc' => 'Samarqanddan Toshkentga boradigan haydovchi uzoq yo‘l xarajatini yo‘lovchi bilan baham ko‘radi.',      'win' => 'Arzon va tez'],
        ['title' => 'Bozor va shifoxonaga',  'desc' => 'Qishloqdan tuman markaziga ish bilan boradiganlar uchun qulay va ishonchli qatnov.',                  'win' => 'Qulay yo‘l'],
        ['title' => 'Talabalar uyiga',        'desc' => 'Dam olish kunlari uyiga ketadigan talabalar bir yo‘nalishda birlashadi.',                            'win' => 'Hamroh va arzon'],
        ['title' => 'Yuk va pochta',          'desc' => 'Allaqachon yo‘lga chiqayotgan haydovchi bilan yuk yoki pochta tez yetkaziladi.',                      'win' => 'Tez yetkazish'],
        ['title' => 'To‘y va tadbirga',       'desc' => 'Bir mahalladan tadbirga boradigan mehmonlar birga, qulay tarzda yo‘lga chiqadi.',                     'win' => 'Birga va qulay'],
        ['title' => 'Aeroport va vokzalga',  'desc' => 'Reysga yoki poyezdga oshiqayotganlar uchun o‘z vaqtidagi ishonchli transfer.',                       'win' => 'O‘z vaqtida'],
    ],

    // Coverage
    'coverage_badge' => 'Qamrov',
    'coverage_title' => 'Qishloqdan poytaxtgacha — hamma joyda',
    'coverage' => [
        ['t' => 'Viloyatlar aro',      'd' => 'Toshkent ↔ Samarqand, Buxoro ↔ Navoiy — uzoq masofali safarlar.'],
        ['t' => 'Shaharlar ichida',    'd' => 'Shahar tashqarisidan markazga, bir tumandan ikkinchisiga qulay qatnov.'],
        ['t' => 'Qishloqlar o‘rtasida', 'd' => 'Mahalla va qishloqlararo — transport kam joylarda ham bog‘lanish.'],
    ],

    // Steps
    'steps_badge' => '3 qadam',
    'steps_title' => 'Safar shunchaki oson',
    'steps' => [
        ['t' => 'Safarni rejalashtiring', 'd' => 'Haydovchi yo‘nalish, vaqt va bo‘sh o‘rindiqni oldindan e’lon qiladi.'],
        ['t' => 'Hamrohni toping',         'd' => 'Yo‘lovchi bir xil yo‘nalishdagi safarni topib, o‘rindiqni band qiladi.'],
        ['t' => 'Birga ketamiz!',          'd' => 'Ikkalasi bir mashinada — pul tejaladi, yoqilg‘i qoplanadi.'],
    ],

    // Benefits
    'benefits_badge' => 'Nega ketamiz?',
    'benefits_title' => 'Hamma uchun foyda',
    'benefits' => [
        ['t' => 'Tejamkor', 'd' => 'Yo‘lovchi arzon yuradi, haydovchi xarajatini qoplaydi.'],
        ['t' => 'Rejali',   'd' => 'Safar oldindan ma’lum — kutib qolmaysiz.'],
        ['t' => 'Ekologik', 'd' => 'Bo‘sh o‘rindiqlar to‘ladi — yo‘lda kamroq mashina.'],
        ['t' => 'Xavfsiz',  'd' => 'Reyting va sharhlar bilan ishonchli hamrohlar.'],
    ],

    // App screenshots
    'shots_badge' => 'Ilova ichidan',
    'shots_title' => 'Ilova qanday ko‘rinadi?',
    'shots_sub'   => 'ketamiz.com ilovasining haqiqiy ekranlari — safar topish, band qilish va to‘lov — hammasi qo‘lingizda.',
    'shots_feat'  => [
        'Yo‘nalish bo‘yicha safar qidirish va filtr',
        'Bir necha bosqichda joy band qilish',
        'Ilova ichida qulay va xavfsiz to‘lov',
        'Reyting, sharhlar va safar tarixi',
    ],

    // Download
    'download_badge' => 'Mobil ilova',
    'download_title' => 'ilovasini yuklab oling',
    'download_sub'   => 'Android va iOS uchun — safaringizni rejalashtiring, hamroh toping va birga yo‘lga chiqing.',

    // Footer
    'footer_tagline' => 'O‘zbekiston bo‘ylab birga safar — tejamkor va xavfsiz.',
];
