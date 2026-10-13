<?php $__env->startSection('title'); ?>
    <?php echo e(trans($page_title)); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e(trans($error)); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-primary shadow">
                    <div class="card-body">
                        <form method="post" action="<?php echo e(route('admin.deposit.manual.update',$method)); ?>"
                              class="needs-validation base-form" novalidate="" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('put'); ?>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label><?php echo e(trans('Name')); ?></label>
                                    <input type="text" class="form-control "
                                           name="name"
                                           value="<?php echo e(old('name', $method->name)); ?>" required="">
                                    <?php if($errors->has('name')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('name'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group col-md-4">
                                    <label><?php echo e(trans('Currency')); ?></label>
                                    <input type="text" class="form-control "
                                           name="currency"
                                           value="<?php echo e(old('currency',$method->currency)); ?>" required="required">

                                    <?php if($errors->has('currency')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('currency'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group col-md-4">
                                    <label><?php echo e(trans('Convention Rate')); ?></label>
                                    <div class="input-group ">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                1 <?php echo e($basic->currency ? : 'USD'); ?> =
                                            </div>
                                        </div>
                                        <input type="text" class="form-control "
                                               name="convention_rate"
                                               value="<?php echo e(old('convention_rate',getAmount($method->convention_rate))); ?>"
                                               required>
                                        <div class="input-group-append">
                                            <div class="input-group-text set-currency">

                                            </div>
                                        </div>
                                    </div>

                                    <?php if($errors->has('convention_rate')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('currency_symbol'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 col-6">
                                    <label><?php echo e(trans('Minimum Deposit Amount')); ?></label>
                                    <div class="input-group ">
                                        <input type="text" class="form-control "
                                               name="minimum_deposit_amount"
                                               value="<?php echo e(old('minimum_deposit_amount',getAmount($method->min_amount))); ?>"
                                               required="">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <?php echo e($basic->currency ?? trans('USD')); ?>

                                            </div>
                                        </div>
                                    </div>

                                    <?php if($errors->has('minimum_deposit_amount')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('minimum_deposit_amount'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group col-md-6 col-6">
                                    <label><?php echo e(trans('Maximum Deposit Amount')); ?></label>
                                    <div class="input-group ">
                                        <input type="text" class="form-control "
                                               name="maximum_deposit_amount"
                                               value="<?php echo e(old('maximum_deposit_amount', getAmount($method->max_amount))); ?>"
                                               required="">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <?php echo e($basic->currency ?? trans('USD')); ?>

                                            </div>
                                        </div>
                                    </div>

                                    <?php if($errors->has('maximum_deposit_amount')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('maximum_deposit_amount'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 col-6">
                                    <label><?php echo e(trans('Percentage Charge')); ?></label>
                                    <div class="input-group ">
                                        <input type="text" class="form-control "
                                               name="percentage_charge"
                                               value="<?php echo e(old('percentage_charge',getAmount($method->percentage_charge))); ?>"
                                               required="">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <?php echo e(trans('%')); ?>

                                            </div>
                                        </div>
                                    </div>

                                    <?php if($errors->has('percentage_charge')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('percentage_charge'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group col-md-6 col-6">
                                    <label><?php echo app('translator')->get('Fixed Charge'); ?></label>
                                    <div class="input-group ">
                                        <input type="text" class="form-control "
                                               name="fixed_charge"
                                               value="<?php echo e(old('fixed_charge',getAmount($method->fixed_charge))); ?>"
                                               required="">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <?php echo e($basic->currency ?? trans('USD')); ?>

                                            </div>
                                        </div>
                                    </div>

                                    <?php if($errors->has('fixed_charge')): ?>
                                        <span class="invalid-text">
                                                <?php echo e(trans($errors->first('fixed_charge'))); ?>

                                            </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row justify-content-between">
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo app('translator')->get('Gateway Logo'); ?></label>

                                        <div class="image-input ">
                                            <label for="image-upload" id="image-label"><i class="fas fa-upload"></i></label>
                                            <input type="file" name="image" placeholder="<?php echo app('translator')->get('Choose image'); ?>" id="image">
                                            <img id="image_preview_container" class="preview-image" src="<?php echo e(getFile(config('location.gateway.path').$method->image)); ?>"
                                                 alt="preview image">
                                        </div>
                                    </div>
                                    <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-sm-12 col-md-9">
                                    <div class="form-group ">
                                        <label><?php echo app('translator')->get('Note'); ?></label>
                                        <textarea class="form-control summernote" name="note" id="summernote" rows="15"><?php echo e(old('note',$method->note)); ?></textarea>
                                        <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 justify-content-between">

                                <div class="form-group col-lg-3 col-md-6">
                                    <label><?php echo app('translator')->get('Status'); ?></label>

                                    <div class="custom-switch-btn">
                                        <input type='hidden' value='1' name='status'>
                                        <input type="checkbox" name="status" class="custom-switch-checkbox" id="status" value = "0"
                                            <?php echo e(($method->status == 0)? 'checked' : ''); ?>>
                                        <label class="custom-switch-checkbox-label" for="status">
                                            <span class="custom-switch-checkbox-inner"></span>
                                            <span class="custom-switch-checkbox-switch"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <a href="javascript:void(0)" class="btn btn-success float-right mt-3" id="generate"><i
                                                class="fa fa-plus-circle"></i> <?php echo e(trans('Add Field')); ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="row addedField">
                                <?php if($method->parameters): ?>
                                    <?php $__currentLoopData = $method->parameters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="input-group">

                                                    <input name="field_name[]" class="form-control"
                                                           type="text" value="<?php echo e($v->field_level); ?>" required
                                                           placeholder="<?php echo e(trans('Field Name')); ?>">

                                                    <select name="type[]" class="form-control  ">
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

                            <button type="submit" class="btn btn-rounded btn-primary btn-block mt-3"><?php echo app('translator')->get('Save Changes'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('style-lib'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/summernote.min.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startPush('js-lib'); ?>
    <script src="<?php echo e(asset('assets/admin/js/summernote.min.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        "use strict";

        $(document).ready(function () {
            setCurrency();
            $(document).on('change', 'input[name="currency"]', function (){
                setCurrency();
            });

            function setCurrency() {
                let currency = $('input[name="currency"]').val();
                $('.set-currency').text(currency);
            }

            $(document).on('click', '.copy-btn', function () {
                var _this = $(this)[0];
                var copyText = $(this).parents('.input-group-append').siblings('input');
                $(copyText).prop('disabled', false);
                copyText.select();
                document.execCommand("copy");
                $(copyText).prop('disabled', true);
                $(this).text('Coppied');
                setTimeout(function () {
                    $(_this).text('');
                    $(_this).html('<i class="fas fa-copy"></i>');
                }, 500)
            });
        })



        $(document).ready(function (e) {

            $("#generate").on('click', function () {
                var form = `<div class="col-md-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input name="field_name[]" class="form-control " type="text" value="" required placeholder="<?php echo e(trans('Field Name')); ?>">

                                        <select name="type[]"  class="form-control  ">
                                            <option value="text"><?php echo e(trans('Input Text')); ?></option>
                                            <option value="textarea"><?php echo e(trans('Textarea')); ?></option>
                                            <option value="file"><?php echo e(trans('File upload')); ?></option>
                                        </select>

                                        <select name="validation[]"  class="form-control  ">
                                            <option value="required"><?php echo e(trans('Required')); ?></option>
                                            <option value="nullable"><?php echo e(trans('Optional')); ?></option>
                                        </select>

                                        <span class="input-group-btn">
                                            <button class="btn btn-danger delete_desc" type="button">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div> `;

                $('.addedField').append(form)
            });


            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.input-group').parent().remove();
            });


            $('#image').change(function () {
                let reader = new FileReader();
                reader.onload = (e) => {
                    $('#image_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });

            $('.summernote').summernote({
                height: 250,
                callbacks: {
                    onBlurCodeview: function() {
                        let codeviewHtml = $(this).siblings('div.note-editor').find('.note-codable').val();
                        $(this).val(codeviewHtml);
                    }
                }
            });
        });



    </script>
    <script>
        $(document).ready(function (e) {
            "use strict";

            $('#image').change(function(){
                let reader = new FileReader();
                reader.onload = (e) => {
                    $('#image_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });


            $('select').select2({
                selectOnClose: true
            });

        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/payment_methods/manual/edit.blade.php ENDPATH**/ ?>