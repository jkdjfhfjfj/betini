<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <?php echo $__env->make('user.layouts.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
<?php echo $__env->make('user.layouts.notification', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <section class="login">
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative"
             style="background:url(<?php echo e(url('assets/backend/images/big/auth-bg.jpg')); ?>) no-repeat center center;">
            <div class="auth-box row">
                <div class="col-lg-7 col-md-5 modal-bg-img" style="background-image: url(<?php echo e(url('assets/backend/images/big/3.jpg')); ?>);">
                </div>
                <div class="col-lg-5 col-md-7 bg-white">
                    <div class="p-3">
                        <div class="text-center">
                            <?php if(session('status')): ?>
                                <div class="alert alert-success" role="alert"><?php echo e(session('status')); ?></div>
                            <?php endif; ?>
                            <img src="<?php echo e(url('assets/backend/images/big/icon.png')); ?>" alt="wrapkit">
                        </div>
                        <h2 class="mt-3 text-center"><?php echo app('translator')->get('Reset Password'); ?></h2>
                        <form method="POST" action="<?php echo e(route('password.email')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="text-dark">E-Mail Address</label>
                                        <input id="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e(old('email')); ?>" placeholder="E-Mail Address" required autocomplete="email" autofocus>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="invalid-feedback" role="alert"><strong><?php echo e($message); ?></strong></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-block btn-dark btn-sm"><?php echo app('translator')->get('Send Password Reset Link'); ?></button>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="text-center mt-5">
                                        <p><?php echo app('translator')->get("Don't have an account?"); ?> <a href="<?php echo e(route('register')); ?>" class="text-danger"><?php echo app('translator')->get('Sign Up'); ?></a></p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php echo $__env->make('user.layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script src="<?php echo e(asset('assets/backend/libs/jquery/dist/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/backend/libs/bootstrap/dist/js/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/backend/js/app-style-switcher.js')); ?>"></script>
</body>
<?php /**PATH /home/runner/workspace/resources/views/auth/passwords/email.blade.php ENDPATH**/ ?>