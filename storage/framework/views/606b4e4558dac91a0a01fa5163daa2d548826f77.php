<?php $attributes = $attributes->exceptProps(['align' => 'right', 'width' => '48']); ?>
<?php foreach (array_filter((['align' => 'right', 'width' => '48']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $alignmentClasses = [
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        'right' => 'ltr:origin-top-right rtl:origin-top-left end-0',
    ];
    $widthClasses = [
        '48' => 'w-48',
    ];
    $alignment = $alignmentClasses[$align] ?? $alignmentClasses['right'];
    $dropdownWidth = $widthClasses[$width] ?? $widthClasses['48'];
?>

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        <?php echo e($trigger); ?>

    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 <?php echo e($dropdownWidth); ?> rounded-md shadow-lg <?php echo e($alignment); ?>"
        @click="open = false"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white">
            <?php echo e($content); ?>

        </div>
    </div>
</div>
<?php /**PATH /home/runner/workspace/resources/views/components/dropdown.blade.php ENDPATH**/ ?>