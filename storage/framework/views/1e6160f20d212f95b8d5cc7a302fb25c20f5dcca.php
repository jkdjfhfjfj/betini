<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('Default Template'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead class="thead-primary">
                    <tr>
                        <th> <?php echo app('translator')->get('SHORTCODE'); ?> </th>
                        <th> <?php echo app('translator')->get('DESCRIPTION'); ?> </th>
                    </tr>
                    </thead>
                    <tbody>


                    <?php if($emailTemplate->short_keys): ?>
                        <?php $__currentLoopData = $emailTemplate->short_keys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <pre>[[<?php echo app('translator')->get($key); ?>]]</pre>
                                </td>
                                <td> <?php echo app('translator')->get($value); ?> </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
        <div class="card-body">


            <ul class="nav nav-tabs mb-3">
                <?php $__currentLoopData = $mailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a href="#tab-<?php echo e($value->id); ?>" data-toggle="tab" aria-expanded="<?php echo e(($key == 0) ? 'true':'false'); ?>"
                           class="nav-link <?php echo e(($key == 0) ? 'active':''); ?>">
                            <i class="mdi mdi-home-variant d-lg-none d-block mr-1"></i>
                            <span class="d-none d-lg-block"><?php echo e(optional($value->language)->name); ?></span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            <div class="tab-content">
                <?php $__currentLoopData = $mailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="tab-pane <?php echo e(($key == 0) ? 'show active' : ''); ?>" id="tab-<?php echo e($value->id); ?>">
                        <h3 class="card-title my-3"><?php echo e(trans('Mail in')); ?>  <?php echo e(optional($value->language)->name); ?>

                            : <?php echo e($value->name); ?></h3>


                        <form action="<?php echo e(route('admin.email-template.update',$value->id)); ?>" method="POST"
                              class="mt-4">
                            <?php echo csrf_field(); ?>

                            <div class="row justify-content-between">
                                <div class="col-md-4 form-group">
                                    <label><?php echo app('translator')->get('Subject'); ?></label>
                                    <input class="form-control" type="text" name="subject"
                                           value="<?php echo e($value->subject); ?>">
                                    <?php if($errors->has('subject')): ?>
                                        <div class="error text-danger"><?php echo app('translator')->get($errors->first('subject')); ?> </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label><?php echo app('translator')->get('Mail from'); ?></label>
                                    <input class="form-control" type="email" name="email_from"
                                           value="<?php echo e($value->email_from); ?>">
                                    <?php if($errors->has('email_from')): ?>
                                        <div class="error text-danger"><?php echo app('translator')->get($errors->first('email_from')); ?> </div>
                                    <?php endif; ?>
                                </div>


                                <div class="col-md-3 form-group">
                                    <label><?php echo app('translator')->get('Status'); ?></label>
                                    <div class="custom-switch-btn">
                                        <input type='hidden' value='1' name='mail_status'>
                                        <input type="checkbox" name="mail_status" class="custom-switch-checkbox"
                                               id="status-<?php echo e($value->id); ?>"
                                               value="0" <?php if ($value->mail_status == 0):echo 'checked'; endif ?> >
                                        <label class="custom-switch-checkbox-label" for="status-<?php echo e($value->id); ?>">
                                            <span class="custom-switch-checkbox-inner"></span>
                                            <span class="custom-switch-checkbox-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label><?php echo app('translator')->get('Email Body'); ?></label>
                                    <textarea class="form-control summernote" name="template" id="summernote"
                                              rows="20"><?php echo e($value->template); ?></textarea>
                                    <?php if($errors->has('template')): ?>
                                        <div class="error text-danger"><?php echo app('translator')->get($errors->first('template')); ?> </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn waves-effect waves-light btn-rounded btn-primary btn-block mt-3">
                                <span><?php echo app('translator')->get('Update'); ?></span></button>

                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('style-lib'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/summernote.min.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startPush('js'); ?>
    <script src="<?php echo e(asset('assets/admin/js/summernote.min.js')); ?>"></script>
    <script>
        "use strict";
        $(document).ready(function () {
           $('.summernote').summernote({
                minHeight: 200,
                callbacks: {
                    onBlurCodeview: function() {
                        let codeviewHtml = $(this).siblings('div.note-editor').find('.note-codable').val();
                        $(this).val(codeviewHtml);
                    }
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/email-template/edit.blade.php ENDPATH**/ ?>