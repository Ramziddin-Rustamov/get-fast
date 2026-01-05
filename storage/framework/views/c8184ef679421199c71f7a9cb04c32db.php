<?php $__env->startSection('content'); ?>
<?php if(auth()->guard()->check()): ?>
    
<div class="vh-100 d-flex align-items-center justify-content-center" >
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0" style="border-radius: 15px;">
                    <div class="card-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h3 class="mb-0">Boshqaruv Panelga Xush Kelibsiz</h3>
                    </div>
                    <div class="card-body p-5" style="background: #f7f9fc; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <p class="lead mb-4">Assalomu alaykum, <strong><?php echo e(Auth::user()->first_name ?? Auth::user()->phone); ?></strong>!</p>
                        <p class="mb-4">Sizning boshqaruv panelingizdan barcha imkoniyatlardan foydalanishingiz mumkin.</p>

                        <a href="<?php echo e(route('auth.logout.post')); ?>" 
                           class="btn btn-danger btn-lg"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                           Chiqish
                        </a>

                        <form id="logout-form" action="<?php echo e(route('auth.logout.post')); ?>" method="POST" class="d-none">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if(auth()->guard()->guest()): ?>
<!-- Hero / Landing Image Section -->
<section class="hero-section text-center d-flex align-items-center" style="background: url('https://images.pexels.com/photos/8828455/pexels-photo-8828455.jpeg') center/cover no-repeat;
 height: 80vh; border-radius: 15px; ">
    <div class="container text-white">
        <h1 class="display-4 fw-bold mb-3 text-primary backdrop">Sayohatingizni Osonlashtiring</h1>
        <p class="lead mb-4 fw-bold text-primary">Eng qulay yo‘llar bilan haydovchi va yo‘lovchilarni bog‘laymiz</p>
        <a href="<?php echo e(route('login')); ?>" class="btn btn-lg btn-primary shadow">Boshlash</a>
    </div>
</section>


<!-- Features & Opportunities Section -->
<section id="features-opportunities" class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="fw-bold display-5" style="color: #222;">Qadam Ilovasi</h2>
            <p class="text-muted fs-5 mx-auto" style="max-width: 700px;">
                Safaringizni eng qulay va xavfsiz tarzda osonlashtiruvchi platforma. O‘zbekiston bo‘ylab viloyatlar ichida yoki aro sayohat qilish mumkin. Ilova ikki xil foydalanuvchi uchun mo‘ljallangan: <strong>Haydovchi</strong> va <strong>Mijoz</strong>.
            </p>
        </div>

        <!-- Haydovchi va Mijoz Cards -->
        <div class="row g-4">
            <!-- Haydovchi Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-5 p-5" style="transition: transform 0.3s; background: #ffffff;">
                    <div class="text-center mb-4">
                        <img src="https://img.icons8.com/ios-filled/100/4a90e2/driver.png" alt="Driver" class="mb-3">
                        <h4 class="fw-bold" style="color: #333;">1️⃣ Haydovchi uchun Imkoniyatlar</h4>
                    </div>
                    <ul class="list-unstyled fs-6 text-secondary" style="line-height: 1.8;">
                        <li>🚗 Bo‘sh o‘rindiqlarni e’lon qilish va mijozlarni topish</li>
                        <li>💰 Narxni belgilash va shoshilinch safarlar uchun moslashuv</li>
                        <li>📦 Yuk va pochta tashish imkoniyati</li>
                        <li>💳 Tez va qulay to‘lov (naxt yoki karta orqali)</li>
                        <li>📅 Moslashuvchan jadval: har kuni qatnashish va foydalanuvchilar bilan safar qilish</li>
                    </ul>
                </div>
            </div>

            <!-- Mijoz Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-5 p-5" style="transition: transform 0.3s; background: #ffffff;">
                    <div class="text-center mb-4">
                        <img src="https://img.icons8.com/ios-filled/100/4a90e2/passenger.png" alt="Passenger" class="mb-3">
                        <h4 class="fw-bold" style="color: #333;">2️⃣ Mijoz uchun Imkoniyatlar</h4>
                    </div>
                    <ul class="list-unstyled fs-6 text-secondary" style="line-height: 1.8;">
                        <li>🚌 Arzon va tez transport topish imkoniyati</li>
                        <li>📍 Viloyatlar va mahallalararo safarlar</li>
                        <li>💳 To‘lov karta yoki naxt orqali, ilova avtomatik hisob-kitob qiladi</li>
                        <li>📆 Ikkita kunlik safarlarni rejalashtirish va joylash</li>
                        <li>👍 Foydalanuvchi reytingi va sharhlar bilan ishonch hosil qilish</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Additional Features -->
        <div class="text-center mt-5">
            <h3 class="fw-bold mb-4" style="color: #222;">Qo‘shimcha Imkoniyatlar</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-5 p-4 h-100" style="background: #ffffff;">
                        <h5 class="fw-semibold">⏱ Shoshilinch va samarali</h5>
                        <p class="text-secondary">Vaqtingizni tejash va bo‘sh o‘rindiqlardan optimal foydalanish.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-5 p-4 h-100" style="background: #ffffff;">
                        <h5 class="fw-semibold">💬 Foydalanuvchi sharhlari</h5>
                        <p class="text-secondary">Haydovchi yoki mijoz sifatida ishonch hosil qilish imkoniyati.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-5 p-4 h-100" style="background: #ffffff;">
                        <h5 class="fw-semibold">📲 Mobil ilova</h5>
                        <p class="text-secondary">App Store va Play Marketda mavjud bo‘lib, har doim yoningizda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Optional Hover Effect for Cards -->
<style>
.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
</style>
<!-- Coming Soon Section -->
<section class="coming-soon-section d-flex align-items-center justify-content-center" 
         style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); min-height: 70vh; color: #fff; text-align: center;">
    <div class="container">
        <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInDown">Coming Soon</h1>
        <p class="lead mb-4 fs-4 animate__animated animate__fadeInUp">
            Qadam ilovasi tez orada <strong>App Store</strong> va <strong>Google Play</strong>da!<br>
            Sizning sayohatlaringizni eng oson va xavfsiz qilamiz.
        </p>
        <a href="<?php echo e(route('login')); ?>" class="btn btn-light btn-lg px-5 py-3 shadow animate__animated animate__zoomIn">
            Oldindan Kirish
        </a>
    </div>
</section>

<!-- Animate.css CDN for animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<!-- App Store & Play Market Section -->
<section class="download-app-section py-5" style="background: #f8f9fa;">
    <div class="container text-center">
        <h2 class="fw-bold mb-4" style="color: #222;">Ilovani Yuklab Oling</h2>
        <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 700px;">
            Android va iOS uchun qulay ilovamiz bilan sayohatingizni yanada oson va xavfsiz qiling. Tez orada App Store va Google Play orqali yuklab olishingiz mumkin.
        </p>
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a href="#" class="btn btn-dark btn-lg px-4 py-3 shadow rounded-4 d-flex align-items-center gap-2">
                <img src="https://img.icons8.com/ios-filled/30/ffffff/apple-logo.png" alt="App Store">
                App Store
            </a>
            <a href="#" class="btn btn-primary btn-lg px-4 py-3 shadow rounded-4 d-flex align-items-center gap-2" style="background: linear-gradient(135deg,#6a11cb,#2575fc); border: none;">
                <img src="https://img.icons8.com/ios-filled/30/ffffff/google-play.png" alt="Google Play">
                Google Play
            </a>
        </div>
    </div>
</section>

<!-- Optional Hover Effect -->
<style>
.btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 25px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
</style>
<!-- Footer Section -->
<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 Qadam Ilovasi. Barcha huquqlar himoyalangan.</p>
        <p class="mb-0" style="font-weight: 500;">Powered by <span style="color: #6a11cb;">GOTOGETHER</span></p>
    </div>
</footer>

<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/get-fast/resources/views/welcome.blade.php ENDPATH**/ ?>