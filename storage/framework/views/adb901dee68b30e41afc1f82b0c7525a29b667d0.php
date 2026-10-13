<?php $__env->startSection('title','Register'); ?>
<?php $__env->startSection('content'); ?>
    <section class="login-section auth-page auth-register-page">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 p-0">
                    <div class="text-box h-100">
                        <div class="overlay h-100">
                            <div class="text">
                                <h2><?php echo app('translator')->get('create an account'); ?></h2>
                                <p><?php echo app('translator')->get('Create your player account to follow matches, place bets and manage your wallet.'); ?></p>
                                <a href="<?php echo e(route('home')); ?>"><?php echo app('translator')->get('back to home'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="<?php echo e(route('register')); ?>" method="post" class="auth-form-card">
                            <?php echo csrf_field(); ?>
                            <div class="row g-4">
                                <div class="col-12 auth-form-heading">
                                    <span class="auth-eyebrow"><?php echo app('translator')->get('NEW PLAYER'); ?></span>
                                    <h4><?php echo app('translator')->get('Create your account'); ?></h4>
                                    <p><?php echo app('translator')->get('A few details are all you need to get started.'); ?></p>
                                </div>
                                <?php if(session()->get('sponsor') != null): ?>
                                    <div class="input-box col-12">
                                        <label class="auth-field-label" for="register-sponsor"><?php echo app('translator')->get('Sponsor Name'); ?></label>
                                        <input type="text" name="sponsor" class="form-control" id="register-sponsor"
                                               placeholder="<?php echo e(trans('Sponsor By')); ?>"
                                               value="<?php echo e(session()->get('sponsor')); ?>" readonly>
                                    </div>
                                <?php endif; ?>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-firstname"><?php echo app('translator')->get('First name'); ?></label>
                                    <input
                                        type="text"
                                        name="firstname"
                                        value="<?php echo e(old('firstname')); ?>"
                                        class="form-control"
                                        id="register-firstname" autocomplete="given-name"
                                        placeholder="<?php echo app('translator')->get('First name'); ?>" required/>
                                    <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger  mt-1"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-lastname"><?php echo app('translator')->get('Last name'); ?></label>
                                    <input
                                        type="text"
                                        name="lastname"
                                        value="<?php echo e(old('lastname')); ?>"
                                        class="form-control"
                                        id="register-lastname" autocomplete="family-name"
                                        placeholder="<?php echo app('translator')->get('Last name'); ?>" required/>
                                    <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger  mt-1"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-username"><?php echo app('translator')->get('Username'); ?></label>
                                    <input
                                        type="text"
                                        name="username"
                                        value="<?php echo e(old('username')); ?>"
                                        class="form-control"
                                        id="register-username" autocomplete="username"
                                        placeholder="<?php echo app('translator')->get('Choose a username'); ?>" required/>
                                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger  mt-1"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-email"><?php echo app('translator')->get('Email address'); ?></label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="<?php echo e(old('email')); ?>"
                                        class="form-control"
                                        id="register-email" autocomplete="email"
                                        placeholder="<?php echo app('translator')->get('you@example.com'); ?>" required/>
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger  mt-1"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-phone-code"><?php echo app('translator')->get('Country calling code'); ?></label>
                                    <?php
                                        $country_code = (string) @getIpInfo()['code'] ?: null;
                                        $myCollection = collect(config('country'))->map(function($row) {
                                            return collect($row);
                                        });
                                        $countries = $myCollection->sortBy('code');
                                    ?>
                                    <select
                                        class="form-select country_code dialCode-change" name="phone_code"
                                        aria-label="<?php echo app('translator')->get('Country calling code'); ?>" id="register-phone-code" required>
                                        <option selected="" disabled><?php echo app('translator')->get('Select Code'); ?></option>
                                        <?php $__currentLoopData = config('country'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($value['phone_code']); ?>"
                                                    data-name="<?php echo e($value['name']); ?>"
                                                    data-code="<?php echo e($value['code']); ?>"
                                                <?php echo e($country_code == $value['code'] ? 'selected' : ''); ?>> <?php echo e($value['name']); ?>

                                                (<?php echo e($value['phone_code']); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-phone"><?php echo app('translator')->get('Phone number'); ?></label>
                                    <input
                                        type="text"
                                        name="phone" value="<?php echo e(old('phone')); ?>"
                                        class="form-control dialcode-set"
                                        id="register-phone" autocomplete="tel-national" inputmode="tel"
                                        placeholder="712 345 678" required/>
                                    <?php $__errorArgs = ['phone'];
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
                                <input type="hidden" name="country_code" value="<?php echo e(old('country_code', $country_code)); ?>"
                                       class="text-dark">
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-password"><?php echo app('translator')->get('Password'); ?></label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        id="register-password" autocomplete="new-password"
                                        placeholder="<?php echo app('translator')->get('Create a password'); ?>" required/>
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
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-password-confirm"><?php echo app('translator')->get('Confirm password'); ?></label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        class="form-control"
                                        id="register-password-confirm" autocomplete="new-password"
                                        placeholder="<?php echo app('translator')->get('Confirm your password'); ?>" required/>
                                </div>
                                <?php if(basicControl()->reCaptcha_status_registration): ?>
                                    <div class="col-md-6 box mb-4 form-group">
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
                            </div>
                            <button type="submit" class="btn-custom w-100 mt-2"><?php echo app('translator')->get('sign up'); ?></button>
                            <div class="bottom">
                                <?php echo app('translator')->get('Already have an account?'); ?>

                                <a href="<?php echo e(route('login')); ?>"><?php echo app('translator')->get('Login here'); ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script'); ?>
    <script>
        "use strict";
        $(document).ready(function () {
            setDialCode();
            $(document).on('change', '.dialCode-change', function () {
                setDialCode();
            });

            function setDialCode() {
                let selected = $('.dialCode-change option:selected');
                $('input[name="country_code"]').val(selected.data('code') || '');
            }

        });

    </script>
<?php $__env->stopPush(); ?>

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

<?php echo $__env->make($theme.'layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/auth/register.blade.php ENDPATH**/ ?>