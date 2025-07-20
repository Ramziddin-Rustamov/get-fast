<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('title_uz');
            $table->string('title_en');
            $table->string('title_ru');
            $table->string('code');
            $table->timestamps();
        });

        $colors = [
            ['title_uz' => 'Qizil', 'title_en' => 'Red', 'title_ru' => 'Красный', 'code' => '#FF0000'],
            ['title_uz' => 'Yashil', 'title_en' => 'Green', 'title_ru' => 'Зелёный', 'code' => '#00FF00'],
            ['title_uz' => 'Ko\'k', 'title_en' => 'Blue', 'title_ru' => 'Синий', 'code' => '#0000FF'],
            ['title_uz' => 'Sariq', 'title_en' => 'Yellow', 'title_ru' => 'Жёлтый', 'code' => '#FFFF00'],
            ['title_uz' => 'Qora', 'title_en' => 'Black', 'title_ru' => 'Чёрный', 'code' => '#000000'],
            ['title_uz' => 'Oq', 'title_en' => 'White', 'title_ru' => 'Белый', 'code' => '#FFFFFF'],
            ['title_uz' => 'Kulrang', 'title_en' => 'Gray', 'title_ru' => 'Серый', 'code' => '#808080'],
            ['title_uz' => 'To‘q ko‘k', 'title_en' => 'Navy', 'title_ru' => 'Морской', 'code' => '#000080'],
            ['title_uz' => 'Jigarrang', 'title_en' => 'Brown', 'title_ru' => 'Коричневый', 'code' => '#A52A2A'],
            ['title_uz' => 'To‘q yashil', 'title_en' => 'Dark Green', 'title_ru' => 'Тёмно-зелёный', 'code' => '#006400'],
            ['title_uz' => 'Olcha', 'title_en' => 'Maroon', 'title_ru' => 'Бордовый', 'code' => '#800000'],
            ['title_uz' => 'Zaytun', 'title_en' => 'Olive', 'title_ru' => 'Оливковый', 'code' => '#808000'],
            ['title_uz' => 'Kumush', 'title_en' => 'Silver', 'title_ru' => 'Серебряный', 'code' => '#C0C0C0'],
            ['title_uz' => 'Olovrang', 'title_en' => 'Orange', 'title_ru' => 'Оранжевый', 'code' => '#FFA500'],
            ['title_uz' => 'Siyohrang', 'title_en' => 'Purple', 'title_ru' => 'Пурпурный', 'code' => '#800080'],
            ['title_uz' => 'Pushti', 'title_en' => 'Pink', 'title_ru' => 'Розовый', 'code' => '#FFC0CB'],
            ['title_uz' => 'Ko‘k-yashil', 'title_en' => 'Teal', 'title_ru' => 'Бирюзовый', 'code' => '#008080'],
            ['title_uz' => 'Aqua', 'title_en' => 'Aqua', 'title_ru' => 'Аква', 'code' => '#00FFFF'],
            ['title_uz' => 'Shaftoli', 'title_en' => 'Peach', 'title_ru' => 'Персиковый', 'code' => '#FFDAB9'],
            ['title_uz' => 'Oltin', 'title_en' => 'Gold', 'title_ru' => 'Золотой', 'code' => '#FFD700'],
            ['title_uz' => 'Bej', 'title_en' => 'Beige', 'title_ru' => 'Бежевый', 'code' => '#F5F5DC'],
            ['title_uz' => 'Shokolad', 'title_en' => 'Chocolate', 'title_ru' => 'Шоколадный', 'code' => '#D2691E'],
            ['title_uz' => 'Karamel', 'title_en' => 'Caramel', 'title_ru' => 'Карамельный', 'code' => '#AF6E4D'],
            ['title_uz' => 'Quyosh', 'title_en' => 'Sunshine', 'title_ru' => 'Солнечный', 'code' => '#FFD300'],
            ['title_uz' => 'Dengiz to‘lqini', 'title_en' => 'Sea wave', 'title_ru' => 'Морская волна', 'code' => '#2E8B57'],
            ['title_uz' => 'Pushti binafsha', 'title_en' => 'Magenta', 'title_ru' => 'Фуксия', 'code' => '#FF00FF'],
            ['title_uz' => 'Qaymoqrang', 'title_en' => 'Ivory', 'title_ru' => 'Слоновая кость', 'code' => '#FFFFF0'],
            ['title_uz' => 'Jigar binafsha', 'title_en' => 'Indigo', 'title_ru' => 'Индиго', 'code' => '#4B0082'],
            ['title_uz' => 'Zangori', 'title_en' => 'Sky Blue', 'title_ru' => 'Небесно-голубой', 'code' => '#87CEEB'],
            ['title_uz' => 'To‘q kulrang', 'title_en' => 'Dark Gray', 'title_ru' => 'Тёмно-серый', 'code' => '#A9A9A9'],
        ];

        DB::table('colors')->insert($colors);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
