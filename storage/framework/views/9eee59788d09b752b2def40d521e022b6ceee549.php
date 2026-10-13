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
                                class="card-img-top gateway-img" alt="...">
                        </div>

                        <div class="col-md-9">
                            <h4><?php echo app('translator')->get('Please Pay'); ?> <?php echo e(getAmount($order->final_amount)); ?> <?php echo e($order->gateway_currency); ?></h4>
                            <h4 class="mt-15 mb-15"><?php echo app('translator')->get('To Get'); ?> <?php echo e(getAmount($order->amount)); ?>  <?php echo e($basic->currency); ?></h4>

                            <form action="<?php echo e($data->url); ?>" method="<?php echo e($data->method); ?>" class="form">
                                <script src="<?php echo e($data->checkout_js); ?>"
                                        <?php $__currentLoopData = $data->val; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            data-<?php echo e($key); ?>="<?php echo e($value); ?>"
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> >
                                </script>
                                <input type="hidden" custom="<?php echo e($data->custom); ?>" name="hidden">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php $__env->startPush('script'); ?>
        <script>
            $(document).ready(function () {
                $('input[type="submit"]').addClass(" btn-custom");
            })
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>





<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/razorpay.blade.php ENDPATH**/ ?>