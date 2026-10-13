<?php $__env->startSection('title', __('Google Analytics Control')); ?>
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
                            <h5 class="m-0 text-white"><?php echo app('translator')->get('Google Analytics Control'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <form action="<?php echo e(route('admin.google.analytics.control')); ?>" method="post">
                                        <?php echo csrf_field(); ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="MEASUREMENT_ID"><?php echo app('translator')->get('MEASUREMENT_ID'); ?></label>
                                                    <input type="text" name="MEASUREMENT_ID" value="<?php echo e(old('MEASUREMENT_ID',$basicControl->MEASUREMENT_ID)); ?>" placeholder="<?php echo app('translator')->get('MEASUREMENT_ID'); ?>"
                                                            class="form-control <?php $__errorArgs = ['MEASUREMENT_ID'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                    <div class="invalid-feedback"><?php $__errorArgs = ['MEASUREMENT_ID'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6 col-lg-6">
                                                <div class="form-group ">
                                                    <label><?php echo app('translator')->get('Google Analytics'); ?></label>
                                                    <div class="custom-switch-btn w-md-100">
                                                        <input type='hidden' value="1" name='analytic_status'>
                                                        <input type="checkbox" name="analytic_status" class="custom-switch-checkbox" id="analytic_status"  value = "0" <?php if( $basicControl->analytic_status == 0):echo 'checked'; endif ?> >
                                                        <label class="custom-switch-checkbox-label" for="analytic_status">
                                                            <span class="custom-switch-checkbox-inner"></span>
                                                            <span class="custom-switch-checkbox-switch"></span>
                                                        </label>
                                                    </div>
                                                    <?php $__errorArgs = ['analytic_status'];
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

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/plugin_panel/analyticControl.blade.php ENDPATH**/ ?>