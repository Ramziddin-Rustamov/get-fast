<!DOCTYPE html>
<html lang="uz">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Qadam — bir yo‘nalishda ketayotgan odamlarni bog‘lovchi qulay ilova" />
        <meta name="author" content="Qadam App" />
        <title>Qadam — Yo‘ldosh topish ilovasi</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('landing-page/favicon.ico') }}" />
        <link href="{{ asset('landing-page/styles.css') }}" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&display=swap" rel="stylesheet" />
    </head>

    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
            <div class="container px-5">
                <a class="navbar-brand fw-bold" href="#page-top">Qadam</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                    Menu <i class="bi-list"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                        <li class="nav-item"><a class="nav-link me-lg-3" href="#features">Imkoniyatlar</a></li>
                        <li class="nav-item"><a class="nav-link me-lg-3" href="#download">Yuklab olish</a></li>
                    </ul>
                      <a href="https://t.me/ramziddin_rustam" class="btn btn-primary">
                        <i class="bi-chat-text-fill me-2"></i> Fikr bildirish
                      </a>
                 
                </div>
            </div>
        </nav>

        <!-- Header-->
        <header class="masthead">
            <div class="container px-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="mb-5 text-center text-lg-start">
                            <h1 class="display-1 lh-1 mb-3">
                                Bir yo‘nalishda — birga yo‘lga chiqing
                            </h1>
                            <p class="lead fw-normal text-muted mb-5">
                                Qadam — bir shahardan boshqasiga ketayotgan odamlarni bog‘lovchi qulay va ishonchli ilova.
                            </p>
                            <div class="d-flex flex-column flex-lg-row align-items-center">
                                <a class="me-lg-3 mb-4 mb-lg-0" href="#">
                                    <img class="app-badge" src="{{ asset('landing-page/img/google-play-badge.svg') }}" />
                                </a>
                                <a href="#">
                                    <img class="app-badge" src="{{ asset('landing-page/img/app-store-badge.svg') }}" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="masthead-device-mockup">
                            <div class="device-wrapper">
                                <div class="device" data-device="iPhoneX" data-orientation="portrait" data-color="black">
                                    <div class="screen bg-black">
                                        <video muted autoplay loop style="max-width: 100%; height: 100%">
                                            <source src="{{ asset('landing-page/img/demo-screen.mp4') }}" type="video/mp4" />
                                        </video>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- Quote-->
        <aside class="text-center bg-gradient-primary-to-secondary">
            <div class="container px-5">
                <div class="h2 fs-1 text-white mb-4">
                    "Qadam — safarni arzonroq, qulayroq va yanada ijtimoiy qiladi."
                </div>
            </div>
        </aside>

        <!-- Features-->
        <section id="features">
            <div class="container px-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-8">
                        <div class="row gx-5">
                            <div class="col-md-6 mb-5 text-center">
                                <i class="bi-phone icon-feature text-gradient mb-3"></i>
                                <h3>Qulay ilova</h3>
                                <p class="text-muted">
                                    Haydovchi va yo‘lovchilarni bir necha soniyada toping.
                                </p>
                            </div>
                            <div class="col-md-6 mb-5 text-center">
                                <i class="bi-camera icon-feature text-gradient mb-3"></i>
                                <h3>Jonli safarlar</h3>
                                <p class="text-muted">
                                    Safar tafsilotlari, joy va vaqt aniq ko‘rsatiladi.
                                </p>
                            </div>
                            <div class="col-md-6 text-center">
                                <i class="bi-gift icon-feature text-gradient mb-3"></i>
                                <h3>Bepul foydalanish</h3>
                                <p class="text-muted">
                                    Ilovani yuklab olish va foydalanish mutlaqo bepul.
                                </p>
                            </div>
                            <div class="col-md-6 text-center">
                                <i class="bi-patch-check icon-feature text-gradient mb-3"></i>
                                <h3>Ishonchli tizim</h3>
                                <p class="text-muted">
                                    Reyting va tekshiruvlar orqali xavfsizlik ta’minlanadi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Info section-->
        <section class="bg-light">
            <div class="container px-5">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <h2 class="display-4 mb-4">
                            Safarni birga boshlash vaqti keldi
                        </h2>
                        <p class="lead text-muted">
                            Qadam orqali siz yo‘l xarajatlarini bo‘lishasiz, yangi odamlar bilan tanishasiz va
                            har bir safarni yanada samarali qilasiz.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <img class="img-fluid rounded-circle" src="https://source.unsplash.com/u8Jn2rzYIps/900x900" />
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA-->
        <section class="cta">
            <div class="cta-content">
                <div class="container px-5">
                    <h2 class="text-white display-1 mb-4">
                        Kutmang.<br />Yo‘lga chiqing.
                    </h2>
                    <a class="btn btn-outline-light py-3 px-4 rounded-pill" href="#">
                        Ilovani yuklab olish
                    </a>
                </div>
            </div>
        </section>

        <!-- Download-->
        <section class="bg-gradient-primary-to-secondary" id="download">
            <div class="container px-5">
                <h2 class="text-center text-white mb-4">
                    Qadam ilovasini tez kunda yuklab oling
                </h2>
                <div class="d-flex justify-content-center">
                    <a class="me-3" href="#"><img class="app-badge" src="{{ asset('landing-page/img/google-play-badge.svg') }}"></a>
                    <a href="#"><img class="app-badge" src="{{ asset('landing-page/img/app-store-badge.svg') }}"></a>
                </div>
            </div>
        </section>

        <!-- Footer-->
        <footer class="bg-black text-center py-5">
            <div class="text-white-50 small">
                &copy; Qadam 2025. Barcha huquqlar himoyalangan.
            </div>
        </footer>
    </body>
</html>
