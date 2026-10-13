<?php $__env->startSection('title', trans('Wallet')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $activeWalletOperation = old('wallet_operation') === 'withdrawal' ? 'withdrawal' : 'deposit';
        $activeWalletMethod = old('wallet_method', request()->query('method', 'automatic')) === 'manual'
            ? 'manual'
            : 'automatic';
    ?>
    <div class="row g-3 wallet-page">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php if(session('notice')): ?>
                <div class="alert alert-info"><?php echo e(session('notice')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
            <div class="alert alert-info" id="wallet-payment-status" <?php if(!request()->query('reference')): ?> style="display:none" <?php endif; ?>>
                <?php if(request()->query('cancelled')): ?>
                    Checkout was cancelled. No deposit was credited.
                <?php elseif(request()->query('reference')): ?>
                    Checking transaction status…
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4 wallet-balance-column">
            <div class="card secbg br-4">
                <div class="card-body">
                    <p class="mb-1"><?php echo app('translator')->get('Available balance'); ?></p>
                    <h2><?php echo e($currencySymbol); ?> <?php echo e(number_format((float)$user->balance, 2)); ?> <small><?php echo e($currency); ?></small></h2>
                    <p class="text-muted mb-0">USD is the conversion base. Existing balances remain USD until you change the wallet currency.</p>
                </div>
            </div>
        </div>

        <div class="col-12 wallet-operation-tabs-column">
            <div class="wallet-operation-switcher wallet-operation-tabs" role="tablist" aria-label="Wallet actions">
                <button type="button"
                        class="wallet-operation-choice"
                        id="wallet-deposit-tab"
                        role="tab"
                        data-wallet-operation-choice="deposit"
                        aria-controls="wallet-deposit-automatic wallet-deposit-manual"
                        aria-selected="<?php echo e($activeWalletOperation === 'deposit' ? 'true' : 'false'); ?>"
                        tabindex="<?php echo e($activeWalletOperation === 'deposit' ? '0' : '-1'); ?>"
                        aria-pressed="<?php echo e($activeWalletOperation === 'deposit' ? 'true' : 'false'); ?>">
                    <span class="wallet-operation-choice-title">Deposit</span>
                    <span class="wallet-operation-choice-description">Automatic or manual</span>
                </button>
                <button type="button"
                        class="wallet-operation-choice"
                        id="wallet-withdrawal-tab"
                        role="tab"
                        data-wallet-operation-choice="withdrawal"
                        aria-controls="wallet-withdraw-automatic wallet-withdraw-manual"
                        aria-selected="<?php echo e($activeWalletOperation === 'withdrawal' ? 'true' : 'false'); ?>"
                        tabindex="<?php echo e($activeWalletOperation === 'withdrawal' ? '0' : '-1'); ?>"
                        aria-pressed="<?php echo e($activeWalletOperation === 'withdrawal' ? 'true' : 'false'); ?>">
                    <span class="wallet-operation-choice-title">Withdraw</span>
                    <span class="wallet-operation-choice-description">Automatic or admin-reviewed</span>
                </button>
            </div>
            <p class="wallet-operation-help" id="wallet-operation-help" aria-live="polite">
                <?php if($activeWalletOperation === 'withdrawal'): ?>
                    Choose an automatic provider or submit a manual withdrawal request for review.
                <?php else: ?>
                    Choose an automatic provider or an admin-managed manual deposit option.
                <?php endif; ?>
            </p>
            <div class="wallet-method-tabs" role="tablist" aria-label="Wallet payment method">
                <button type="button"
                        class="wallet-method-choice"
                        id="wallet-automatic-tab"
                        role="tab"
                        data-wallet-method-choice="automatic"
                        aria-controls="wallet-deposit-automatic wallet-withdraw-automatic"
                        aria-selected="<?php echo e($activeWalletMethod === 'automatic' ? 'true' : 'false'); ?>"
                        aria-pressed="<?php echo e($activeWalletMethod === 'automatic' ? 'true' : 'false'); ?>"
                        tabindex="<?php echo e($activeWalletMethod === 'automatic' ? '0' : '-1'); ?>">
                    Automatic
                </button>
                <button type="button"
                        class="wallet-method-choice"
                        id="wallet-manual-tab"
                        role="tab"
                        data-wallet-method-choice="manual"
                        aria-controls="wallet-deposit-manual wallet-withdraw-manual"
                        aria-selected="<?php echo e($activeWalletMethod === 'manual' ? 'true' : 'false'); ?>"
                        aria-pressed="<?php echo e($activeWalletMethod === 'manual' ? 'true' : 'false'); ?>"
                        tabindex="<?php echo e($activeWalletMethod === 'manual' ? '0' : '-1'); ?>">
                    Manual
                </button>
            </div>
        </div>

        <div class="col-12 wallet-currency-column">
            <details class="wallet-currency-settings" open>
                <summary>Wallet currency and conversion settings</summary>
                <div class="card secbg br-4">
                    <div class="card-body">
                        <h4><?php echo app('translator')->get('Wallet currency'); ?></h4>
                        <p class="text-muted">
                            New accounts default to the currency mapped to their country.
                            Suggested for your country: <strong><?php echo e($suggestedCurrency); ?></strong>.
                            Changing currency converts your available balance. A current exchange rate and the administrator's fee are shown before you confirm.
                        </p>
                        <form id="wallet-currency-form" method="post" action="<?php echo e(route('user.wallet.currency')); ?>" class="row align-items-end">
                            <?php echo csrf_field(); ?>
                            <input id="wallet-currency-quote-token" type="hidden" name="quote_token" value="">
                            <div class="col-md-8">
                                <label for="wallet_currency"><?php echo app('translator')->get('Default wallet currency'); ?></label>
                                <select id="wallet_currency" name="wallet_currency" class="form-control" required>
                                    <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $details): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($code); ?>" <?php echo e($targetCurrency === $code ? 'selected' : ''); ?>>
                                            <?php echo e($details['name']); ?> (<?php echo e($code); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <button type="submit" class="btn-custom w-100">Review conversion</button>
                            </div>
                            <div class="col-12">
                                <div id="wallet-currency-quote-result" class="mt-3" role="status" aria-live="polite" hidden></div>
                            </div>
                        </form>
                        <?php if(!$rateAvailable): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                No current USD exchange rate is stored for <?php echo e($currency); ?>. Native-currency deposits and withdrawals still work, but betting and balance conversion require an administrator to configure CurrencyLayer and update rates.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </details>
        </div>

        <div class="col-lg-6 wallet-flow-panel wallet-deposit-panel" id="wallet-deposit-automatic"
             role="tabpanel" aria-labelledby="wallet-deposit-tab wallet-automatic-tab" tabindex="0"
             data-wallet-operation-panel="deposit" data-wallet-method-panel="automatic"
             <?php if($activeWalletOperation !== 'deposit' || $activeWalletMethod !== 'automatic'): ?> hidden <?php endif; ?>>
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4><?php echo app('translator')->get('Deposit'); ?></h4>
                    <p class="text-muted">
                        Route: <strong>
                            <?php if($depositProvider === 'payhero'): ?> PayHero M-Pesa STK
                            <?php elseif($depositProvider === 'paystack'): ?> Card (Paystack)
                            <?php elseif($depositProvider === 'payza'): ?> Payza
                            <?php else: ?> Not configured
                            <?php endif; ?>
                        </strong>
                    </p>
                    <form method="post" action="<?php echo e(route('user.wallet.deposit')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="wallet_operation" value="deposit">
                        <input type="hidden" name="wallet_method" value="automatic">
                        <div class="form-group mb-3">
                            <label for="deposit_amount"><?php echo app('translator')->get('Amount'); ?> (<?php echo e($currency); ?>)</label>
                            <input id="deposit_amount" class="form-control" type="number" name="amount" min="<?php echo e($depositProvider === 'payhero' ? '1' : '0.01'); ?>" step="<?php echo e($depositProvider === 'payhero' ? '1' : '0.01'); ?>" value="<?php echo e(old('amount')); ?>" required>
                        </div>
                        <?php if(in_array($depositProvider, ['payhero', 'payza'], true)): ?>
                            <div class="form-group mb-3">
                                <label for="deposit_phone"><?php echo e($depositProvider === 'payhero' ? 'M-Pesa phone number' : 'Phone number (optional)'); ?></label>
                                <div class="input-group">
                                    <?php if($depositProvider === 'payhero'): ?>
                                        <span class="input-group-text">+254</span>
                                    <?php endif; ?>
                                    <input id="deposit_phone" class="form-control" type="tel" name="phone_number"
                                           value="<?php echo e($depositProvider === 'payhero' ? $payheroPhoneInput : $payzaPhoneInput); ?>"
                                           placeholder="<?php echo e($depositProvider === 'payhero' ? '712345678' : '+254712345678'); ?>"
                                           <?php if($depositProvider === 'payhero'): ?> required <?php endif; ?>
                                           aria-describedby="deposit-phone-help">
                                </div>
                                <small id="deposit-phone-help" class="form-text text-muted">
                                    <?php if($depositProvider === 'payhero'): ?>
                                        Enter a Kenyan number without +254; the country code is added once.
                                    <?php else: ?>
                                        Enter the full international number, including its country calling code. Duplicate saved prefixes are normalized.
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                        <button class="btn-custom w-100" type="submit" <?php if(!$depositProvider): ?> disabled <?php endif; ?>>
                            <?php if($depositProvider === 'paystack'): ?> Deposit by Card
                            <?php elseif($depositProvider === 'payhero'): ?> Send M-Pesa prompt
                            <?php else: ?> Continue to deposit
                            <?php endif; ?>
                        </button>
                    </form>
                    <?php if(in_array($depositProvider, ['payhero', 'payza', 'paystack'], true)): ?>
                        <div class="wallet-support-actions border rounded p-3 mt-3">
                            <p class="mb-2"><strong>Deposit debited but not credited?</strong> Open a support ticket with the transaction reference, or view the support phone and email on our contact page.</p>
                            <a class="btn btn-outline-primary btn-sm mr-2" href="<?php echo e(route('user.ticket.create', ['subject' => 'Wallet deposit not credited'])); ?>">Open support ticket</a>
                            <a class="btn btn-outline-secondary btn-sm" href="<?php echo e(route('contact')); ?>">View support contacts</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 wallet-flow-panel wallet-withdraw-panel" id="wallet-withdraw-automatic"
             role="tabpanel" aria-labelledby="wallet-withdrawal-tab wallet-automatic-tab" tabindex="0"
             data-wallet-operation-panel="withdrawal" data-wallet-method-panel="automatic"
             <?php if($activeWalletOperation !== 'withdrawal' || $activeWalletMethod !== 'automatic'): ?> hidden <?php endif; ?>>
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4><?php echo app('translator')->get('Withdraw'); ?></h4>
                    <p class="text-muted">
                        Route: <strong><?php echo e($withdrawalProvider === 'makamesco' ? 'Makamesco B2C (M-Pesa)' : ($withdrawalProvider === 'payza' ? 'Payza Payouts' : 'Not configured')); ?></strong>
                    </p>
                    <form method="post" action="<?php echo e(route('user.wallet.withdraw')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="wallet_operation" value="withdrawal">
                        <input type="hidden" name="wallet_method" value="automatic">
                        <div class="form-group mb-3">
                            <label for="withdraw_amount"><?php echo app('translator')->get('Amount'); ?> (<?php echo e($currency); ?>)</label>
                            <input id="withdraw_amount" class="form-control" type="number" name="amount" min="0.01" step="0.01" value="<?php echo e(old('amount')); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="account_name"><?php echo app('translator')->get('Account name'); ?></label>
                            <input id="account_name" class="form-control" name="account_name" maxlength="120" value="<?php echo e(old('account_name', $user->fullname)); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="account_number"><?php echo e($withdrawalProvider === 'makamesco' ? 'Safaricom M-Pesa number' : 'Account number / wallet address'); ?></label>
                            <?php if($withdrawalProvider === 'makamesco'): ?>
                                <div class="input-group">
                                    <span class="input-group-text">+254</span>
                                    <input id="account_number" class="form-control" name="account_number" maxlength="32"
                                           type="tel" inputmode="tel" autocomplete="tel"
                                           value="<?php echo e(old('account_number', $phoneCode === '254' ? $nationalPhone : '')); ?>"
                                           placeholder="712345678" aria-describedby="mpesa-number-help" required>
                                </div>
                            <?php else: ?>
                                <input id="account_number" class="form-control" name="account_number" maxlength="80"
                                       value="<?php echo e(old('account_number')); ?>" required>
                            <?php endif; ?>
                            <?php if($withdrawalProvider === 'makamesco'): ?>
                                <small id="mpesa-number-help" class="form-text text-muted">Enter the local Kenyan number without +254; the country code is added once.</small>
                            <?php elseif($withdrawalProvider === 'payza'): ?>
                                <small class="form-text text-muted">For mobile-money methods, use the number's saved country code once. Bank account numbers are sent unchanged.</small>
                            <?php endif; ?>
                        </div>
                        <?php if($withdrawalProvider === 'payza'): ?>
                            <div class="form-group mb-3">
                                <label for="payout_method"><?php echo app('translator')->get('Payout method'); ?></label>
                                <select id="payout_method" class="form-control" name="method" required>
                                    <option value="">Loading available methods…</option>
                                </select>
                                <small class="text-muted" id="payout-method-note"></small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="bank_code"><?php echo app('translator')->get('Bank code (bank transfer only)'); ?></label>
                                <input id="bank_code" class="form-control" name="bank_code" maxlength="40" value="<?php echo e(old('bank_code')); ?>">
                            </div>
                            <div class="form-group mb-3">
                                <label for="bank_name"><?php echo app('translator')->get('Bank name (optional)'); ?></label>
                                <input id="bank_name" class="form-control" name="bank_name" maxlength="120" value="<?php echo e(old('bank_name')); ?>">
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="method" value="mpesa">
                        <?php endif; ?>
                        <button class="btn-custom w-100" type="submit" <?php if(!$withdrawalProvider): ?> disabled <?php endif; ?>>
                            <?php echo app('translator')->get('Request withdrawal'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if($manualDepositGateways->isNotEmpty()): ?>
            <div class="col-lg-6 wallet-flow-panel wallet-manual-deposit-panel" id="wallet-deposit-manual"
                 role="tabpanel" aria-labelledby="wallet-deposit-tab wallet-manual-tab" tabindex="0"
                 data-wallet-operation-panel="deposit" data-wallet-method-panel="manual"
                 <?php if($activeWalletOperation !== 'deposit' || $activeWalletMethod !== 'manual'): ?> hidden <?php endif; ?>>
                <div class="card secbg br-4 h-100">
                    <div class="card-body">
                        <h4><?php echo app('translator')->get('Manual deposit options'); ?> (<?php echo e($currency); ?>)</h4>
                        <p class="text-muted">Follow the instructions, submit payment proof, and an administrator will review the deposit before crediting your wallet.</p>
                        <?php $__currentLoopData = $manualDepositGateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manualGateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="manual-wallet-method border rounded p-3 mb-3">
                                <h5><?php echo e($manualGateway->name); ?></h5>
                                <p class="text-muted mb-2">
                                    <?php echo app('translator')->get('Limits'); ?>: <?php echo e($currencySymbol); ?> <?php echo e(number_format((float)$manualGateway->min_amount, 2)); ?>

                                    – <?php echo e($currencySymbol); ?> <?php echo e(number_format((float)$manualGateway->max_amount, 2)); ?> <?php echo e($currency); ?>.
                                    <?php echo app('translator')->get('Fee'); ?>: <?php echo e($currencySymbol); ?> <?php echo e(number_format((float)$manualGateway->fixed_charge, 2)); ?>

                                    + <?php echo e(number_format((float)$manualGateway->percentage_charge, 2)); ?>%.
                                </p>
                                <?php if($manualGateway->manual_instruction_html): ?>
                                    <div class="manual-wallet-instructions mb-3"><?php echo $manualGateway->manual_instruction_html; ?></div>
                                <?php endif; ?>

                                <form class="manual-wallet-deposit-form" method="post"
                                      action="<?php echo e(route('user.wallet.manual-deposit')); ?>"
                                      enctype="multipart/form-data"
                                      data-fixed-charge="<?php echo e(max(0, (float)$manualGateway->fixed_charge)); ?>"
                                      data-percentage-charge="<?php echo e(max(0, (float)$manualGateway->percentage_charge)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="wallet_operation" value="deposit">
                                     <input type="hidden" name="wallet_method" value="manual">
                                    <input type="hidden" name="gateway_id" value="<?php echo e($manualGateway->id); ?>">
                                    <div class="form-group mb-3">
                                        <label for="manual_amount_<?php echo e($manualGateway->id); ?>"><?php echo app('translator')->get('Wallet credit amount'); ?> (<?php echo e($currency); ?>)</label>
                                        <input id="manual_amount_<?php echo e($manualGateway->id); ?>" class="form-control manual-wallet-amount"
                                               type="number" name="amount" min="<?php echo e(max(0.01, (float)$manualGateway->min_amount)); ?>"
                                               <?php if((float)$manualGateway->max_amount > 0): ?> max="<?php echo e((float)$manualGateway->max_amount); ?>" <?php endif; ?>
                                               step="0.01" value="<?php echo e(old('amount')); ?>" required>
                                    </div>
                                    <p class="small text-muted">
                                        <?php echo app('translator')->get('Estimated payment total'); ?>:
                                        <strong class="manual-wallet-total"><?php echo e($currencySymbol); ?> <?php echo e(number_format((float)$manualGateway->min_amount + (float)$manualGateway->fixed_charge + ((float)$manualGateway->min_amount * (float)$manualGateway->percentage_charge / 100), 2)); ?> <?php echo e($currency); ?></strong>
                                    </p>

                                    <?php $__currentLoopData = ($manualGateway->parameters ?: []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(in_array($field->type ?? '', ['text', 'textarea', 'file'], true)): ?>
                                            <div class="form-group mb-3">
                                                <label for="manual_<?php echo e($manualGateway->id); ?>_field_<?php echo e($loop->index); ?>">
                                                    <?php echo e(trans($field->field_level ?? 'Payment details')); ?>

                                                    <?php if(($field->validation ?? '') === 'required'): ?> <span class="text-danger">*</span> <?php endif; ?>
                                                </label>
                                                <?php if($field->type === 'textarea'): ?>
                                                    <textarea id="manual_<?php echo e($manualGateway->id); ?>_field_<?php echo e($loop->index); ?>"
                                                              class="form-control"
                                                              name="payment_fields[<?php echo e($loop->index); ?>]"
                                                              rows="3"
                                                              <?php if(($field->validation ?? '') === 'required'): ?> required <?php endif; ?>></textarea>
                                                <?php elseif($field->type === 'file'): ?>
                                                    <input id="manual_<?php echo e($manualGateway->id); ?>_field_<?php echo e($loop->index); ?>"
                                                           class="form-control" type="file"
                                                           name="payment_fields[<?php echo e($loop->index); ?>]"
                                                           accept="image/jpeg,image/png"
                                                           <?php if(($field->validation ?? '') === 'required'): ?> required <?php endif; ?>>
                                                    <small class="text-muted">JPEG or PNG, up to 2 MB.</small>
                                                <?php else: ?>
                                                    <input id="manual_<?php echo e($manualGateway->id); ?>_field_<?php echo e($loop->index); ?>"
                                                           class="form-control" type="text"
                                                           name="payment_fields[<?php echo e($loop->index); ?>]"
                                                           maxlength="191"
                                                           <?php if(($field->validation ?? '') === 'required'): ?> required <?php endif; ?>>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <button class="btn-custom w-100" type="submit"><?php echo app('translator')->get('Submit deposit for review'); ?></button>
                                </form>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-lg-6 wallet-flow-panel wallet-manual-deposit-panel" id="wallet-deposit-manual"
                 role="tabpanel" aria-labelledby="wallet-deposit-tab wallet-manual-tab" tabindex="0"
                 data-wallet-operation-panel="deposit" data-wallet-method-panel="manual"
                 <?php if($activeWalletOperation !== 'deposit' || $activeWalletMethod !== 'manual'): ?> hidden <?php endif; ?>>
                <div class="card secbg br-4 h-100">
                    <div class="card-body">
                        <h4><?php echo app('translator')->get('Manual deposit options'); ?> (<?php echo e($currency); ?>)</h4>
                        <div class="alert alert-info mb-0">
                            Manual deposits are not currently enabled for <?php echo e($currency); ?>. Use the automatic option above or ask an administrator to activate a manual gateway for this currency.
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-lg-6 wallet-flow-panel wallet-manual-withdraw-panel" id="wallet-withdraw-manual"
             role="tabpanel" aria-labelledby="wallet-withdrawal-tab wallet-manual-tab" tabindex="0"
             data-wallet-operation-panel="withdrawal" data-wallet-method-panel="manual"
             <?php if($activeWalletOperation !== 'withdrawal' || $activeWalletMethod !== 'manual'): ?> hidden <?php endif; ?>>
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4><?php echo app('translator')->get('Manual withdrawal'); ?> (<?php echo e($currency); ?>)</h4>
                    <p class="text-muted">An administrator will review this payout request. The amount is reserved now; rejected requests are refunded to this wallet.</p>
                    <form method="post" action="<?php echo e(route('user.wallet.manual-withdraw')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="wallet_operation" value="withdrawal">
                        <input type="hidden" name="wallet_method" value="manual">
                        <div class="form-group mb-3">
                            <label for="manual_withdraw_amount"><?php echo app('translator')->get('Amount'); ?> (<?php echo e($currency); ?>)</label>
                            <input id="manual_withdraw_amount" class="form-control" type="number" name="amount"
                                   min="0.01" step="0.01" value="<?php echo e(old('amount')); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_payout_method"><?php echo app('translator')->get('Preferred payout method'); ?></label>
                            <input id="manual_payout_method" class="form-control" name="payout_method" maxlength="100"
                                   value="<?php echo e(old('payout_method')); ?>" placeholder="M-Pesa, bank transfer, or wallet" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_account_name"><?php echo app('translator')->get('Account name'); ?></label>
                            <input id="manual_account_name" class="form-control" name="account_name" maxlength="120"
                                   value="<?php echo e(old('account_name', $user->fullname)); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_account_number"><?php echo app('translator')->get('Payment account or mobile number'); ?></label>
                            <input id="manual_account_number" class="form-control" name="account_number" maxlength="120"
                                   value="<?php echo e(old('account_number')); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_bank_name"><?php echo app('translator')->get('Bank name (optional)'); ?></label>
                            <input id="manual_bank_name" class="form-control" name="bank_name" maxlength="120"
                                   value="<?php echo e(old('bank_name')); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_payment_notes"><?php echo app('translator')->get('Additional payout details (optional)'); ?></label>
                            <textarea id="manual_payment_notes" class="form-control" name="payment_notes" rows="3" maxlength="500"><?php echo e(old('payment_notes')); ?></textarea>
                        </div>
                        <button class="btn-custom w-100" type="submit"><?php echo app('translator')->get('Request manual withdrawal'); ?></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12" id="wallet-transactions">
            <div class="card secbg br-4">
                <div class="card-body">
                    <h4><?php echo app('translator')->get('Wallet activity'); ?></h4>
                    <div class="table-responsive wallet-activity-table">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php echo app('translator')->get('Reference'); ?></th>
                                <th><?php echo app('translator')->get('Type'); ?></th>
                                <th><?php echo app('translator')->get('Provider'); ?></th>
                                <th><?php echo app('translator')->get('Amount'); ?></th>
                                <th><?php echo app('translator')->get('Fee'); ?></th>
                                <th><?php echo app('translator')->get('Wallet credit / net'); ?></th>
                                <th><?php echo app('translator')->get('Provider reference'); ?></th>
                                <th><?php echo app('translator')->get('Status'); ?></th>
                                <th><?php echo app('translator')->get('Date'); ?></th>
                                <th><?php echo app('translator')->get('Support'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td data-label="<?php echo app('translator')->get('Reference'); ?>"><span class="wallet-reference"><?php echo e($transaction->reference); ?></span></td>
                                    <td data-label="<?php echo app('translator')->get('Type'); ?>"><?php echo e(ucfirst($transaction->direction)); ?></td>
                                    <td data-label="<?php echo app('translator')->get('Provider'); ?>"><?php echo e($transaction->provider === 'paystack' ? 'Card (Paystack)' : ($transaction->provider === 'manual' ? 'Manual (admin review)' : ($transaction->direction === 'conversion' ? 'Currency conversion' : ucfirst($transaction->provider)))); ?></td>
                                    <td data-label="<?php echo app('translator')->get('Amount'); ?>"><?php echo e(data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code)); ?> <?php echo e(number_format((float)$transaction->amount, 2)); ?> <?php echo e($transaction->currency_code); ?></td>
                                    <td data-label="<?php echo app('translator')->get('Fee'); ?>"><?php echo e(data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code)); ?> <?php echo e(number_format((float)$transaction->fee, 2)); ?> <?php echo e($transaction->currency_code); ?></td>
                                    <td data-label="<?php echo app('translator')->get('Wallet credit / net'); ?>"><?php echo e(data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code)); ?> <?php echo e(number_format((float)$transaction->net_amount, 2)); ?> <?php echo e($transaction->currency_code); ?></td>
                                    <td data-label="<?php echo app('translator')->get('Provider reference'); ?>"><?php echo e($transaction->provider_reference ?: '—'); ?></td>
                                    <td data-label="<?php echo app('translator')->get('Status'); ?>">
                                        <?php if($transaction->status === 'succeeded'): ?>
                                            <span class="badge bg-success">Successful</span>
                                        <?php elseif($transaction->status === 'failed'): ?>
                                            <span class="badge bg-danger">Failed</span>
                                            <?php if($transaction->failure_reason): ?><small class="d-block"><?php echo e($transaction->failure_reason); ?></small><?php endif; ?>
                                        <?php elseif($transaction->status === 'processing'): ?>
                                            <span class="badge bg-info">Processing</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><?php echo e(ucfirst($transaction->status)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="<?php echo app('translator')->get('Date'); ?>">
                                        <?php echo e(optional($transaction->created_at)->format('d M Y H:i')); ?>

                                        <?php if($transaction->updated_at && $transaction->updated_at != $transaction->created_at): ?>
                                            <small class="d-block text-muted"><?php echo app('translator')->get('Updated'); ?> <?php echo e(optional($transaction->updated_at)->format('d M Y H:i')); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="<?php echo app('translator')->get('Support'); ?>">
                                        <?php if($transaction->direction === 'deposit' && $transaction->provider !== 'manual'): ?>
                                            <a class="btn btn-outline-primary btn-sm"
                                               href="<?php echo e(route('user.ticket.create', [
                                                    'subject' => 'Wallet deposit not credited — ' . $transaction->reference,
                                                    'message' => 'Please investigate this wallet deposit.' . "\n\n"
                                                        . 'Provider: ' . $transaction->provider . "\n"
                                                        . 'Amount: ' . number_format((float)$transaction->amount, 2) . ' ' . $transaction->currency_code . "\n"
                                                        . 'App reference: ' . $transaction->reference . "\n"
                                                        . 'Provider reference: ' . ($transaction->provider_reference ?: 'not yet available') . "\n"
                                                        . 'Current status: ' . $transaction->status,
                                                ])); ?>">Contact support</a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="10" class="text-center"><?php echo app('translator')->get('No wallet activity yet.'); ?></td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3"><?php echo e($transactions->links()); ?></div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script>
        (function () {
            const form = document.getElementById('wallet-currency-form');
            const currencySelect = document.getElementById('wallet_currency');
            const result = document.getElementById('wallet-currency-quote-result');
            const tokenInput = document.getElementById('wallet-currency-quote-token');
            if (!form || !currencySelect || !result || !tokenInput) return;

            let submittingConfirmedQuote = false;
            const setMessage = function (message, style) {
                result.hidden = false;
                result.className = 'alert mt-3 ' + style;
                result.textContent = message;
            };

            currencySelect.addEventListener('change', function () {
                tokenInput.value = '';
                result.hidden = true;
                result.replaceChildren();
            });

            form.addEventListener('submit', async function (event) {
                if (submittingConfirmedQuote) return;
                event.preventDefault();
                tokenInput.value = '';
                result.hidden = false;
                result.className = 'alert alert-info mt-3';
                result.textContent = 'Getting the latest conversion quote…';

                try {
                    const params = new URLSearchParams({wallet_currency: currencySelect.value});
                    const response = await fetch(<?php echo json_encode(route('user.wallet.currency-quote'), 15, 512) ?> + '?' + params.toString(), {
                        headers: {'Accept': 'application/json'}
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        const errors = data.errors ? Object.values(data.errors).flat().join(' ') : 'Could not create a conversion quote.';
                        setMessage(errors, 'alert-danger');
                        return;
                    }

                    const quote = data.quote;
                    if (!quote.token) {
                        setMessage('Your wallet already uses ' + quote.to_currency + '. No conversion is needed.', 'alert-secondary');
                        return;
                    }

                    result.replaceChildren();
                    result.className = 'alert alert-info mt-3 wallet-conversion-quote';
                    result.hidden = false;
                    const title = document.createElement('strong');
                    title.textContent = 'Confirm wallet conversion';
                    result.appendChild(title);
                    const list = document.createElement('ul');
                    list.className = 'mb-3 mt-2';
                    const lines = [
                        'Current balance: ' + data.from_symbol + ' ' + Number(quote.current_balance).toFixed(2) + ' ' + quote.from_currency,
                        'Converted amount before fee: ' + data.to_symbol + ' ' + Number(quote.gross_amount).toFixed(2) + ' ' + quote.to_currency,
                        'Conversion fee (' + Number(quote.fee_percent).toFixed(2) + '%): ' + data.to_symbol + ' ' + Number(quote.fee_amount).toFixed(2) + ' ' + quote.to_currency,
                        'New available balance: ' + data.to_symbol + ' ' + Number(quote.net_amount).toFixed(2) + ' ' + quote.to_currency
                    ];
                    lines.forEach(function (line) {
                        const item = document.createElement('li');
                        item.textContent = line;
                        list.appendChild(item);
                    });
                    result.appendChild(list);

                    const confirm = document.createElement('button');
                    confirm.type = 'button';
                    confirm.className = 'btn btn-success btn-sm mr-2';
                    confirm.textContent = 'Confirm conversion';
                    confirm.addEventListener('click', function () {
                        tokenInput.value = quote.token;
                        submittingConfirmedQuote = true;
                        form.requestSubmit();
                    });
                    result.appendChild(confirm);

                    const cancel = document.createElement('button');
                    cancel.type = 'button';
                    cancel.className = 'btn btn-outline-secondary btn-sm';
                    cancel.textContent = 'Cancel';
                    cancel.addEventListener('click', function () {
                        tokenInput.value = '';
                        result.hidden = true;
                        result.replaceChildren();
                    });
                    result.appendChild(cancel);
                } catch (error) {
                    setMessage('Could not reach the rate service. Try again later or contact support.', 'alert-danger');
                }
            });
        })();

        const walletOperationButtons = document.querySelectorAll('[data-wallet-operation-choice]');
        const walletMethodButtons = document.querySelectorAll('[data-wallet-method-choice]');
        const walletOperationPanels = document.querySelectorAll('[data-wallet-operation-panel]');
        const walletOperationHelp = document.getElementById('wallet-operation-help');
        const walletOperationDescriptions = {
            deposit: 'Choose an automatic provider or an admin-managed manual deposit option.',
            withdrawal: 'Choose an automatic provider or submit a manual withdrawal request for review.'
        };
        let activeWalletOperation = <?php echo json_encode($activeWalletOperation, 15, 512) ?>;
        let activeWalletMethod = <?php echo json_encode($activeWalletMethod, 15, 512) ?>;
        const setWalletMethod = function (method) {
            activeWalletMethod = method === 'manual' ? 'manual' : 'automatic';
            walletMethodButtons.forEach(function (button) {
                const selected = button.dataset.walletMethodChoice === activeWalletMethod;
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                button.setAttribute('aria-selected', selected ? 'true' : 'false');
                button.tabIndex = selected ? 0 : -1;
            });
            walletOperationPanels.forEach(function (panel) {
                const visible = panel.dataset.walletOperationPanel === activeWalletOperation
                    && panel.dataset.walletMethodPanel === activeWalletMethod;
                panel.hidden = !visible;
                panel.setAttribute('aria-hidden', visible ? 'false' : 'true');
            });
        };
        const setWalletOperation = function (operation) {
            activeWalletOperation = operation === 'withdrawal' ? 'withdrawal' : 'deposit';
            walletOperationButtons.forEach(function (button) {
                const selected = button.dataset.walletOperationChoice === activeWalletOperation;
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                button.setAttribute('aria-selected', selected ? 'true' : 'false');
                button.tabIndex = selected ? 0 : -1;
            });
            if (walletOperationHelp && walletOperationDescriptions[activeWalletOperation]) {
                walletOperationHelp.textContent = walletOperationDescriptions[activeWalletOperation];
            }
            setWalletMethod(activeWalletMethod);
        };
        walletOperationButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setWalletOperation(button.dataset.walletOperationChoice);
            });
            button.addEventListener('keydown', function (event) {
                const currentIndex = Array.prototype.indexOf.call(walletOperationButtons, button);
                let nextIndex = currentIndex;
                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                    nextIndex = (currentIndex + 1) % walletOperationButtons.length;
                } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    nextIndex = (currentIndex - 1 + walletOperationButtons.length) % walletOperationButtons.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = walletOperationButtons.length - 1;
                } else {
                    return;
                }
                event.preventDefault();
                const nextButton = walletOperationButtons[nextIndex];
                nextButton.focus();
                setWalletOperation(nextButton.dataset.walletOperationChoice);
            });
        });
        walletMethodButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setWalletMethod(button.dataset.walletMethodChoice);
            });
            button.addEventListener('keydown', function (event) {
                const currentIndex = Array.prototype.indexOf.call(walletMethodButtons, button);
                let nextIndex = currentIndex;
                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                    nextIndex = (currentIndex + 1) % walletMethodButtons.length;
                } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    nextIndex = (currentIndex - 1 + walletMethodButtons.length) % walletMethodButtons.length;
                } else if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = walletMethodButtons.length - 1;
                } else {
                    return;
                }
                event.preventDefault();
                const nextButton = walletMethodButtons[nextIndex];
                nextButton.focus();
                setWalletMethod(nextButton.dataset.walletMethodChoice);
            });
        });
        setWalletOperation(activeWalletOperation);

        document.querySelectorAll('.manual-wallet-deposit-form').forEach(function (form) {
            const amountInput = form.querySelector('.manual-wallet-amount');
            const totalLabel = form.querySelector('.manual-wallet-total');
            const fixedCharge = parseFloat(form.dataset.fixedCharge || '0') || 0;
            const percentageCharge = parseFloat(form.dataset.percentageCharge || '0') || 0;
            const currencySymbol = <?php echo json_encode($currencySymbol, 15, 512) ?>;
            const currency = <?php echo json_encode($currency, 15, 512) ?>;
            const updateEstimatedTotal = function () {
                const amount = parseFloat(amountInput.value || '0') || 0;
                totalLabel.textContent = currencySymbol + ' ' + (amount + fixedCharge + amount * percentageCharge / 100).toFixed(2) + ' ' + currency;
            };
            amountInput.addEventListener('input', updateEstimatedTotal);
            updateEstimatedTotal();
        });
    </script>
    <?php if($withdrawalProvider === 'payza'): ?>
        <script>
            fetch(<?php echo json_encode(route('user.wallet.payout-methods'), 15, 512) ?>)
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('payout_method');
                    const note = document.getElementById('payout-method-note');
                    select.innerHTML = '';
                    if (!data.methods || data.methods.length === 0) {
                        select.innerHTML = '<option value="">No methods available</option>';
                        note.textContent = data.message || 'Payza did not return payout methods for this currency.';
                        return;
                    }
                    data.methods.forEach(method => {
                        const option = document.createElement('option');
                        option.value = method.code;
                        option.textContent = method.name + (method.fee !== null && method.fee !== undefined ? ' — fee ' + method.fee : '');
                        select.appendChild(option);
                    });
                })
                .catch(() => {
                    document.getElementById('payout_method').innerHTML = '<option value="">Could not load methods</option>';
                    document.getElementById('payout-method-note').textContent = 'Check the Payza API settings and try again.';
                });
        </script>
    <?php endif; ?>
    <?php if(request()->query('reference')): ?>
        <script>
            (function () {
                const statusUrl = <?php echo json_encode(route('user.wallet.status', ['reference' => request()->query('reference')]), 512) ?>;
                const statusBox = document.getElementById('wallet-payment-status');
                let attempts = 0;
                statusBox.style.display = '';

                const refreshStatus = () => {
                    fetch(statusUrl, {headers: {'Accept': 'application/json'}})
                        .then(response => response.ok ? response.json() : Promise.reject())
                        .then(data => {
                            statusBox.textContent = data.message;
                            statusBox.className = 'alert ' + (data.status === 'succeeded' ? 'alert-success' : (data.status === 'failed' ? 'alert-danger' : 'alert-info'));
                            if (data.status === 'succeeded' || data.status === 'failed') {
                                window.setTimeout(() => window.location.reload(), 1500);
                            } else if (++attempts < 12) {
                                window.setTimeout(refreshStatus, 5000);
                            }
                        })
                        .catch(() => {
                            if (++attempts < 12) window.setTimeout(refreshStatus, 5000);
                        });
                };

                refreshStatus();
            })();
        </script>
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/currency-wallet.blade.php ENDPATH**/ ?>