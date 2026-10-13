<?php $__env->startSection('title','Admin Reset Password'); ?>

<?php $__env->startSection('content'); ?>
    <div class="p-3">
        <div class="text-center">
            <img src=" <?php echo e(getFile(config('location.logoIcon.path').'favicon.png')); ?>" alt="wrapkit">
        </div>
        <h2 class="mt-3 text-center"><?php echo app('translator')->get('Reset Password'); ?></h2>

        <form method="POST" action="<?php echo e(route('admin.password.update')); ?>">
            <?php echo csrf_field(); ?>

            <input type="hidden" name="token" value="<?php echo e($token); ?>">
            <input type="hidden" name="email" value="<?php echo e($email); ?>">


            <div class="row mb-5">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="text-dark"><?php echo app('translator')->get('Password'); ?></label>
                        <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-danger"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label class="text-dark"><?php echo app('translator')->get('Confirm Password'); ?></label>
                        <input id="password" type="password" class="form-control" name="password_confirmation" required autocomplete="current-password">
                    </div>

                </div>

                <div class="col-lg-12 text-center">
                    <button type="submit" class="btn btn-block btn-dark"><?php echo app('translator')->get('Reset Password'); ?></button>
                </div>

            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('css'); ?>
    <style>
        .auth-wrapper .auth-box {
            min-width: 800px;
        }
    </style>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin.layouts.login', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/auth/passwords/reset.blade.php ENDPATH**/ ?>