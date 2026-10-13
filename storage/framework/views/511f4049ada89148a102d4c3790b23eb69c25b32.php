<?php $__env->startSection('title',trans('Referrals').': '.$user->username ); ?>
<?php $__env->startSection('content'); ?>



<div id="feature">

    <div class="card card-primary m-0 m-md-4 my-4 m-md-0">
        <div class="card-body">
            <?php if(0 < count($referrals)): ?>
                <div class="d-flex align-items-start">
                    <div class="nav nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="nav-link <?php if($key == '1'): ?>  active  <?php endif; ?> " id="v-pills-<?php echo e($key); ?>-tab" data-toggle="pill" href="#v-pills-<?php echo e($key); ?>"  role="tab" aria-controls="v-pills-<?php echo e($key); ?>" aria-selected="true"><?php echo app('translator')->get('Level'); ?> <?php echo e($key); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="tab-content" id="v-pills-tabContent">

                        <?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="tab-pane fade <?php if($key == '1'): ?> show active  <?php endif; ?> " id="v-pills-<?php echo e($key); ?>" role="tabpanel" aria-labelledby="v-pills-<?php echo e($key); ?>-tab">
                                <?php if( 0 < count($referral)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th scope="col"><?php echo app('translator')->get('Username'); ?></th>
                                                <th scope="col"><?php echo app('translator')->get('Email'); ?></th>
                                                <th scope="col"><?php echo app('translator')->get('Phone Number'); ?></th>
                                                <th scope="col"><?php echo app('translator')->get('Joined At'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $__currentLoopData = $referral; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>

                                                    <td data-label="<?php echo app('translator')->get('Username'); ?>">
                                                        <a href="<?php echo e(route('admin.user-edit',$user->id)); ?>" target="_blank">
                                                            <?php echo app('translator')->get($user->username); ?>
                                                        </a>
                                                    </td>
                                                    <td data-label="<?php echo app('translator')->get('Email'); ?>"><?php echo e($user->email); ?></td>
                                                    <td data-label="<?php echo app('translator')->get('Phone Number'); ?>">
                                                        <?php echo e($user->mobile); ?>

                                                    </td>
                                                    <td data-label="<?php echo app('translator')->get('Joined At'); ?>">
                                                        <?php echo e(dateTime($user->created_at)); ?>

                                                    </td>

                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>


</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/users/referral.blade.php ENDPATH**/ ?>