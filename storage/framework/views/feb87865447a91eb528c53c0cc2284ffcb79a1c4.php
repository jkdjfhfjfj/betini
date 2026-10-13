<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('SMS Template'); ?>
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
                    <?php if($smstemplate->short_keys): ?>
                        <?php $__currentLoopData = $smstemplate->short_keys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                <?php $__currentLoopData = $smsTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                <?php $__currentLoopData = $smsTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="tab-pane <?php echo e(($key == 0) ? 'show active' : ''); ?>" id="tab-<?php echo e($value->id); ?>">
                        <h3 class="card-title my-3"><?php echo e(trans('SMS in')); ?> <?php echo e(optional($value->language)->name); ?> : <?php echo e($value->name); ?></h3>

                        <form action="<?php echo e(route('admin.sms-template.update',$value->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label class="font-weight-bold"><?php echo app('translator')->get('Status'); ?></label>
                                    <div class="custom-switch-btn">
                                        <input type='hidden' value='1' name='sms_status'>
                                        <input type="checkbox" name="sms_status" class="custom-switch-checkbox"
                                               id="status-<?php echo e($value->id); ?>"
                                               value="0" <?php if ($value->sms_status == 0):echo 'checked'; endif ?> >
                                        <label class="custom-switch-checkbox-label" for="status-<?php echo e($value->id); ?>">
                                            <span class="custom-switch-checkbox-inner"></span>
                                            <span class="custom-switch-checkbox-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold"><?php echo app('translator')->get('Message Body'); ?></label>
                                <textarea class="form-control" name="sms_body" rows="10"><?php echo e((old('sms_body', $value->sms_body))); ?></textarea>
                                <?php if($errors->has('sms_body')): ?>
                                    <div class="error text-danger"><?php echo app('translator')->get($errors->first('sms_body')); ?> </div>
                                <?php endif; ?>
                            </div>


                            <button type="submit"
                                    class="btn waves-effect waves-light btn-rounded btn-primary btn-block mt-3">
                                <span><?php echo app('translator')->get('Save Change'); ?></span></button>

                        </form>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>


        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/sms-template/edit.blade.php ENDPATH**/ ?>