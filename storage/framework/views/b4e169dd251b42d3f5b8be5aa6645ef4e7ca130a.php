<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card secbg">
                <div class="card-body ">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <img
                                src="<?php echo e(getFile(config('location.gateway.path').optional($order->gateway)->image)); ?>"
                                class="card-img-top gateway-img" alt="..">
                        </div>

                        <div class="col-md-9">
                            <h3 class="my-3  text-center"><?php echo app('translator')->get('Please Pay'); ?> <?php echo e(getAmount($order->final_amount)); ?> <?php echo e($order->gateway_currency); ?></h3>
                            <h3 class="text-center"><?php echo app('translator')->get('To Get'); ?> <?php echo e(getAmount($order->amount)); ?>  <?php echo e($basic->currency); ?></h3>

                            <form action="<?php echo e(route('ipn', [optional($order->gateway)->code ?? 'mercadopago', $order->transaction])); ?>"
                                method="POST">
                                <script
                                    src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
                                    data-preference-id="<?php echo e($data->preference); ?>">
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/mercado.blade.php ENDPATH**/ ?>