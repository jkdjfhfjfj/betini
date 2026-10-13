<?php $__env->startSection('title',trans($page_title)); ?>
<?php $__env->startPush('style'); ?>
    <link href="<?php echo e(asset('assets/admin/css/select2.min.css')); ?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/bootstrap-select.min.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
                <div class="card-body">

                    <div class="media mb-4 justify-content-end">
                        <a href="<?php echo e(route('admin.payout-method')); ?>" class="btn btn-sm  btn-primary mr-2">
                            <span><i class="fas fa-arrow-left"></i> <?php echo app('translator')->get('Back'); ?></span>
                        </a>
                    </div>


                    <form method="post" action="<?php echo e(route('admin.payout-method.update', $method->id)); ?>"
                          enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('put'); ?>
                        <div class="row">
                            <div class="form-group col-md-6 col-6">
                                <label><?php echo e(trans('Name')); ?></label>
                                <input type="text" class="form-control"
                                       name="name"
                                       value="<?php echo e(old('name', $method->name ?? '')); ?>" required>

                                <?php $__errorArgs = ['name'];
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

                            <div class="form-group col-md-6 col-6">
                                <label> <?php echo e(trans('Duration')); ?> </label>
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           name="duration"
                                           value="<?php echo e(old('duration', $method->duration)); ?>"
                                           required="">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <?php echo e(trans('Hour / Minutes/ Days ')); ?>

                                        </div>
                                    </div>
                                </div>

                                <?php $__errorArgs = ['duration'];
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
                        <?php if($method->is_automatic == 1): ?>
                            <div class="row">
                                <?php if($method->bank_name): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency"><?php echo app('translator')->get('Bank'); ?></label>
                                            <select
                                                class="form-select form-control"
                                                name="banks[]" multiple="multiple" id="selectCurrency"
                                                required>
                                                <?php $__currentLoopData = $method->bank_name; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php $__currentLoopData = $bank; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $curKey => $singleBank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($curKey); ?>"
                                                                <?php echo e(in_array($curKey,$method->banks) == true ? 'selected' : ''); ?> data-fiat="<?php echo e($key); ?>"
                                                                required><?php echo e(trans($curKey)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                <?php $__errorArgs = ['banks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if($method->currency_lists): ?>
                                    <div
                                        class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency"><?php echo app('translator')->get('Supported Currency'); ?></label>
                                            <select
                                                class="form-select form-control <?php $__errorArgs = ['supported_currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="supported_currency[]" multiple="multiple"
                                                id="selectSupportedCurrency"
                                                required>
                                                <?php $__currentLoopData = $method->currency_lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option
                                                        value="<?php echo e($key); ?>"
                                                        <?php $__currentLoopData = $method->supported_currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($sup == $currency): ?>
                                                                selected
                                                        <?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e($currency); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                <?php $__errorArgs = ['currency_lists'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="form-group col-md-6 col-6">
                                <label><?php echo e(trans('Minimum Amount')); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           name="minimum_amount"
                                           value="<?php echo e(old('minimum_amount', round($method->minimum_amount, 2) ?? '')); ?>"
                                           required="">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <?php echo e($basic->currency ?? 'USD'); ?>

                                        </div>
                                    </div>
                                </div>

                                <?php $__errorArgs = ['minimum_amount'];
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


                            <div class="form-group col-md-6 col-6">
                                <label><?php echo e(trans('Maximum Amount')); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           name="maximum_amount"
                                           value="<?php echo e(old('maximum_amount', round($method->maximum_amount, 2) ?? '')); ?>"
                                           required="">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <?php echo e($basic->currency ?? 'USD'); ?>

                                        </div>
                                    </div>
                                </div>

                                <?php $__errorArgs = ['maximum_amount'];
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

                        <div class="row">
                            <div class="form-group col-md-6 col-6">
                                <label><?php echo app('translator')->get('Percent Charge'); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           name="percent_charge"
                                           value="<?php echo e(old('percent_charge', round($method->percent_charge, 2) ?? '')); ?>"
                                           required="">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            %
                                        </div>
                                    </div>
                                </div>
                                <?php $__errorArgs = ['percent_charge'];
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

                            <div class="form-group col-md-6 col-6">
                                <label><?php echo app('translator')->get('Fixed Charge'); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           name="fixed_charge"
                                           value="<?php echo e(old('fixed_charge', round($method->fixed_charge, 2) ?? '')); ?>"
                                           required="">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <?php echo e($basic->currency ?? 'USD'); ?>

                                        </div>
                                    </div>
                                </div>

                                <?php $__errorArgs = ['fixed_charge'];
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

                        <?php if($method->is_automatic == 1): ?>
                            <?php if($method->supported_currency): ?>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="card card-primary shadow params-color">
                                            <div
                                                class="card-header text-dark font-weight-bold"> <?php echo app('translator')->get('Conversion Rate'); ?></div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <?php $__currentLoopData = $method->supported_currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-append">
																			<span
                                                                                class="form-control"><?php echo app('translator')->get('1 '); ?><?php echo e(config('basic.currency')); ?> =</span>
                                                                    </div>
                                                                    <input type="text"
                                                                           name="rate[<?php echo e($key); ?>]"
                                                                           step="0.001"
                                                                           class="form-control"
                                                                           <?php $__currentLoopData = $method->convert_rate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1 => $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                               <?php
                                                                                   if($key == $key1){
                                                                                       $rate = $rate;
                                                                                       break;
                                                                                   }else{
                                                                                       $rate =1;
                                                                                   }
                                                                               ?>
                                                                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                           value="<?php echo e($rate); ?>">
                                                                    <div class="input-group-prepend">
																				<span
                                                                                    class="form-control"><?php echo e($currency); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($method->is_automatic == 1): ?>
                            <?php if($method->parameters): ?>
                                <div class="row mt-4">
                                    <?php $__currentLoopData = $method->parameters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $parameter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label
                                                    for="<?php echo e($key); ?>"><?php echo e(__(strtoupper(str_replace('_',' ', $key)))); ?></label>
                                                <input type="text" name="<?php echo e($key); ?>"
                                                       value="<?php echo e(old($key, $parameter)); ?>"
                                                       id="<?php echo e($key); ?>"
                                                       class="form-control <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <div class="invalid-feedback">
                                                    <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                            <?php if($method->extra_parameters): ?>
                                <div class="row">
                                    <?php $__currentLoopData = $method->extra_parameters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $param): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="<?php echo e($key); ?>"><?php echo e(__(strtoupper(str_replace('_',' ', $key)))); ?></label>
                                                <div class="input-group">
                                                    <input type="text" name="<?php echo e($key); ?>"
                                                           class="form-control <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                           value="<?php echo e(old($key, route($param, $method->code ))); ?>"
                                                           disabled>
                                                    <div class="input-group-append">
                                                        <button type="button"
                                                                class="btn btn-info copy-btn btn-sm">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback">
                                                    <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="row justify-content-between">

                            <div class="col-sm-6 col-md-3">
                                <div class="image-input ">
                                    <label for="image-upload" id="image-label"><i class="fas fa-upload"></i></label>
                                    <input type="file" name="image" placeholder="<?php echo app('translator')->get('Choose image'); ?>" id="image">
                                    <img id="image_preview_container" class="preview-image"
                                         src="<?php echo e(getFile(config('location.withdraw.path').$method->image)); ?>"
                                         alt="preview image">
                                </div>
                                <?php $__errorArgs = ['image'];
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
                        <div
                            class="row mt-3 <?php echo e($method->is_automatic == 1?'justify-content-start':'justify-content-between'); ?>">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group ">
                                    <label><?php echo app('translator')->get('Status'); ?></label>
                                    <div class="custom-switch-btn">
                                        <input type='hidden' value='1' name='status'>
                                        <input type="checkbox" name="status" class="custom-switch-checkbox" id="status"
                                               value="0" <?php echo e(($method->status == 0) ? 'checked':''); ?>>
                                        <label class="custom-switch-checkbox-label" for="status">
                                            <span class="custom-switch-checkbox-inner"></span>
                                            <span class="custom-switch-checkbox-switch"></span>
                                        </label>
                                    </div>

                                </div>
                            </div>

                            <?php if($method->is_automatic == 1): ?>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group ">
                                        <label><?php echo app('translator')->get('Test Environment'); ?></label>
                                        <div class="custom-switch-btn">
                                            <input type='hidden' value='1' name='environment'>
                                            <input type="checkbox" name="environment" class="custom-switch-checkbox"
                                                   id="environment"
                                                   value="0" <?php echo e(($method->environment == 0) ? 'checked':''); ?>>
                                            <label class="custom-switch-checkbox-label" for="environment">
                                                <span class="custom-switch-checkbox-inner"></span>
                                                <span class="custom-switch-checkbox-switch"></span>
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($method->is_automatic == 0): ?>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <a href="javascript:void(0)" class="btn btn-success float-right mt-3"
                                           id="generate"><i
                                                class="fa fa-plus-circle"></i> <?php echo app('translator')->get('Add Field'); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>

                        <?php if($method->is_automatic == 0): ?>
                            <div class="row addedField">
                                <?php if($method->input_form): ?>
                                    <?php $__currentLoopData = $method->input_form; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="input-group">

                                                    <input name="field_name[]" class="form-control"
                                                           type="text" value="<?php echo e($v->label); ?>" required
                                                           placeholder="<?php echo e(trans('Field Name')); ?>">

                                                    <select name="type[]" class="form-control">
                                                        <option value="text"
                                                                <?php if($v->type == 'text'): ?> selected <?php endif; ?>><?php echo e(trans('Input Text')); ?></option>
                                                        <option value="textarea"
                                                                <?php if($v->type == 'textarea'): ?> selected <?php endif; ?>><?php echo e(trans('Textarea')); ?></option>
                                                        <option value="file"
                                                                <?php if($v->type == 'file'): ?> selected <?php endif; ?>><?php echo e(trans('File upload')); ?></option>
                                                    </select>

                                                    <select name="validation[]" class="form-control  ">
                                                        <option value="required"
                                                                <?php if($v->validation == 'required'): ?> selected <?php endif; ?>><?php echo e(trans('Required')); ?></option>
                                                        <option value="nullable"
                                                                <?php if($v->validation == 'nullable'): ?> selected <?php endif; ?>><?php echo e(trans('Optional')); ?></option>
                                                    </select>

                                                    <span class="input-group-btn">
                                                    <button class="btn btn-danger  delete_desc" type="button">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <button type="submit"
                                class="btn  btn-primary btn-block mt-3"><?php echo app('translator')->get('Save Changes'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('js'); ?>
    <script src="<?php echo e(asset('assets/admin/js/bootstrap-select.min.js')); ?>"></script>
    <script>
        $(document).ready(function (e) {
            "use strict";

            $(function () {
                $('#selectCurrency').selectpicker();
                $('#selectSupportedCurrency').selectpicker();
            });

            $("#generate").on('click', function () {
                var form = `<div class="col-md-12">
        <div class="form-group">
            <div class="input-group">
                <input name="field_name[]" class="form-control " type="text" value="" required
                       placeholder="<?php echo app('translator')->get("Field Name"); ?>">

                <select name="type[]" class="form-control ">
                    <option value="text"><?php echo app('translator')->get("Input Text"); ?></option>
                    <option value="textarea"><?php echo app('translator')->get("Textarea"); ?></option>
                    <option value="file"><?php echo app('translator')->get("File upload"); ?></option>
                </select>

                <select name="validation[]" class="form-control  ">
                    <option value="required"><?php echo app('translator')->get('Required'); ?></option>
                    <option value="nullable"><?php echo app('translator')->get('Optional'); ?></option>
                </select>

                <span class="input-group-btn">
                    <button class="btn btn-danger  delete_desc" type="button">
                        <i class="fa fa-times"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>`;
                $('.addedField').append(form)
            });


            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.input-group').parent().remove();
            });


            $('#image').on('change',function () {
                var reader = new FileReader();
                reader.onload = (e) => {
                    $('#image_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });

        });

    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/payout/edit.blade.php ENDPATH**/ ?>