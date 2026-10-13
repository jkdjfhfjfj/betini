<?php $__env->startSection('title', trans($title)); ?>
<?php $__env->startSection('content'); ?>
    <!-- main -->
    <div class="row">
        <div class="card col-md-3 ms-3">
            <div class="payment-info text-center">
                <ul class="list-group">
                    <li class="list-group-item font-weight-bold bg-transparent">
                        <img
                            src="<?php echo e(getFile(config('location.withdraw.path').optional($withdraw->method)->image)); ?>"
                            class="card-img-top w-50" alt="<?php echo e(optional($withdraw->method)->name); ?>">
                    </li>
                    <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Request Amount'); ?>
                        :
                        <span
                            class="float-right text-success"><?php echo e(@$basic->currency_symbol); ?><?php echo e(getAmount($withdraw->amount)); ?> </span>
                    </li>
                    <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Charge Amount'); ?>
                        :
                        <span
                            class="float-right text-danger"><?php echo e(@$basic->currency_symbol); ?><?php echo e(getAmount($withdraw->charge)); ?> </span>
                    </li>
                    <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Total Payable'); ?>
                        :
                        <span
                            class="float-right text-danger"><?php echo e(@$basic->currency_symbol); ?><?php echo e(getAmount($withdraw->net_amount)); ?> </span>
                    </li>
                    <?php if($layout != 'layouts.payment'): ?>
                        <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Available Balance'); ?>
                            :
                            <span
                                class="float-right text-success"><?php echo e(@$basic->currency_symbol); ?><?php echo e($remaining); ?> </span>
                        </li>
                    <?php endif; ?>
                    <div id="showInfo">

                    </div>
                </ul>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header custom-header text-center">
                    <h5 class="card-title"><?php echo app('translator')->get('Additional Information To Withdraw Confirm'); ?></h5>
                </div>

                <div class="card-body">
                    <form <?php if($layout == 'layouts.payment'): ?> action="<?php echo e(route('user.payout.submit',$billId)); ?>"
                          <?php else: ?> action="<?php echo e(route('user.payout.submit.flutterwave',$withdraw->trx_id)); ?>"
                          <?php endif; ?> method="post" enctype="multipart/form-data"
                          class="form-row text-left preview-form">
                        <?php echo csrf_field(); ?>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group input-box search-currency-dropdown">
                                    <label for="from_wallet"><?php echo app('translator')->get('Select Transfer'); ?></label>
                                    <select id="from_wallet" name="transfer_name"
                                            class="form-control form-control-sm bank">
                                        <option value="" disabled=""
                                                selected=""><?php echo app('translator')->get('Select Transfer'); ?></option>
                                        <?php $__currentLoopData = $payoutMethod->banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option
                                                value="<?php echo e($bank); ?>" <?php echo e(old('transfer_name') == $bank ?'selected':''); ?>><?php echo e($bank); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['transfer_name'];
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
                        </div>

                        <?php if($payoutMethod->supported_currency): ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group input-box search-currency-dropdown">
                                        <label for="from_wallet"><?php echo app('translator')->get('Select Bank Currency'); ?></label>
                                        <select id="from_wallet" name="currency_code"
                                                class="form-control form-control-sm transfer-currency">
                                            <option value="" disabled=""
                                                    selected=""><?php echo app('translator')->get('Select Currency'); ?></option>
                                            <?php $__currentLoopData = $payoutMethod->supported_currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $singleCurrency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option
                                                    value="<?php echo e($singleCurrency); ?>"
                                                    <?php $__currentLoopData = $payoutMethod->convert_rate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($singleCurrency == $key): ?> data-rate="<?php echo e($rate); ?>" <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php echo e(old('currency_code') == $singleCurrency ?'selected':''); ?>><?php echo e($singleCurrency); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['currency_code'];
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
                            </div>
                        <?php endif; ?>

                        <div class="row input-box dynamic-bank mx-1 d-none">
                            <label><?php echo app('translator')->get('Select Bank'); ?></label>
                            <select id="dynamic-bank" name="bank"
                                    class="form-control form-control-sm">
                            </select>
                            <?php $__errorArgs = ['bank'];
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
                        <div class="row dynamic-input mt-4">

                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn-custom mt-3">
                                    <span><?php echo app('translator')->get('Confirm Now'); ?></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('css-lib'); ?>

<?php $__env->stopPush(); ?>
<?php $__env->startPush('extra-js'); ?>

<?php $__env->stopPush(); ?>
<?php $__env->startPush('script'); ?>
    <script type="text/javascript">
        var bankName = null;
        var payAmount = '<?php echo e($withdraw->amount); ?>'
        var baseCurrency = "<?php echo e(config('basic.currency')); ?>"
        var transferName = "<?php echo e(old('transfer_name')); ?>";
        if (transferName) {
            getBankForm(transferName);
        }

        $(document).on("change", ".transfer-currency", function () {
            let currencyCode = $(this).val();
            let rate = $(this).find(':selected').data('rate');
            let getAmount = parseFloat(rate) * parseFloat(payAmount);
            var output = null;
            $('#showInfo').html('');
            output = `<li class="list-group-item font-weight-bold bg-transparent"><?php echo app('translator')->get('Exchange rate'); ?> :
                        <span
                            class="float-right text-danger">1 ${baseCurrency} = ${rate} ${currencyCode}</span>
                    </li>
                    <li class="list-group-item font-weight-bold bg-transparent"><?php echo app('translator')->get('You will get:'); ?> :
                        <span
                            class="float-right text-danger">${getAmount} ${currencyCode}</span>
                    </li>`

            $('#showInfo').html(output);
        })

        $(document).on("change", ".bank", function () {
            bankName = $(this).val();
            $('.dynamic-bank').addClass('d-none');
            getBankForm(bankName);
        })

        function getBankForm(bankName) {
            $.ajax({
                url: "<?php echo e(route('user.payout.getBankFrom')); ?>",
                type: "post",
                data: {
                    bankName,
                },
                success: function (response) {
                    console.log(response)
                    if (response.bank != null) {
                        showBank(response.bank.data)
                    }
                    showInputForm(response.input_form)
                }
            });
        }

        function showBank(bankLists) {
            $('#dynamic-bank').html(``);
            var options = `<option disabled selected><?php echo app('translator')->get("Select Bank"); ?></option>`;
            for (let i = 0; i < bankLists.length; i++) {
                options += `<option value="${bankLists[i].code}">${bankLists[i].name}</option>`;
            }

            $('.dynamic-bank').removeClass('d-none');
            $('#dynamic-bank').html(options);
        }

        function showInputForm(form_fields) {
            $('.dynamic-input').html(``);
            var output = "";

            for (let field in form_fields) {
                let newKey = field.replace('_', ' ');
                output += `<div class="input-box col-md-6 mt-3">
                         <label>${newKey}</label>
				         <input type="text" name="${field}" value="" class="form-control" required>
			          </div>`
            }
            $('.dynamic-input').html(output);
        }

    </script>

    <?php if($errors->any()): ?>
        <?php
            $collection = collect($errors->all());
            $errors = $collection->unique();
        ?>
        <script>
            "use strict";
            <?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            Notiflix.Notify.Failure("<?php echo e(trans($error)); ?>");
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </script>
    <?php endif; ?>
<?php $__env->stopPush(); ?>


<?php echo $__env->make($theme.$layout, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payout/gateway/flutterwave.blade.php ENDPATH**/ ?>