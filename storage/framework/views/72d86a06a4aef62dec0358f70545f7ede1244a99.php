<form method="GET" action="<?php echo e(route('user.wallet')); ?>"
      class="wallet-currency-switch d-inline-flex align-items-center mr-2">
    <label class="sr-only" for="header-wallet-currency"><?php echo app('translator')->get('Wallet currency'); ?></label>
    <select id="header-wallet-currency"
            name="currency"
            class="form-control form-control-sm"
            aria-label="<?php echo app('translator')->get('Choose a currency to review'); ?>"
            title="<?php echo app('translator')->get('Choose a currency to review'); ?>"
            style="width: auto; min-width: 76px;"
            onchange="this.form.submit()">
        <?php $__currentLoopData = config('currencies.supported', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($code); ?>"
                <?php echo e(strtoupper(auth()->user()->wallet_currency ?: 'USD') === $code ? 'selected' : ''); ?>>
                <?php echo e($code); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</form>
<?php /**PATH /home/runner/workspace/resources/views/themes/betting/partials/wallet-currency-switch.blade.php ENDPATH**/ ?>