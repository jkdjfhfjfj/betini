<?php $__env->startSection('title', 'Warning'); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-8">

            <div class="card secbg">
                <div class="card-body ">
                    <h1 class="text-center text-warning mt-5"><i
                            class="fa fa-warning"></i>
                        <?php echo app('translator')->get('Warning'); ?>
                    </h1>
                    <h4 class="text-center"><?php echo app('translator')->get('Uh-ho! We are unable to process your Payment by this method.
                        <br>This method is under construction!!'); ?>
                    </h4>
                    <br>
                    <h4 class="text-center"><?php echo app('translator')->get('Select'); ?> <b><?php echo app('translator')->get('bkash'); ?></b> <?php echo app('translator')->get('as your payment method.'); ?></h4>
                    <div class="col-md-8 col-md-offset-2">

                        <div class="panel panel-info">
                            <div class="panel-body">
                                <div class="text-center">
                                    <a href="<?php echo e(route('addFund',["bkash",session()->get('id')])); ?>">
                                        <img class="w-100" src="<?php echo e(asset('assets/upload/logo/bkash.png')); ?>"
                                             style="max-width: 100px;">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/cardWarning.blade.php ENDPATH**/ ?>