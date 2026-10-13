<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card secbg br-4">
                <div class="card-body text-center br-4">
                    <div id="paypal-button-container"></div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('script'); ?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo e($data->cleint_id); ?>">
        </script>
        <script>
            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [
                            {
                                description: "<?php echo e($data->description); ?>",
                                custom_id: "<?php echo e($data->custom_id); ?>",
                                amount: {
                                    currency_code: "<?php echo e($data->currency); ?>",
                                    value: "<?php echo e($data->amount); ?>",
                                    breakdown: {
                                        item_total: {
                                            currency_code: "<?php echo e($data->currency); ?>",
                                            value: "<?php echo e($data->amount); ?>"
                                        }
                                    }
                                }
                            }
                        ]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        var trx = "<?php echo e($data->custom_id); ?>";
                        window.location = '<?php echo e(url('payment/paypal')); ?>/' + trx + '/' + details.id
                    });
                }
            }).render('#paypal-button-container');
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/paypal.blade.php ENDPATH**/ ?>