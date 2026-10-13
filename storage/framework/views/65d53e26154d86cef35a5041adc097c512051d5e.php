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
                            <button class="btn-custom  mt-5"
                                    onclick="payWithMonnify()"><?php echo app('translator')->get('Pay via Monnify'); ?>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('script'); ?>
        <script type="text/javascript" src="//sdk.monnify.com/plugin/monnify.js"></script>
        <script type="text/javascript">
            function payWithMonnify() {
                MonnifySDK.initialize({
                    amount: <?php echo e($data->amount ?? '0'); ?>,
                    currency: "<?php echo e($data->currency ?? 'NGN'); ?>",
                    reference: "<?php echo e($data->ref); ?>",
                    customerName: "<?php echo e($data->customer_name ?? 'John Doe'); ?>",
                    customerEmail: "<?php echo e($data->customer_email ?? 'example@example.com'); ?>",
                    customerMobileNumber: "<?php echo e($data->customer_phone ?? '0123'); ?>",
                    apiKey: "<?php echo e($data->api_key); ?>",
                    contractCode: "<?php echo e($data->contract_code); ?>",
                    paymentDescription: "<?php echo e($data->description); ?>",
                    isTestMode: true,
                    onComplete: function (response) {
                        if (response.paymentReference) {
                            window.location.href = '<?php echo e(route('ipn', ['monnify', $data->ref])); ?>';
                        } else {
                            window.location.href = '<?php echo e(route('failed')); ?>';
                        }
                    },
                    onClose: function (data) {
                    }
                });
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/monnify.blade.php ENDPATH**/ ?>