<?php $__env->startSection('title', __('Google reCaptcha Control')); ?>
<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <div class="row mt-sm-4 justify-content-center">
            <div class="col-12 col-md-4 col-lg-4">
                <?php echo $__env->make('admin.plugin_panel.components.sidebar', ['settings' => config('generalsettings.plugin'), 'suffix' => ''], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            <div class="col-12 col-md-8 col-lg-8">
                <div class="container-fluid" id="container-wrapper">
                    <div class="card mb-4 card-primary shadow">
                        <div class="card-header bg-dark py-3 d-flex flex-row align-items-center justify-content-between">
                            <h5 class="m-0 text-white"><?php echo app('translator')->get('Google reCaptcha Control'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <form action="<?php echo e(route('admin.google.recaptcha.control')); ?>" method="post">
                                        <?php echo csrf_field(); ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="NOCAPTCHA_SECRET"><?php echo app('translator')->get('NOCAPTCHA SECRET'); ?></label>
                                                    <input type="text" name="NOCAPTCHA_SECRET" value="<?php echo e(old('NOCAPTCHA_SECRET',env('NOCAPTCHA_SECRET'))); ?>" placeholder="<?php echo app('translator')->get('NOCAPTCHA_SECRET'); ?>"
                                                            class="form-control <?php $__errorArgs = ['NOCAPTCHA_SECRET'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                    <div class="invalid-feedback"><?php $__errorArgs = ['NOCAPTCHA_SECRET'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="NOCAPTCHA_SITEKEY"><?php echo app('translator')->get('NOCAPTCHA SITEKEY'); ?></label>
                                                    <input type="text" name="NOCAPTCHA_SITEKEY" value="<?php echo e(old('NOCAPTCHA_SITEKEY',env('NOCAPTCHA_SITEKEY'))); ?>" placeholder="<?php echo app('translator')->get('NOCAPTCHA SITEKEY'); ?>"
                                                            class="form-control <?php $__errorArgs = ['NOCAPTCHA_SITEKEY'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                    <div class="invalid-feedback"><?php $__errorArgs = ['NOCAPTCHA_SITEKEY'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group ">
                                                    <label><?php echo app('translator')->get('Login Status'); ?></label>
                                                    <div class="custom-switch-btn w-md-100">
                                                        <input type='hidden' value="1" name='reCaptcha_status_login'>
                                                        <input type="checkbox" name="reCaptcha_status_login" class="custom-switch-checkbox" id="reCaptcha_status_login"  value = "0" <?php if( $basicControl->reCaptcha_status_login == 0):echo 'checked'; endif ?> >
                                                        <label class="custom-switch-checkbox-label" for="reCaptcha_status_login">
                                                            <span class="custom-switch-checkbox-inner"></span>
                                                            <span class="custom-switch-checkbox-switch"></span>
                                                        </label>
                                                    </div>
                                                    <?php $__errorArgs = ['reCaptcha_status_login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <span class="text-danger"><?php echo e($message); ?></span>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6 col-lg-6">
                                                <div class="form-group ">
                                                    <label><?php echo app('translator')->get('Registration Status'); ?></label>
                                                    <div class="custom-switch-btn w-md-100">
                                                        <input type='hidden' value="1" name='reCaptcha_status_registration'>
                                                        <input type="checkbox" name="reCaptcha_status_registration" class="custom-switch-checkbox" id="reCaptcha_status_registration"  value = "0" <?php if( $basicControl->reCaptcha_status_registration == 0):echo 'checked'; endif ?> >
                                                        <label class="custom-switch-checkbox-label" for="reCaptcha_status_registration">
                                                            <span class="custom-switch-checkbox-inner"></span>
                                                            <span class="custom-switch-checkbox-switch"></span>
                                                        </label>
                                                    </div>
                                                    <?php $__errorArgs = ['reCaptcha_status_registration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <span class="text-danger"><?php echo e($message); ?></span>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mt-2">
                                            <button type="submit" name="submit" class="btn btn-primary btn-rounded btn-block"><?php echo app('translator')->get('Save changes'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/plugin_panel/googleReCaptchaControl.blade.php ENDPATH**/ ?>