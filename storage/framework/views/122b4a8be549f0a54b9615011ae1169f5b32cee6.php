<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Failed</title>
    <link href='https://fonts.googleapis.com/css?family=Lato:300,400|Montserrat:700' rel='stylesheet' type='text/css'>
    <link href="<?php echo e(asset('assets/admin/css/success-failed.css')); ?>" rel="stylesheet" type="text/css">
</head>
<body <?php if(session()->get('dark-mode') == 'true'): ?> class="dark-mode"  <?php endif; ?>>
<header class="site-header" id="header">
    <h1 class="site-header__title" data-lead-id="site-header-title">Sorry!</h1>
</header>

<div class="main-content">
    <i class="fa fa-times main-content__times" id="checkmark"></i>
    <p class="main-content__body" data-lead-id="main-content-body">We really appreciate you giving us a moment of your
        time today but unfortunately the payment was unsuccessful due
        to <?php echo e(session('error') ?? 'it seems some issue in server to server communication. Kindly connect with administrator'); ?></p>
</div>
<footer class="site-footer" id="footer">
    <a href="<?php echo e(url('/')); ?>">Go back to Home</a>
    <p class="site-footer__fineprint" id="fineprint">Copyright ©<?php echo e(date('Y')); ?> | All Rights Reserved <a href="<?php echo e(url('/')); ?>"><?php echo e($basic->site_title ?? 'Photoica'); ?></a></p>
</footer>
</body>
</html>
<?php /**PATH /home/runner/workspace/resources/views/failed.blade.php ENDPATH**/ ?>