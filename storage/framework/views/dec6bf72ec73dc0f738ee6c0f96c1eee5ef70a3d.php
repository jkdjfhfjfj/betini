<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('403'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="icon-check"></i> <?php echo app('translator')->get("You're signed in, but this account doesn't have permission to open this page."); ?></h4>
                        <p class="mb-3">
                            <?php echo app('translator')->get('For wallet transactions, grant Payment Request & Log view access. To approve or reject manual wallet requests, also grant edit access.'); ?>
                        </p>
                        <?php if(adminAccessRoute(config('role.manage_staff.access.view'))): ?>
                            <a class="btn btn-primary" href="<?php echo e(route('admin.staff')); ?>">
                                <?php echo app('translator')->get('Manage admin permissions'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/errors/403.blade.php ENDPATH**/ ?>