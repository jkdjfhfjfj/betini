<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startPush('style'); ?>
        <link href="<?php echo e(asset('assets/admin/css/card-js.min.css')); ?>" rel="stylesheet" type="text/css"/>
        <style>
            .card-js .icon {
                top: 5px;
            }
        </style>
    <?php $__env->stopPush(); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card secbg br-4">
                <div class="card-body ">
                    <div class="row ">

                        <div class="col-md-12">

                            <h4 class="card-title text-center mb-4"> <?php echo app('translator')->get('Your Card Information'); ?></h4>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <img
                                    src="<?php echo e(getFile(config('location.gateway.path').optional($order->gateway)->image)); ?>"
                                    class="card-img-top gateway-img" alt="...">
                            </div>
                        </div>

                        <div class="col-md-9">
                            <form class="form-horizontal" id="example-form"
                                  action="<?php echo e(route('ipn', [optional($order->gateway)->code ?? '', $order->transaction])); ?>"
                                  method="post">
                                <div class="card-js form-group --payment-card">
                                    <input class="card-number form-control"
                                           name="card_number"
                                           placeholder="<?php echo app('translator')->get('Enter your card number'); ?>"
                                           autocomplete="off"
                                           required>
                                    <input class="name form-control"
                                           id="the-card-name-id"
                                           name="card_name"
                                           placeholder="<?php echo app('translator')->get('Enter the name on your card'); ?>"
                                           autocomplete="off"
                                           required>
                                    <input class="expiry form-control"
                                           autocomplete="off"
                                           required>
                                    <input class="expiry-month" name="expiry_month">
                                    <input class="expiry-year" name="expiry_year">
                                    <input class="cvc form-control"
                                           name="card_cvc"
                                           autocomplete="off"
                                           required>
                                </div>
                                <button type="submit" class="btn-custom mt-3"><?php echo app('translator')->get('Submit'); ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('script'); ?>
        <script src="<?php echo e(asset('assets/admin/js/card-js.min.js')); ?>"></script>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/card.blade.php ENDPATH**/ ?>