<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get($title); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- POLICY -->
    <section class="policy">
        <div class="container">
            <div class="policy wow fadeInUp " data-wow-duration="1s" data-wow-delay="0.35s">
                <?php echo app('translator')->get(@$description); ?>
            </div>
        </div>
    </section>
    <!-- /POLICY -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/getLink.blade.php ENDPATH**/ ?>