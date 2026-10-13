<?php $__env->startSection('title','Verification'); ?>

<?php $__env->startSection('content'); ?>
    <!-- LOGIN-SIGNUP -->
    <section style="padding: 120px 0"id="login-signup">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center justify-content-start">
                        <div class="wrapper">
                            <div class="login-info-wrapper">

                                <img class="w-100"src="<?php echo e(asset(template(true).'images/verification.jpg')); ?>" alt="..." class="w-100">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-lg-7">
                    <div
                        class="form-wrapper w-100 h-100 d-flex flex-column align-items-start justify-content-center pl-65">
                        <h4 class="h4 text-uppercase mb-30"><?php echo app('translator')->get('Email Verification'); ?></h4>

                        <form method="POST" action="" class="form-content w-100">
                            <?php echo csrf_field(); ?>
                            <div class="login">
                                <div class="form-group">
                                    <input class="form-control" type="text" name="username" value="<?php echo e(old('username')); ?>"
                                           placeholder="<?php echo app('translator')->get('Code'); ?>">
                                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-danger  mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                            </div>
                            <button class="btn mt-20" type="submit"><?php echo app('translator')->get('Submit'); ?></button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- /LOGIN-SIGNUP -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/check.blade.php ENDPATH**/ ?>