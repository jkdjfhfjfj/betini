<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card secbg">
                <div class="card-body text-center">
                    <h3 class="card-title"><?php echo app('translator')->get('Payment Preview'); ?></h3>

                    <h4> <?php echo app('translator')->get('PLEASE SEND EXACTLY'); ?> <span
                            class="text-success"> <?php echo e(getAmount($data->amount)); ?></span> <?php echo e($data->currency); ?>

                    </h4>
                    <h5><?php echo app('translator')->get('TO'); ?> <span class="text-success"> <?php echo e($data->sendto); ?></span></h5>
                    <img class="" src="<?php echo e($data->img); ?>" alt="...">
                    <h4 class="text-color font-weight-bold"><?php echo app('translator')->get('SCAN TO SEND'); ?></h4>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/crypto.blade.php ENDPATH**/ ?>