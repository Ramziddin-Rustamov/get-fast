# ketamiz.com — Qonun-qoidalar va foydalanish tartibi

**Oxirgi yangilangan sana:** 2026-yil 21-iyun

Ushbu hujjat **ketamiz.com** ilovasidan qanday foydalanish, safar bron qilish, bekor qilish, to'lovlar va boshqa jarayonlar qanday ishlashini batafsil tushuntiradi. Bu yerda keltirilgan barcha qoidalar ilovaning haqiqiy ishlash tartibiga mos keladi. Ilovadan foydalanib, siz ushbu qoidalarni qabul qilgan hisoblanasiz.

> **Eslatma:** Misollarda bir o'rin narxi sifatida shartli **100 000 so'm** olingan. Haqiqiy narxlar safarga qarab farq qiladi. Xizmat haqqi va boshqa foizlar Operator tomonidan o'zgartirilishi mumkin va ilovada ko'rsatiladi.

---

## 1. ketamiz.com nima?

ketamiz.com — Haydovchilar va Yo'lovchilarni bog'lovchi **onlayn vositachilik platformasi** (shaharlararo, mahalladan-mahallaga qatnov). Platforma o'zi yo'lovchi tashish xizmatini ko'rsatmaydi — u faqat Haydovchi va Yo'lovchini bog'laydi va to'lovlarni xavfsiz tarzda amalga oshiradi.

Foydalanuvchi toifalari:
- **Yo'lovchi (Mijoz)** — safarga joy bron qiladi.
- **Haydovchi** — o'z mashinasida safar e'lon qiladi va yo'lovchilarni tashiydi.
- **Administrator** — haydovchilarni tekshiradi, pul yechish so'rovlarini ko'rib chiqadi.

---

## 2. Ro'yxatdan o'tish va kirish

### 2.1. Ro'yxatdan o'tish
1. Foydalanuvchi **ism, familiya, telefon raqami, parol** (kamida 6 belgi) kiritadi. Email va otasining ismi ixtiyoriy.
2. Telefon raqamiga **6 xonali SMS tasdiqlash kodi** yuboriladi.
3. Yangi foydalanuvchi avtomatik **"mijoz" (client)** rolida yaratiladi, balansi **0 so'm**, til esa **o'zbekcha** (keyin o'zgartirish mumkin).
4. Bitta telefon raqami bilan faqat bitta hisob bo'ladi. Agar raqam allaqachon ro'yxatdan o'tgan va tasdiqlangan bo'lsa — "tizimga kiring" deb javob qaytadi.

### 2.2. Telefonni tasdiqlash
- Foydalanuvchi SMS kodni kiritadi. Kod to'g'ri bo'lsa, hisob **tasdiqlangan** (`is_verified`) holatga o'tadi va kirish tokeni beriladi.
- Kod noto'g'ri bo'lsa — "Tasdiqlash kodi noto'g'ri" xabari chiqadi.
- **Kodni qayta yuborish** mumkin (allaqachon tasdiqlangan raqamga qayta yuborilmaydi).

### 2.3. Tizimga kirish
- Telefon + parol orqali kiriladi.
- **Tasdiqlanmagan** hisob bilan kirib bo'lmaydi — avval telefonni tasdiqlash kerak.
- **Bloklangan** (`blocked`) hisob bilan kirib bo'lmaydi — Operator bilan bog'lanish kerak.

### 2.4. Parolni unutish
- Telefon raqamiga SMS orqali tiklash kodi yuboriladi.
- Kod + yangi parol (kamida 6 belgi) kiritilib, parol yangilanadi.

---

## 3. Haydovchi bo'lish

Mijoz haydovchiga aylanishi uchun bosqichlar:

1. **Haydovchilik ma'lumotlari:** haydovchilik guvohnomasi raqami, amal qilish muddati, tug'ilgan sana kiritiladi. Shu bosqichda rol **"haydovchi"** ga o'zgaradi.
2. **Mashina rasmlari va hujjatlari yuklanadi:** texnik pasport (old/orqa), mashina fotosuratlari. Shu bosqichdan so'ng hisob **"tekshiruvda" (pending)** holatiga o'tadi.
3. **Haydovchi hujjatlari:** haydovchilik guvohnomasi (old/orqa), pasport rasmi yuklanadi.
4. **Administrator tekshiruvi:** administrator hujjatlarni ko'rib chiqib, holatni belgilaydi:
   - **approved** — tasdiqlangan, endi safar e'lon qila oladi;
   - **rejected / none / blocked** — rad etilgan, rol yana "mijoz"ga qaytadi;
   - **pending** — tekshiruv davom etmoqda.

> Faqat **approved** holatidagi haydovchi safar e'lon qila oladi.

**Hujjat (rasm) talablari:** har bir rasm JPEG/PNG/JPG/GIF formatda, hajmi **10 MB** gacha. Profil rasmi — 5 MB gacha.

---

## 4. Safar (Trip) yaratish — Haydovchi uchun

Haydovchi safar yaratishda quyidagilarni ko'rsatadi:
- **Yo'nalish:** boshlanish va tugash nuqtalari (viloyat, tuman, mahalla), ixtiyoriy koordinatalar;
- **Vaqt:** boshlanish va tugash vaqti;
- **Joylar soni** (`available_seats`) va **bir o'rin narxi**.

**Qoidalar:**
- Safar boshlanish vaqti **hozirdan keyingi 48 soat ichida** bo'lishi kerak (o'tmishda bo'lishi mumkin emas).
- Safar davomiyligi **kamida 10 daqiqa, ko'pi bilan 48 soat**.
- Bir o'rin narxi 0 dan kam bo'lmasligi kerak.
- **Bir xil yoki vaqt jihatdan ustma-ust** tushadigan, hali faol (active/pending) safar mavjud bo'lsa, yangi safar yaratib bo'lmaydi ("Sizda shu vaqtda safar mavjud").

**Safar holatlari:**
- **active** — faol, bron qabul qilmoqda;
- **full** — barcha o'rinlar band;
- **cancelled** — bekor qilingan;
- **completed** — yakunlangan.

Yo'lovchi joy bron qilganda `available_seats` kamayadi; 0 ga yetganda safar **full** bo'ladi. Bron bekor qilinsa, o'rin qaytadi va safar yana **active** bo'ladi.

---

## 5. Safar bron qilish — Yo'lovchi uchun

### 5.1. Bron qanday qilinadi
1. Yo'lovchi safarni tanlaydi va **yo'lovchilar ro'yxatini** kiritadi. Har bir yo'lovchi uchun: **ism, telefon, olib ketish manzili (koordinatalari)**.
2. **Band qilinadigan o'rinlar soni = yo'lovchilar soni.**
3. Umumiy narx = `bir o'rin narxi × o'rinlar soni`.

**Misol:** 100 000 so'm × 4 o'rin = **400 000 so'm**.

### 5.2. Bron qilish shartlari
- Safar mavjud va bekor qilinmagan bo'lishi;
- So'ralgan o'rinlar soni bo'sh o'rinlardan oshmasligi;
- Haydovchi **o'z safariga** bron qila olmaydi;
- Yo'lovchining **hamyonida yetarli mablag'** bo'lishi shart (balans yetmasa, bron amalga oshmaydi).

### 5.3. Pul qanday taqsimlanadi (bron paytida)
400 000 so'mlik bron misolida (xizmat haqqi **5%**):

| Tomon | Harakat | Summa |
|------|---------|-------|
| **Yo'lovchi** | Hamyondan yechiladi | −400 000 so'm |
| **Haydovchi** | Hamyonga tushadi (xizmat haqqi chegirilgan) | +380 000 so'm |
| **Platforma (Operator)** | Xizmat haqqi (5%) | +20 000 so'm |

Har bir operatsiya Yo'lovchi va Haydovchining **tranzaksiyalar tarixiga** yoziladi. Bron tasdiqlangach, Haydovchiga va Yo'lovchiga SMS xabar yuboriladi.

### 5.4. Yo'lovchi qo'shish (mavjud bronga)
- Bron **confirmed** va safar **active** bo'lishi, kamida 1 bo'sh o'rin bo'lishi kerak.
- Har bir qo'shilgan yo'lovchi uchun bir o'rin narxi yana hamyondan yechiladi (xuddi yangi bron kabi: Yo'lovchidan to'liq, Haydovchiga 95%, Platformaga 5%).
- Yo'lovchi qo'shishda **vaqt cheklovi yo'q** (safar faol va o'rin bo'lsa bo'ldi).

### 5.5. Yo'lovchi manzilini yangilash
- Yo'lovchining olib ketish manzili (koordinatalari) istalgan vaqtda yangilanishi mumkin. Bu **pulga ta'sir qilmaydi**.

---

## 6. Bekor qilish va pul qaytarish

### 6.1. Yo'lovchi bronni bekor qilganda
**Asosiy qoida:** bronni **safar boshlanishiga kamida 2 soat qolganda** bekor qilish mumkin. 2 soatdan kam qolgan bo'lsa, bekor qilib bo'lmaydi.

400 000 so'mlik bron misolida (bekor qilish haqqi **5%**, haydovchiga komissiya **1%**):

| Tomon | Harakat | Summa |
|------|---------|-------|
| **Yo'lovchi** | Hamyonga qaytariladi (xizmat haqqi chegirilgan) | +380 000 so'm |
| **Platforma** | Bekor qilish haqqi (ushlab qolinadi) | 20 000 so'm |
| **Haydovchi** | Olgan daromadi yechiladi | −380 000 so'm |
| **Haydovchi** | 1% komissiya qaytariladi | +4 000 so'm |

> **Xizmat haqqi (5%) qaytarilmaydi.** Bron bekor qilinganda barcha yo'lovchilar "cancelled" bo'ladi, o'rinlar safarga qaytadi.

### 6.2. Bitta yo'lovchini olib tashlash
- **2 soat qoidasi** bu yerda ham amal qiladi.
- Bir yo'lovchi olib tashlanganda: Yo'lovchiga `narx − 5%` qaytariladi, Haydovchidan tegishli summa yechiladi va 1% komissiya qaytariladi, o'rin safarga qaytadi.
- Agar bronda yo'lovchi qolmasa, bron butunlay **cancelled** bo'ladi.

### 6.3. Haydovchi safarni bekor qilganda
Haydovchi safarni bekor qilsa, har bir bron bo'yicha hisob-kitob avtomatik amalga oshadi (haydovchining bekor qilish haqqi **4%**, yo'lovchiga kompensatsiya **1%**):

| Tomon | Harakat | Summa (400 000 misolida) |
|------|---------|--------------------------|
| **Yo'lovchi** | To'liq summa + 1% kompensatsiya qaytariladi | +404 000 so'm |
| **Haydovchi** | Daromadi va jarima yechiladi | balansidan ushlanadi |

> Haydovchi safarni bekor qilganda **Yo'lovchi yutqazmaydi** — to'langan summasi qaytariladi va ustiga kichik kompensatsiya beriladi. Bekor qilingan safar `expired_trips` jadvaliga yoziladi.

---

## 7. To'lovlar, kartalar va hamyon

### 7.1. Bank kartasini ulash
1. Foydalanuvchi karta raqami, amal muddati (MMYY), egasi ismi va telefonini kiritadi.
2. Karta **Hamkorbank** to'lov tizimi orqali ro'yxatdan o'tkaziladi. Platforma **kartaning to'liq raqamini saqlamaydi** — faqat bank tokeni va niqoblangan raqam saqlanadi.
3. Kartaga **SMS tasdiqlash kodi** keladi; uni kiritib, karta **tasdiqlanadi (verified)**.
4. Faqat **tasdiqlangan** karta bilan to'lov qilish mumkin. Birinchi ulangan karta — asosiy (default) bo'ladi.

### 7.2. Hamyonni to'ldirish
1. Foydalanuvchi summani kiritadi (**kamida 1 000 so'm**) va kartani tanlaydi.
2. Bank karta balansini tekshiradi (mablag' yetarli bo'lishi kerak).
3. Bank talab qilsa — **SMS kod** kiritiladi; tasdiqlangach hamyonga pul tushadi. Ba'zan SMS talab qilinmaydi va pul darrov tushadi.
4. Kerak bo'lsa SMS kodni **qayta yuborish** mumkin.

### 7.3. Hamyon balansi va tarix
- Balans **so'mda (UZS)** ko'rsatiladi.
- Har bir kirim/chiqim **tranzaksiyalar tarixida** (kim, qancha, sabab, sana) saqlanadi.

### 7.4. Hamyondan kartaga pul qaytarish (refund)
- Foydalanuvchi hamyondagi pulni kartasiga qaytarib olishi mumkin.
- **Cheklovlar:** kamida **1 000 so'm**, ko'pi bilan **200 000 so'm**, hamda hamyon balansidan oshmasligi kerak.
- Pul Hamkorbank orqali kartaga o'tkaziladi va tasdiqlovchi SMS yuboriladi.

---

## 8. Pul yechish (Withdrawal) — Haydovchilar uchun

1. Haydovchi hamyon mablag'ini kartasiga yechish uchun so'rov yuboradi (**kamida 10 000 so'm**).
2. Bir vaqtning o'zida faqat **bitta "pending" so'rov** bo'lishi mumkin.
3. So'rov **administrator** tomonidan ko'rib chiqiladi:
   - **Tasdiqlansa** — mablag' haydovchi hamyonidan yechilib, kartasiga o'tkaziladi va haydovchiga xabar boradi;
   - **Rad etilsa** — balans o'zgarmaydi, so'rov "rejected" bo'ladi.

---

## 9. Hisobni o'chirish

Foydalanuvchi istalgan vaqtda ilovadagi **"Hisobni o'chirish"** funksiyasidan foydalanishi mumkin. Lekin:
- **Balans manfiy (qarz) bo'lsa** — avval qarzni to'lash kerak;
- **Faol bronlar bo'lsa** (pending/confirmed) — avval ularni yakunlash kerak.

Shartlar bajarilsa, hisob o'chiriladi: shaxsiy ma'lumotlar **anonimlashtiriladi** (ism "Deleted User", telefon/email tasodifiy qiymatga almashtiriladi), rasmlar o'chiriladi va hisob **soft delete** qilinadi (qonuniy hisobotlar uchun yozuv saqlanadi, lekin hisobga kirib bo'lmaydi).

---

## 10. Foydalanuvchining majburiyatlari

Foydalanuvchi quyidagilarni qilmasligi shart:
- soxta ma'lumot yoki hujjat taqdim etish;
- boshqaning hisobidan ruxsatsiz foydalanish;
- noqonuniy, xavfli yoki taqiqlangan yuk tashish;
- haqorat, kamsitish, tahdid yoki zo'ravonlik;
- ilova ishiga texnik aralashuv yoki firibgarlik.

Qoidabuzarlik hisobni cheklash yoki bloklashga olib keladi.

---

## 11. Narxlar va foizlar (umumiy jadval)

| Qoida | Joriy qiymat |
|------|-------------|
| Bron uchun xizmat haqqi | **5%** |
| Bron bekor qilish haqqi (yo'lovchi) | **5%** |
| Yo'lovchi bekor qilganda haydovchiga komissiya | **1%** |
| Haydovchi safarni bekor qilish jarimasi | **4%** |
| Haydovchi bekor qilganda yo'lovchiga kompensatsiya | **1%** |
| Bronni bekor qilish muddati | safardan **kamida 2 soat** oldin |
| Hamyonni to'ldirish (minimal) | **1 000 so'm** |
| Kartaga qaytarish (refund) | **1 000 – 200 000 so'm** |
| Pul yechish so'rovi (minimal) | **10 000 so'm** |
| Safar vaqti | hozirdan **48 soat** ichida |
| Safar davomiyligi | **10 daqiqa – 48 soat** |

> Foizlar va summalar Operator tomonidan o'zgartirilishi mumkin; amaldagi qiymatlar har doim ilovada ko'rsatiladi.

---

## 12. Mas'uliyat

- Platforma **vositachi** bo'lib, yo'lovchi tashish xizmatini bevosita ko'rsatmaydi va tashuvchi hisoblanmaydi.
- Operator safar xavfsizligi, kechikish yoki Haydovchi/Yo'lovchi o'rtasidagi nizolar uchun bevosita javobgar emas, biroq xavfsizlik uchun oqilona choralar ko'radi (hujjatlarni tekshirish, bloklash).
- Barcha to'lovlar **Hamkorbank** xavfsiz to'lov infratuzilmasi orqali amalga oshiriladi.

---

## 13. Bog'lanish

- **Email:** *[email]*
- **Telefon:** *[telefon]*
- **Manzil:** *[manzil]*
- **Operator:** *[Kompaniya nomi]*, STIR: *[STIR]*

---

*Ushbu hujjat ilovaning haqiqiy ishlash mantig'i asosida tayyorlangan. Yuridik jihatdan to'liq kuchga ega bo'lishi uchun kompaniya ma'lumotlari to'ldirilishi va O'zbekiston Respublikasi qonunchiligiga muvofiqligi malakali yurist bilan tekshirilishi tavsiya etiladi.*
