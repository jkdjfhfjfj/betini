<?php $__env->startSection('title','Login'); ?>
<?php $__env->startSection('content'); ?>
    <section class="login-section auth-page auth-login-page">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 p-0">
                    <div class="text-box h-100">
                        <div class="overlay h-100">
                            <div class="text">
                                <h2><?php echo app('translator')->get('Sign in to your account'); ?></h2>
                                <p><?php echo app('translator')->get('Pick up where you left off. Your bets and wallet are ready when you are.'); ?></p>
                                <a href="<?php echo e(route('home')); ?>"><?php echo app('translator')->get('back to home'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="<?php echo e(route('login')); ?>" method="post" class="auth-form-card">
                            <?php echo csrf_field(); ?>
                            <div class="row g-4">
                                <div class="col-12 auth-form-heading">
                                    <span class="auth-eyebrow"><?php echo app('translator')->get('PLAYER ACCOUNT'); ?></span>
                                    <h4><?php echo app('translator')->get('Welcome back'); ?></h4>
                                    <p><?php echo app('translator')->get('Sign in to continue to your account.'); ?></p>
                                </div>
                                <div class="input-box col-12">
                                    <label class="auth-field-label" for="login-identity"><?php echo app('translator')->get('Email or username'); ?></label>
                                    <input type="text" class="form-control" name="username"
                                           id="login-identity" autocomplete="username"
                                           placeholder="<?php echo app('translator')->get('Enter your email or username'); ?>" required/>
                                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger  mt-1"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger  mt-1"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="input-box col-12">
                                    <label class="auth-field-label" for="login-password"><?php echo app('translator')->get('Password'); ?></label>
                                    <input type="password" class="form-control" name="password"
                                           id="login-password" autocomplete="current-password"
                                           placeholder="<?php echo app('translator')->get('Enter your password'); ?>" required/>
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger mt-1"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <?php if(basicControl()->reCaptcha_status_login): ?>
                                    <div class="box mb-4 form-group">
                                        <?php echo NoCaptcha::renderJs(session()->get('trans')); ?>

                                        <?php echo NoCaptcha::display(['data-theme' => 'dark']); ?>

                                        <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger mt-1"><?php echo app('translator')->get($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="col-12">
                                    <div class="links">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>

                                                id="flexCheckDefault"/>
                                            <label
                                                class="form-check-label"
                                                for="flexCheckDefault">
                                                <?php echo app('translator')->get('Remember Me'); ?>
                                            </label>
                                        </div>
                                        <a href="<?php echo e(route('password.request')); ?>"><?php echo app('translator')->get('Forgot password?'); ?></a>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-custom auth-submit w-100"><?php echo app('translator')->get('Sign in'); ?></button>
                            <div class="bottom">
                                <?php echo app('translator')->get("Don't have an account?"); ?>

                                <a href="<?php echo e(route('register')); ?>"><?php echo app('translator')->get('Create account'); ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('style'); ?>
    <style>
        .modal .text-box,
            .login-section .text-box {
                background-image: url(<?php echo e(getFile(config('location.logo.path').'loginImage.png')); ?>);
                background-size: cover;
                background-position: center;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make($theme.'layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/auth/login.blade.php ENDPATH**/ ?>