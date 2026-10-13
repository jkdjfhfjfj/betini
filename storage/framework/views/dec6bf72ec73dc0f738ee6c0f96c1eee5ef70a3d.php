<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('403'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="icon-check"></i> <?php echo app('translator')->get("You don't have permission to access that link"); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/errors/403.blade.php ENDPATH**/ ?>