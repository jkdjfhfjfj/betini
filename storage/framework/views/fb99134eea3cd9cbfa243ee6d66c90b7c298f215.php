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
                            class="text-success"><?php echo e(@$basic->currency_symbol); ?><?php echo e(getAmount($withdraw->amount)); ?> </span>
                    </li>
                    <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Charge Amount'); ?>
                        :
                        <span
                            class=" text-danger"><?php echo e(@$basic->currency_symbol); ?><?php echo e(getAmount($withdraw->charge)); ?> </span>
                    </li>
                    <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Total Payable'); ?>
                        :
                        <span
                            class=" text-danger"><?php echo e(@$basic->currency_symbol); ?><?php echo e(getAmount($withdraw->net_amount)); ?> </span>
                    </li>
                    <?php if($layout != 'layouts.payment'): ?>
                        <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Available Balance'); ?>
                            :
                            <span
                                class="text-success"><?php echo e(@$basic->currency_symbol); ?><?php echo e($remaining); ?> </span>
                        </li>
                    <?php endif; ?>
                    <div id="showInfo">

                    </div>
                </ul>
            </div>
        </div>

        <div class="col-md-8">

            <div class="card card-type-1 bgGateway">
                <div class="text-center">
                    <h3 class="card-title"><?php echo app('translator')->get('Additional Information To Withdraw Confirm'); ?></h3>
                </div>


                <div class="card-body">
                    <form <?php if($layout == 'layouts.payment'): ?> action="<?php echo e(route('user.payout.submit',$billId)); ?>"
                          <?php else: ?> action="<?php echo e(route('user.payout.submit.paystack',$withdraw->trx_id)); ?>"
                          <?php endif; ?> method="post" enctype="multipart/form-data"
                          class="form-row text-left preview-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" class="type" name="type" value="">
                        <input type="hidden" class="currency" name="currency" value="">

                        <?php if($payoutMethod->supported_currency): ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group input-box search-currency-dropdown">
                                        <label for="from_wallet"><?php echo app('translator')->get('Select Bank Currency'); ?></label>
                                        <select id="from_wallet" name="currency_code"
                                                class="form-control form-control-sm transfer-currency" required>
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

                        <div class="row g-4 dynamic-bank input-box mx-1  d-none mb-4">
                            <label><?php echo app('translator')->get('Select Bank'); ?></label>
                            <select id="dynamic-bank" name="bank"
                                    class="form-control form-control-sm" required>
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

                        <?php if(optional($withdraw->method)->input_form): ?>
                            <div class="row g-4">
                                <?php $__currentLoopData = $withdraw->method->input_form; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($v->type == "text"): ?>
                                        <div class="input-box col-md-12">
                                            <label><?php echo e(trans($v->label)); ?> <?php if($v->validation == 'required'): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?></label>
                                            <input type="text" name="<?php echo e($k); ?>"
                                                   class="form-control"
                                                   <?php if($v->validation == "required"): ?> required <?php endif; ?>>
                                            <?php if($errors->has($k)): ?>
                                                <span
                                                    class="text-danger"><?php echo e(trans($errors->first($k))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif($v->type == "textarea"): ?>
                                        <div class="input-box col-12">
                                            <div class="form-group">
                                                <label><?php echo e(trans($v->label)); ?> <?php if($v->validation == 'required'): ?>
                                                        <span class="text-danger">*</span>
                                                    <?php endif; ?>
                                                </label>
                                                <textarea name="<?php echo e($k); ?>" class="form-control"
                                                          cols="30"
                                                          rows="10"
                                                          <?php if($v->validation == "required"): ?> required <?php endif; ?>></textarea>
                                                <?php if($errors->has($k)): ?>
                                                    <span
                                                        class="text-danger"><?php echo e(trans($errors->first($k))); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php elseif($v->type == "file"): ?>
                                        <div class="input-box col-12">
                                            <label
                                                for=""><?php echo e(trans($v->label)); ?> <?php if($v->validation == 'required'): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?></label>
                                            <input class="form-control" name="<?php echo e($k); ?>" accept="image/*"
                                                   <?php if($v->validation == "required"): ?> required <?php endif; ?> type="file"
                                                   id="formFile"/>
                                            <span class="icon"> <i class="fal fa-paperclip"></i></span>
                                            <?php if($errors->has($k)): ?>
                                                <br>
                                                <span
                                                    class="text-danger"><?php echo e(__($errors->first($k))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn-custom mt-3">
                                <span><?php echo app('translator')->get('Confirm Now'); ?></span>
                            </button>
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
        var currency_code = "<?php echo e(old('currency_code')); ?>";
        if (currency_code) {
            getBankForm(currency_code);
        }

        $(document).on("change", "#dynamic-bank", function () {
            let type = $(this).find(':selected').data('type');
            let currency = $(this).find(':selected').data('currency');

            $('.type').val(type);
            $('.currency').val(currency);
        })

        $(document).on("change", ".transfer-currency", function () {
            let currencyCode = $(this).val();
            let rate = $(this).find(':selected').data('rate');
            let getAmount = parseFloat(rate) * parseFloat(payAmount);
            var output = null;
            $('#showInfo').html('');
            output = `<p><?php echo app('translator')->get('Exchange rate:'); ?> <span
                         class="text-danger">1 ${baseCurrency} = ${rate} ${currencyCode}</span></p>
                      <p><?php echo app('translator')->get('You will get:'); ?> <span
                         class="text-danger">${getAmount} ${currencyCode}</span></p>`

            $('#showInfo').html(output);
        })


        $(document).on("change", ".transfer-currency", function () {
            currencyCode = $(this).val();
            $('.dynamic-bank').addClass('d-none');
            getBankForm(currencyCode);
        })

        function getBankForm(currencyCode) {
            $.ajax({
                url: "<?php echo e(route('user.payout.getBankList')); ?>",
                type: "post",
                data: {
                    currencyCode,
                },
                success: function (response) {
                    if (response.data != null) {
                        showBank(response.data)
                    }
                }
            });
        }

        function showBank(bankLists) {
            $('#dynamic-bank').html(``);
            var options = `<option disabled selected><?php echo app('translator')->get("Select Bank"); ?></option>`;
            for (let i = 0; i < bankLists.length; i++) {
                options += `<option value="${bankLists[i].code}" data-type="${bankLists[i].type}" data-currency="${bankLists[i].currency}">${bankLists[i].name}</option>`;
            }

            $('.dynamic-bank').removeClass('d-none');
            $('#dynamic-bank').html(options);
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


<?php echo $__env->make($theme.$layout, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payout/gateway/paystack.blade.php ENDPATH**/ ?>