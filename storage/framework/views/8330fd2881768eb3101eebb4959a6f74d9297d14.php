<?php $__env->startSection('title', __($title)); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if($configurationWarning): ?>
                <div class="alert alert-warning"><?php echo e($configurationWarning); ?></div>
            <?php endif; ?>
            <div class="alert alert-info">
                Existing user balances stay USD until a user switches their wallet currency. USD is the conversion base.
                Save provider credentials here; they are encrypted in the database and are never written to source files or `.env`.
            </div>
        </div>

        <div class="col-12">
            <div class="card card-primary m-0 m-md-4 my-4 shadow">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold"><?php echo app('translator')->get('Automatic provider routing'); ?></h5>
                    <p class="text-muted">KES deposits are fixed to PayHero M-Pesa STK, and KES withdrawals are fixed to Makamesco B2C.</p>
                    <?php if(adminAccessRoute(config('role.payment_gateway.access.edit'))): ?>
                    <form method="post" action="<?php echo e(route('admin.wallet-providers.save')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th><?php echo app('translator')->get('Currency'); ?></th>
                                    <th><?php echo app('translator')->get('Deposit provider'); ?></th>
                                    <th><?php echo app('translator')->get('Withdrawal provider'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $currencyInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($currencyInfo['name']); ?> (<?php echo e($code); ?>)</td>
                                        <td>
                                            <?php if($code === 'KES'): ?>
                                                <strong>PayHero M-Pesa STK (fixed)</strong>
                                                <input type="hidden" name="routes[<?php echo e($code); ?>][deposit]" value="payhero">
                                            <?php else: ?>
                                                <select class="form-control" name="routes[<?php echo e($code); ?>][deposit]" required>
                                                    <?php $__currentLoopData = $routeOptions[$code]['deposit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($option); ?>" <?php echo e(($routes[$code]['deposit'] ?? '') === $option ? 'selected' : ''); ?>>
                                                            <?php if($option === 'payhero'): ?> PayHero M-Pesa STK
                                                            <?php elseif($option === 'payza'): ?> Payza
                                                            <?php else: ?> Card (Paystack)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($code === 'KES'): ?>
                                                <strong>Makamesco B2C (M-Pesa, fixed)</strong>
                                                <input type="hidden" name="routes[<?php echo e($code); ?>][withdrawal]" value="makamesco">
                                            <?php else: ?>
                                                <select class="form-control" name="routes[<?php echo e($code); ?>][withdrawal]" required>
                                                    <?php $__currentLoopData = $routeOptions[$code]['withdrawal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($option); ?>" <?php echo e(($routes[$code]['withdrawal'] ?? '') === $option ? 'selected' : ''); ?>>
                                                            <?php echo e($option === 'makamesco' ? 'Makamesco B2C (M-Pesa)' : 'Payza Payouts'); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="card-title font-weight-bold mt-4"><?php echo app('translator')->get('Provider credentials'); ?></h5>
                        <p class="text-muted">Leave a credential field empty to keep its saved value. Saved values are not shown again.</p>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>PayHero M-Pesa</h6>
                                    <p class="text-muted">Required for KES STK deposits. Credentials are from your PayHero account; see <a href="https://docs.payhero.co.ke/docs" target="_blank" rel="noopener">PayHero API docs</a>. Register the linked collection account below to create and save its channel ID.</p>
                                    <p class="text-muted">PayHero callback: <code><?php echo e(route('wallet.payhero.webhook', [], true)); ?></code> (sent automatically with each STK request).</p>
                                    <label>PayHero username</label>
                                    <input class="form-control mb-2" name="payhero[username]" autocomplete="off">
                                    <label>PayHero password</label>
                                    <input class="form-control mb-2" type="password" name="payhero[password]" autocomplete="new-password">
                                    <div>Username: <strong><?php echo e(!empty($settings['payhero']['username']) ? 'Saved' : 'Not set'); ?></strong> · Password: <strong><?php echo e(!empty($settings['payhero']['password']) ? 'Saved' : 'Not set'); ?></strong></div>
                                    <div>Channel ID: <strong><?php echo e(!empty($settings['payhero']['channel_id']) ? 'Configured' : 'Not set'); ?></strong></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>Payza</h6>
                                    <p class="text-muted">Used for non-KES deposits and non-KES payouts. In the Payza dashboard, open API Keys and create a separate webhook signing secret. See <a href="https://payzaapi.co.ke/docs" target="_blank" rel="noopener">Payza API docs</a>.</p>
                                    <p class="text-muted">Webhook URL: <code><?php echo e(route('wallet.payza.webhook', [], true)); ?></code>. Live payout accounts may also require the server's public IP to be allowlisted in Payza.</p>
                                    <label>Public API key</label>
                                    <input class="form-control mb-2" name="payza[public_key]" autocomplete="off">
                                    <label>Secret API key</label>
                                    <input class="form-control mb-2" type="password" name="payza[secret_key]" autocomplete="new-password">
                                    <label>Webhook signing secret</label>
                                    <input class="form-control mb-2" type="password" name="payza[webhook_secret]" autocomplete="new-password">
                                    <div>API keys: <strong><?php echo e(!empty($settings['payza']['public_key']) && !empty($settings['payza']['secret_key']) ? 'Saved' : 'Not fully configured'); ?></strong> · Webhook secret: <strong><?php echo e(!empty($settings['payza']['webhook_secret']) ? 'Saved' : 'Not set'); ?></strong></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>Paystack — USD Card</h6>
                                    <p class="text-muted">Use the secret key from Paystack Dashboard → Settings → API Keys &amp; Webhooks, on an account enabled to accept USD card payments. Set the Paystack webhook URL to <code><?php echo e(route('wallet.paystack.webhook', [], true)); ?></code>.</p>
                                    <label>Paystack secret key</label>
                                    <input class="form-control mb-2" type="password" name="paystack[secret_key]" autocomplete="new-password">
                                    <div>Secret key: <strong><?php echo e(!empty($settings['paystack']['secret_key']) ? 'Saved' : 'Not set'); ?></strong></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>Makamesco B2C — KES withdrawals</h6>
                                    <p class="text-muted">Create an API key in the Makamesco dashboard and fund its B2C wallet; the published fee is charged to that wallet. See <a href="https://makamescopay.com/docs" target="_blank" rel="noopener">Makamesco API docs</a>.</p>
                                    <label>Makamesco API key</label>
                                    <input class="form-control mb-2" type="password" name="makamesco[api_key]" autocomplete="new-password">
                                    <div>API key: <strong><?php echo e(!empty($settings['makamesco']['api_key']) ? 'Saved' : 'Not set'); ?></strong></div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit"><?php echo app('translator')->get('Save provider settings'); ?></button>
                    </form>
                    <?php else: ?>
                        <p class="text-warning">You can view routes, but do not have permission to change provider settings.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-primary m-0 m-md-4 my-4 shadow">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold"><?php echo app('translator')->get('Register a PayHero payment channel'); ?></h5>
                    <p class="text-muted">Enter the PayHero account and M-Pesa collection details linked to your business. The API response channel ID is saved automatically.</p>
                    <?php if(adminAccessRoute(config('role.payment_gateway.access.add'))): ?>
                    <form method="post" action="<?php echo e(route('admin.wallet-providers.register-channel')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Channel type</label>
                                <select class="form-control" name="channel_type" required>
                                    <option value="paybill">PayBill</option>
                                    <option value="till">Till</option>
                                    <option value="bank">Bank</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>PayHero account ID</label>
                                <input class="form-control" name="account_id" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>PayBill / Till / bank short code</label>
                                <input class="form-control" name="short_code" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Account number (if required)</label>
                                <input class="form-control" name="account_number">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Description</label>
                                <input class="form-control" name="description" placeholder="Business or bank account name">
                            </div>
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Register channel with PayHero</button>
                    </form>
                    <?php else: ?>
                        <p class="text-warning">You do not have permission to register payment channels.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="alert alert-secondary">
                Currency conversion uses CurrencyLayer. Get an API key at
                <a href="https://currencylayer.com/product" target="_blank" rel="noopener">currencylayer.com/product</a>,
                then add it under <a href="<?php echo e(route('admin.currency.exchange.api.config')); ?>">Admin → Exchange API</a>.
                The wallet rate updater uses HTTPS; the selected CurrencyLayer plan must support HTTPS.
                The server's public <code>APP_URL</code> must also use HTTPS so payment callbacks can be registered securely.
                The server must run <code>php artisan schedule:run</code> every minute to refresh rates and check pending payouts.
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/wallet-providers.blade.php ENDPATH**/ ?>