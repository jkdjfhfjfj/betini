<?php $__env->startSection('title',trans(snake2Title($content))); ?>


<?php $__env->startSection('content'); ?>


    <!-- Container Fluid-->
    <div class="container-fluid" id="container-wrapper">


        <div class="row justify-content-md-center">
            <div class="col-lg-12">
                <!-- Currency Create Form  -->
                <div class="card mb-4 shadow">
                    <div class="card-body">

                        <div class="media align-items-center justify-content-between mb-3">
                            <h4 class="card-title "><?php echo app('translator')->get(snake2Title($content)); ?></h4>
                            <a href="<?php echo e(route('admin.content.index',$content)); ?>" class="btn btn-sm btn-primary"> <i class="fas fa-arrow-left"></i> <?php echo app('translator')->get('Back'); ?></a>
                        </div>

                        <?php if(array_key_exists('language',config("contents.$content")) && config("contents.$content.language") == 0): ?>
                            <form method="post" action="<?php echo e(route('admin.content.store', [$content,0])); ?>" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('put'); ?>
                                <div class="row">
                                    <?php $__currentLoopData = config("contents.$content.field_name"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($type == 'file'): ?>

                                            <div class="col-md-4 source-parent">
                                                <div class="form-group">
                                                    <label for="<?php echo e(@$name); ?>">  <?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?> </label>
                                                    <div class="image-input ">
                                                        <label for="content-image-<?php echo e($name); ?>-0"><i class="fas fa-upload"></i></label>
                                                        <input type="file" placeholder="Choose image" class="image-preview" id="content-image-<?php echo e($name); ?>-0" name="<?php echo e($name); ?>[0]">
                                                        <img id="content-preview-<?php echo e($name); ?>-0" class="preview-image" src="<?php echo e(getFile(config('location.content.path').(isset($templateMedia->description->{$name}) ? $templateMedia->description->{$name} : ''))); ?>"
                                                             alt="preview image">
                                                    </div>
                                                    <?php $__errorArgs = [$name.'.0'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"> <?php echo app('translator')->get($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>



                                        <?php elseif($type == 'url'): ?>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="<?php echo e($name); ?>"> <?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?> </label>
                                                    <input type="<?php echo e($type); ?>" name="<?php echo e($name); ?>[<?php echo e(0); ?>]"
                                                           class="form-control  <?php $__errorArgs = [$name.'.0'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                           value="<?php echo e(old($name.'.0', isset($templateMedia->description->{$name}) ? $templateMedia->description->{$name} : '')); ?>">
                                                    <div class="invalid-feedback">
                                                        <?php $__errorArgs = [$name.'.0'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    </div>
                                                    <div class="valid-feedback"></div>
                                                </div>
                                            </div>
                                        <?php elseif($type == 'icon'): ?>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="<?php echo e($name); ?>"> <?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?> </label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="<?php echo e($name); ?>[<?php echo e(0); ?>]"
                                                               class="form-control icon <?php $__errorArgs = [$name.'.0'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                               value="<?php echo e(old($name.'.0', isset($templates[0]) ? $templates[0][0]->description->{$name} : '')); ?>">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-primary iconPicker" data-icon="fas fa-home" role="iconpicker"></button>
                                                        </div>
                                                        <div class="invalid-feedback"><?php $__errorArgs = [$name.'.0'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm btn-block"><?php echo app('translator')->get('Save Change'); ?></button>
                            </form>

                        <?php else: ?>
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo e($loop->first ? 'active' : ''); ?>" data-toggle="tab" href="#lang-tab-<?php echo e($key); ?>" role="tab" aria-controls="lang-tab-<?php echo e($key); ?>"
                                           aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>"><?php echo app('translator')->get($language->name); ?></a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>


                            <div class="tab-content mt-2" id="myTabContent">
                                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <div class="tab-pane fade <?php echo e($loop->first ? 'show active' : ''); ?>" id="lang-tab-<?php echo e($key); ?>" role="tabpanel">
                                        <form method="post" action="<?php echo e(route('admin.content.store', [$content,$language->id])); ?>" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('put'); ?>
                                            <div class="row">
                                                <?php $__currentLoopData = config("contents.$content.field_name"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($type == 'text' ): ?>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="<?php echo e($name); ?>"> <?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?> </label>
                                                                <input type="<?php echo e($type); ?>" name="<?php echo e($name); ?>[<?php echo e($language->id); ?>]"
                                                                       class="form-control  <?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                       value="<?php echo e(old($name.'.'.$language->id, isset($templates[$language->id]) ? $templates[$language->id][0]->description->{$name} : '')); ?>">
                                                                <div class="invalid-feedback">
                                                                    <?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                                </div>
                                                                <div class="valid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    <?php elseif($type == 'file' && $key == 0): ?>


                                                        <div class="col-md-4 source-parent">
                                                            <div class="form-group">
                                                                <label for="<?php echo e(@$name); ?>"> <?php echo app('translator')->get(ucwords(str_replace('_',' ',@$name))); ?> </label>
                                                                <div class="image-input ">
                                                                    <label for="content-image-<?php echo e($name); ?>-<?php echo e($language->id); ?>"><i class="fas fa-upload"></i></label>
                                                                    <input type="file" placeholder="Choose image" class="image-preview" id="content-image-<?php echo e($name); ?>-<?php echo e($language->id); ?>" name="<?php echo e($name); ?>[<?php echo e($language->id); ?>]">
                                                                    <img id="content-preview-<?php echo e($name); ?>-<?php echo e($language->id); ?>" class="preview-image" src="<?php echo e(getFile(config('location.default'))); ?>"
                                                                         alt="preview image">
                                                                </div>


                                                                <?php if(config("contents.$content.size.image")): ?>
                                                                    <span class="text-muted mb-2"><?php echo e(trans('Image size should be')); ?> <?php echo e(config("contents.$content.size.image")); ?> <?php echo e(trans('px')); ?></span>
                                                                <?php endif; ?>

                                                                <?php $__errorArgs = [$name.'.'.$language->id];
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


                                                    <?php elseif($type == 'url' && $key == 0): ?>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="<?php echo e($name); ?>"> <?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?> </label>
                                                                <input type="<?php echo e($type); ?>" name="<?php echo e($name); ?>[<?php echo e($language->id); ?>]"
                                                                       class="form-control  <?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                       value="<?php echo e(old($name.'.'.$language->id, isset($templateMedia->description->{$name}) ? $templateMedia->description->{$name} : '')); ?>">
                                                                <div class="invalid-feedback">
                                                                    <?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                                </div>
                                                                <div class="valid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    <?php elseif($type == 'textarea'): ?>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="<?php echo e($name); ?>"><?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?></label>
                                                                <textarea class="form-control  summernote <?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                          name="<?php echo e($name); ?>[<?php echo e($language->id); ?>]"
                                                                          rows="5"><?php echo e(old($name.'.'.$language->id, isset($templates[$language->id]) ? $templates[$language->id][0]->description->{$name} : '')); ?></textarea>
                                                                <div class="invalid-feedback">
                                                                    <?php $__errorArgs = [$name.'.'.$language->id];
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
                                                    <?php elseif($type == 'icon' && $key == 0): ?>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="<?php echo e($name); ?>"> <?php echo app('translator')->get(ucwords(str_replace('_',' ',$name))); ?> </label>
                                                                <div class="input-group">
                                                                    <input type="text" name="<?php echo e($name); ?>[<?php echo e($language->id); ?>]"
                                                                           class="form-control icon <?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                           value="<?php echo e(old($name.'.'.$language->id, isset($templates[$language->id]) ? $templates[$language->id][0]->description->{$name} : '')); ?>">
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-outline-primary iconPicker" data-icon="fas fa-home" role="iconpicker"></button>
                                                                    </div>
                                                                    <div class="invalid-feedback"><?php $__errorArgs = [$name.'.'.$language->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo app('translator')->get($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-primary btn-block mt-3"><?php echo app('translator')->get('Save Change'); ?></button>
                                        </form>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                         <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---Container Fluid-->




<?php $__env->stopSection(); ?>

<?php $__env->startPush('style-lib'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/summernote.min.css')); ?>">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"/>
    <link href="<?php echo e(asset('assets/admin/css/bootstrap-iconpicker.min.css')); ?>" rel="stylesheet" type="text/css">
<?php $__env->stopPush(); ?>


<?php $__env->startPush('js-lib'); ?>
    <script src="<?php echo e(asset('assets/admin/js/summernote.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/bootstrap-iconpicker.bundle.min.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        'use strict'
        $(document).ready(function () {


            $('.image-preview').on('change', function(){
                if (!this.files || !this.files[0]) return;
                let reader = new FileReader();
                const preview = $(this).closest('.source-parent').find('.preview-image').first();
                reader.onload = (e) => {
                    preview.attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });

            $('.summernote').summernote({
                minHeight: 200,
                callbacks: {
                    onBlurCodeview: function() {
                        let codeviewHtml = $(this).siblings('div.note-editor').find('.note-codable').val();
                        $(this).val(codeviewHtml);
                    }
                }
            });

            $('.iconPicker').iconpicker({
                align: 'center', // Only in div tag
                arrowClass: 'btn-danger',
                arrowPrevIconClass: 'fas fa-angle-left',
                arrowNextIconClass: 'fas fa-angle-right',
                cols: 10,
                footer: true,
                header: true,
                icon: 'fas fa-bomb',
                iconset: 'fontawesome5',
                labelHeader: '{0} of {1} pages',
                labelFooter: '{0} - {1} of {2} icons',
                placement: 'bottom', // Only in button tag
                rows: 5,
                search: true,
                searchText: 'Search icon',
                selectedClass: 'btn-success',
                unselectedClass: ''
            }).on('change', function (e) {
                $(this).parent().siblings('.icon').val(`${e.icon}`);
            });
        });


    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/content/create.blade.php ENDPATH**/ ?>