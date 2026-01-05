<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha384-qIgtX3TJL3zI6AOMsBoC3RnUedbLgPoLm1fIxSkKpTME4xD9FfJpLzQ2Np9nXKFN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Scripts -->

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body style="background-color: rgb(220, 213, 213)">
    <div id="app">
        <header id="header" class="fixed-top  pb-2" style="background-color: rgba(255, 244, 239); box-shadow: 11px 11px 35px -10px rgba(66, 68, 90, 1);">
            <div class="container d-flex align-items-center">
        
                <h4 class="logo me-auto  py-3">
                    <a href="/" class="text-decoration-none text-dark fw-bold"> <?php echo e(config('app.name', 'Qadam')); ?></a>
                </h4>
        
        
              <div class="header-social-links ps-2 d-flex py-2">
                <ul class="navbar-nav ms-auto d-md-flex d-lg-flex ">

                    
                    <!-- Authentication Links -->
                    <?php if(auth()->guard()->guest()): ?>
                        <?php if(Route::has('login')): ?>
                            <li class="nav-item ">
                                <a style="<?php echo e((Request::is('login') ? 'color: green; text-decoration: none;' : '')); ?>" class="nav-link " href="<?php echo e(route('login')); ?>"><span><?php echo e(__('')); ?></span></a>
                            </li>
                        <?php endif; ?>
        
                        <?php if(Route::has('register')): ?>
                            <li class="nav-item active">
                                <a style="<?php echo e((Request::is('register') ? 'color: green; text-decoration: underline;' : '')); ?>"  class="nav-link <?php echo e((Request::is('register') ? 'active' : '')); ?>" href="<?php echo e(route('register')); ?>"><span><?php echo e(__('R. O\'tish')); ?></span></a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item dropdown d-flex "  id="navbarDropdown" >
                            <a style="padding-top:16px" id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                               <?php echo e(__("Boshqaruv")); ?>

                            </a>
        
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="background-color: rgba(255, 244, 239, 1);
                            box-shadow: 11px 11px 35px -10px rgba(66, 68, 90, 1);">


                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
                            <ul class="navbar-nav">
                                <li class="nav-item ">
                                    <a class="dropdown-item <?php echo e(request()->routeIs('drivers.index') ? 'active bg-success rounded' : ''); ?>"
                                       href="<?php echo e(route('drivers.index')); ?>">
                                        <?php echo e(__('Drivers')); ?>

                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  <?php echo e(request()->routeIs('clients.index') ? 'active bg-success rounded' : ''); ?>"
                                       href="<?php echo e(route('clients.index')); ?>">
                                        <?php echo e(__('Clients')); ?>

                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  <?php echo e(request()->routeIs('company.dashboard') ? 'active bg-success rounded' : ''); ?>"
                                       href="<?php echo e(route('company.dashboard')); ?>">
                                        <?php echo e(__('Company Dashboard')); ?>

                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  <?php echo e(request()->routeIs('company.transactions') ? 'active bg-success rounded' : ''); ?>"
                                       href="<?php echo e(route('company.transactions')); ?>">
                                        <?php echo e(__('Company Transactions')); ?>

                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="dropdown-item  <?php echo e(request()->routeIs('orders.index') ? 'active bg-success rounded' : ''); ?>"
                                       href="<?php echo e(route('orders.index')); ?>">
                                        <?php echo e(__('Orders')); ?>

                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="dropdown-item  <?php echo e(request()->routeIs('payments.index') ? 'active bg-success rounded' : ''); ?>"
                                       href="<?php echo e(route('payments.index')); ?>">
                                        <?php echo e(__('Payments')); ?>

                                    </a>
                                </li>

                              
                                
                            </ul>
                        <?php endif; ?>
                             

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('driver_web')): ?>
                            <a class="dropdown-item <?php echo e(request()->routeIs('home') ? 'active bg-success rounded' : ''); ?>"
                            href="<?php echo e(route('home')); ?>">
                                <?php echo e(__('Ma\'lumotlarim')); ?>

                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('client_web')): ?> 
                        <a class="dropdown-item <?php echo e(request()->routeIs('/') ? 'active bg-success rounded' : ''); ?>" href="/">
                            <?php echo e(__('Book Trip')); ?>

                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item <?php echo e(request()->routeIs('profile.index.client') ? 'active bg-success rounded' : ''); ?>" href="<?php echo e(route('profile.index.client')); ?>">
                            <?php echo e(__('Profile')); ?>

                        </a>
                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item <?php echo e(request()->routeIs('client.trips.index') ? 'active bg-success rounded' : ''); ?>" href="<?php echo e(route('client.trips.index')); ?>">
                            <?php echo e(__('My booked trips')); ?>

                        </a>
                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item <?php echo e(request()->routeIs('client.parcels.index') ? 'active bg-success rounded' : ''); ?> " href="<?php echo e(route('client.parcels.index')); ?>">
                            <?php echo e(__('My parcels')); ?>

                        </a>
                        <div class="dropdown-divider"></div>

                    <?php endif; ?>


                    <a class="dropdown-item" href="<?php echo e(route('auth.logout.post')); ?>"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <?php echo e(__('Chiqish')); ?>

                    </a>

                    <form id="logout-form" action="<?php echo e(route('auth.logout.post')); ?>" method="POST" class="d-none">
                        <?php echo csrf_field(); ?>
                    </form>
                            </div>
                        </li>
                    <?php endif; ?>
                  </ul>
                 <!-- Right Side Of Navbar -->
              </div>
            </div>
            
        </header>
        
 
        <main class="py-4">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/get-fast/resources/views/layouts/app.blade.php ENDPATH**/ ?>