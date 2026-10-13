<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <section id="dashboard">
        <div class="container add-fund">
            <div class="row">
                <div class="col-md-12">
                    <div class="card secbg">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img
                                        src="<?php echo e(getFile(config('location.gateway.path').optional($order->gateway)->image)); ?>"
                                        class="card-img-top gateway-img br-4" alt="..">
                                </div>

                                <div class="col-md-9">
                                    <h4><?php echo app('translator')->get('Please Pay'); ?> <?php echo e(getAmount($order->final_amount)); ?> <?php echo e($order->gateway_currency); ?></h4>
                                    <h4 class="mt-15 mb-15"><?php echo app('translator')->get('To Get'); ?> <?php echo e(getAmount($order->amount)); ?>  <?php echo e($basic->currency); ?></h4>

                                    <button type="button"
                                            class="btn-custom mt-3"
                                            id="pay-button"><?php echo app('translator')->get('Pay Now'); ?>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="<?php echo e($data->client_key); ?>"></script>

    <script defer>
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            window.snap.pay("<?php echo e($data->token); ?>");
        });
    </script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/midtrans.blade.php ENDPATH**/ ?>