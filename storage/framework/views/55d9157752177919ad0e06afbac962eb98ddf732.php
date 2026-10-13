<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Success</title>
    <link href='https://fonts.googleapis.com/css?family=Lato:300,400|Montserrat:700' rel='stylesheet' type='text/css'>
    <link href="<?php echo e(asset('assets/admin/css/success-failed.css')); ?>" rel="stylesheet" type="text/css">
</head>
<body <?php if(session()->get('dark-mode') == 'true'): ?> class="dark-mode"  <?php endif; ?>>
<header class="site-header" id="header">
    <h1 class="site-header__title" data-lead-id="site-header-title"><?php echo app('translator')->get('THANK YOU!'); ?></h1>
</header>

<div class="main-content">
    <i class="fa fa-check main-content__checkmark" id="checkmark"></i>
    <p class="main-content__body" data-lead-id="main-content-body"><?php echo app('translator')->get('Thanks a bunch for filling that out. It means a lot
        to us, just like you do! We really appreciate you giving us a moment of your time today. Thanks for being
        with us.'); ?></p>
</div>

<footer class="site-footer" id="footer">
    <a href="<?php echo e(url('/')); ?>"><?php echo app('translator')->get('Go back to Home'); ?></a>
    <p class="site-footer__fineprint" id="fineprint"><?php echo app('translator')->get('Copyright'); ?> ©<?php echo e(date('Y')); ?> | <?php echo app('translator')->get('All Rights
        Reserved'); ?> <a href="<?php echo e(url('/')); ?>" class="site_title"><?php echo e($basic->site_title ?? 'Photoica'); ?></a></p>
</footer>

</body>
</html>
<?php /**PATH /home/runner/workspace/resources/views/success.blade.php ENDPATH**/ ?>