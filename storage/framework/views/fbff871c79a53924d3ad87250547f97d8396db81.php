<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card secbg br-4">
                <div class="card-body br-4">
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
                                    class="btn-custom"
                                    id="btn-confirm"><?php echo app('translator')->get('Pay Now'); ?></button>
                            <form action="<?php echo e(route('ipn', [optional($order->gateway)->code, $order->transaction])); ?>" method="POST" class="form">
                                <?php echo csrf_field(); ?>
                                <script
                                    src="//js.paystack.co/v1/inline.js"
                                    data-key="<?php echo e($data->key); ?>"
                                    data-email="<?php echo e($data->email); ?>"
                                    data-amount="<?php echo e($data->amount); ?>"
                                    data-currency="<?php echo e($data->currency); ?>"
                                    data-ref="<?php echo e($data->ref); ?>"
                                    data-custom-button="btn-confirm">
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/paystack.blade.php ENDPATH**/ ?>