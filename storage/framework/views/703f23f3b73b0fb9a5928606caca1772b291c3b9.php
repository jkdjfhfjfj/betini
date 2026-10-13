<?php $__env->startSection('title', __('Tawk Control')); ?>
<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <div class="row mt-sm-4 justify-content-center">
            <div class="col-12 col-md-4 col-lg-4">
                <?php echo $__env->make('admin.plugin_panel.components.sidebar', ['settings' => config('generalsettings.plugin'), 'suffix' => ''], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            <div class="col-12 col-md-8 col-lg-8">
                <div class="container-fluid" id="container-wrapper">
                    <div class="card mb-4 card-primary shadow">
                        <div class="card-header py-3 d-flex flex-row align-items-center bg-dark justify-content-between">
                            <h5 class="m-0 text-white"><?php echo app('translator')->get('Tawk Control'); ?></h5>

                            <a href="https://youtu.be/d7jFXaMecYQ" target="_blank" class="btn btn-primary btn-sm  text-white ">
                                <span class="btn-label"><i class="fab fa-youtube"></i></span>
                                <?php echo app('translator')->get('How to set up it?'); ?>
                            </a>

                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <form action="<?php echo e(route('admin.tawk.control')); ?>" method="post">
                                        <?php echo csrf_field(); ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="tawk_id"><?php echo app('translator')->get('Tawk ID'); ?>
                                                        <?php
                                                        $htmlContent = "embed.tawk.to/[Your Tawk code and Widget ID]";
                                                        ?>
                                                        <a href="javascript:void(0)" data-container="body" title="<?php echo app('translator')->get('How to Get it'); ?>" data-toggle="popover" data-placement="top" data-content="<?php echo app('translator')->get($htmlContent); ?>">
                                                            <i class="fa fa-info-circle"></i>
                                                        </a>

                                                    </label>
                                                    <input type="text" name="tawk_id"
                                                            value="<?php echo e(old('tawk_id',$basicControl->tawk_id)); ?>"
                                                            placeholder="<?php echo app('translator')->get('Tawk ID'); ?>"
                                                            class="form-control <?php $__errorArgs = ['tawk_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                    <div class="invalid-feedback"><?php $__errorArgs = ['tawk_id'];
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
                                                    <label><?php echo app('translator')->get('Tawk Chat'); ?></label>
                                                    <div class="custom-switch-btn w-md-100">
                                                        <input type='hidden' value="1" name='tawk_status'>
                                                        <input type="checkbox" name="tawk_status" class="custom-switch-checkbox" id="tawk_status"  value = "0" <?php if( $basicControl->tawk_status == 0):echo 'checked'; endif ?> >
                                                        <label class="custom-switch-checkbox-label" for="tawk_status">
                                                            <span class="custom-switch-checkbox-inner"></span>
                                                            <span class="custom-switch-checkbox-switch"></span>
                                                        </label>
                                                    </div>
                                                    <?php $__errorArgs = ['tawk_status'];
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
                                            <button type="submit" name="submit"
                                                    class="btn btn-primary btn-rounded btn-block"><?php echo app('translator')->get('Save changes'); ?></button>
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

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/plugin_panel/tawkControl.blade.php ENDPATH**/ ?>