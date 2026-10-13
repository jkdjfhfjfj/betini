<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('assets/uploads/logo/favicon.png')); ?>">
    <title><?php echo $__env->yieldContent('title'); ?></title>

    <link href="<?php echo e(asset('assets/admin/css/style.min.css')); ?>" rel="stylesheet">

    <?php echo $__env->yieldPushContent('css'); ?>



</head>
<body>
<header class="topbar header-user" data-navbarbg="skin6">
    <nav class="navbar top-navbar navbar-expand-md">
        <div class="navbar-header" data-logobg="skin6">
            <!-- This is for the sidebar toggle which is visible on mobile only -->
            <a class="nav-toggler waves-effect waves-light d-block d-md-none" data-toggle="collapse" data-target="#navbar-user"
               aria-controls="navbar-user" aria-expanded="false" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
            <div class="navbar-brand">
                <!-- Logo icon -->
                <a href="<?php echo e(route('user.home')); ?>">
                    <b class="logo-icon">
                        <!-- Dark Logo icon -->
                        <img src="<?php echo e(getFile(config('location.logoIcon.path').'logo.png')); ?>" alt="homepage" class="dark-logo">

                        <img src="<?php echo e(getFile(config('location.logoIcon.path').'logo.png')); ?>" alt="homepage" class="light-logo">
                    </b>
                </a>
            </div>
            <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
               data-toggle="collapse" data-target="#navbarSupportedContent"
               aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                    class="ti-more"></i></a>
        </div>
        <!-- ============================================================== -->
        <!-- End Logo -->
        <!-- ============================================================== -->

        <div class="navbar-collapse collapse" id="navbarSupportedContent">

            <ul class="navbar-nav float-left mr-auto ml-3 pl-1 d-none d-md-flex">
                <li class="nav-item <?php echo e(request()->is('user/home') ? 'active' : ''); ?>">
                    <a class="nav-link" href="<?php echo e(route('user.home')); ?>"><?php echo app('translator')->get('Statistics'); ?> </a>
                </li>
            </ul>

            <ul class="navbar-nav float-right">
                <li class="nav-item d-none d-md-block">
                    <a class="nav-link" href="javascript:void(0)">
                        <form>
                            <div class="customize-input">
                                <input class="form-control custom-shadow custom-radius border-0 bg-white"
                                       type="search" placeholder="Search" aria-label="Search">
                                <i class="form-control-icon" data-feather="search"></i>
                            </div>
                        </form>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <?php if(Auth::user()->image): ?>
                            <img src="<?php echo e(asset('assets/uploads/users/'.Auth::user()->image )); ?>" alt="<?php echo e(Auth::user()->name); ?>" class="rounded-circle" width="40px">
                        <?php endif; ?>

                        <span class="ml-2 d-none d-lg-inline-block"><span>Hello,</span> <span
                                class="text-dark"><?php echo e(Auth::user()->name); ?></span> <i data-feather="chevron-down"
                                                                                     class="svg-icon"></i></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                        <a class="dropdown-item" href="<?php echo e(route('logout')); ?>"
                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"><i data-feather="power" class="svg-icon mr-2 ml-1"></i>
                            <?php echo e(__('Logout')); ?>

                        </a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                            <?php echo csrf_field(); ?>
                        </form>

                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- User profile and search -->
                <!-- ============================================================== -->
            </ul>
        </div>

    </nav>

</header>

<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?php echo e(url('/')); ?>">
                <?php echo app('translator')->get('Please Verify Your Email.'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?php echo e(__('Toggle navigation')); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    <?php if(auth()->guard()->guest()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('login')); ?>"><?php echo e(__('Login')); ?></a>
                        </li>
                        <?php if(Route::has('register')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('register')); ?>"><?php echo e(__('Register')); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <?php echo e(Auth::user()->name); ?>

                            </a>
                            <?php echo app('translator')->get('Before proceeding, please check your email for a verification link.'); ?>
                            <?php echo app('translator')->get('If you did not receive the email'); ?>,
                            <form class="d-inline" method="POST" action="<?php echo e(route('verification.resend')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline"><?php echo e(__('click here to request another')); ?></button>.
                            </form>

                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>



<script src="<?php echo e(asset('assets/admin/js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/admin/js/popper.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/admin/js/bootstrap.min.js')); ?>"></script>
<?php echo $__env->yieldPushContent('js-lib'); ?>
<script src="<?php echo e(asset('assets/admin/js/app-style-switcher.js')); ?>"></script>
<script src="<?php echo e(asset('assets/admin/js/feather.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/admin/js/toastr.min.js')); ?>"></script>
<?php echo $__env->yieldPushContent('js'); ?>
<?php echo $__env->make('admin.layouts.notification', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</body>
</html>
<?php /**PATH /home/runner/workspace/resources/views/layouts/app.blade.php ENDPATH**/ ?>