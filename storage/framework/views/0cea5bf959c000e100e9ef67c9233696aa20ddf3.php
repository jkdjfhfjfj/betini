<div <?php echo e($attributes->merge(['class' => 'min-h-screen flex flex-col items-center pt-6 sm:pt-0'])); ?>>
    <?php if(isset($logo)): ?>
        <div class="mb-6">
            <?php echo e($logo); ?>

        </div>
    <?php endif; ?>

    <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH /home/runner/workspace/resources/views/components/auth-card.blade.php ENDPATH**/ ?>