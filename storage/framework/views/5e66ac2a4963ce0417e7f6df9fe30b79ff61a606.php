<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get($page_title); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header card card-primary m-0 m-md-4 my-4 m-md-0 p-4 p-md-5 shadow">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1"><?php echo app('translator')->get('Wallet Transactions'); ?></h3>
                <p class="text-muted mb-0">Track automatic and manual deposits, withdrawals, and conversions. Only manual requests can be reviewed here; provider transactions are reconciled automatically.</p>
            </div>
            <span class="badge badge-warning"><?php echo e($transactions->total()); ?> <?php echo app('translator')->get('transactions'); ?></span>
        </div>

        <form method="get" action="<?php echo e(route('admin.wallet-manual.index')); ?>" class="mb-4">
            <div class="form-row align-items-end">
                <div class="col-md-3 mb-2">
                    <label for="wallet_provider"><?php echo app('translator')->get('Provider'); ?></label>
                    <select class="form-control" id="wallet_provider" name="provider">
                        <option value="all" <?php if($provider === 'all'): ?> selected <?php endif; ?>><?php echo app('translator')->get('All providers'); ?></option>
                        <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $providerCode => $providerName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($providerCode); ?>" <?php if($provider === $providerCode): ?> selected <?php endif; ?>><?php echo e($providerName); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="manual_direction"><?php echo app('translator')->get('Type'); ?></label>
                    <select class="form-control" id="manual_direction" name="direction">
                        <option value="all" <?php if($direction === 'all'): ?> selected <?php endif; ?>><?php echo app('translator')->get('All types'); ?></option>
                        <option value="deposit" <?php if($direction === 'deposit'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Deposits'); ?></option>
                        <option value="withdrawal" <?php if($direction === 'withdrawal'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Withdrawals'); ?></option>
                        <option value="conversion" <?php if($direction === 'conversion'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Conversions'); ?></option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="manual_status"><?php echo app('translator')->get('Status'); ?></label>
                    <select class="form-control" id="manual_status" name="status">
                        <option value="pending" <?php if($status === 'pending'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Pending'); ?></option>
                        <option value="processing" <?php if($status === 'processing'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Processing'); ?></option>
                        <option value="succeeded" <?php if($status === 'succeeded'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Succeeded'); ?></option>
                        <option value="failed" <?php if($status === 'failed'): ?> selected <?php endif; ?>><?php echo app('translator')->get('Failed'); ?></option>
                        <option value="all" <?php if($status === 'all'): ?> selected <?php endif; ?>><?php echo app('translator')->get('All statuses'); ?></option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="wallet_search"><?php echo app('translator')->get('Reference or user'); ?></label>
                    <input id="wallet_search" class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="Reference, email, or username">
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary" type="submit"><?php echo app('translator')->get('Filter'); ?></button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th><?php echo app('translator')->get('Reference / User'); ?></th>
                    <th><?php echo app('translator')->get('Provider / Type'); ?></th>
                    <th><?php echo app('translator')->get('Amount'); ?></th>
                    <th><?php echo app('translator')->get('Details'); ?></th>
                    <th><?php echo app('translator')->get('Status'); ?></th>
                    <th><?php echo app('translator')->get('Updated'); ?></th>
                    <th><?php echo app('translator')->get('Admin action'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php ($metadata = (array)($transaction->metadata ?: [])); ?>
                    <tr>
                        <td>
                            <strong><?php echo e($transaction->reference); ?></strong><br>
                            <span><?php echo e(optional($transaction->user)->fullname ?: optional($transaction->user)->username ?: 'User #' . $transaction->user_id); ?></span><br>
                            <small class="text-muted"><?php echo e(optional($transaction->user)->email); ?></small>
                        </td>
                        <td>
                            <strong><?php echo e($providers[$transaction->provider] ?? ucfirst($transaction->provider)); ?></strong><br>
                            <small class="text-muted text-capitalize"><?php echo e($transaction->direction); ?></small>
                        </td>
                        <td>
                            <?php echo e($transaction->currency_code); ?> <?php echo e(number_format((float)$transaction->amount, 2)); ?>

                            <?php if((float)$transaction->fee > 0): ?>
                                <br><small class="text-muted">Fee: <?php echo e($transaction->currency_code); ?> <?php echo e(number_format((float)$transaction->fee, 2)); ?></small>
                            <?php endif; ?>
                            <?php if($transaction->direction === 'conversion'): ?>
                                <br><small class="text-muted">Net: <?php echo e($transaction->currency_code); ?> <?php echo e(number_format((float)$transaction->net_amount, 2)); ?></small>
                            <?php elseif($transaction->direction === 'deposit' && $transaction->provider === 'manual'): ?>
                                <br><small class="text-muted">Expected payment: <?php echo e($transaction->currency_code); ?> <?php echo e(number_format((float)($metadata['expected_payment'] ?? $transaction->amount), 2)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <details>
                                <summary><?php echo app('translator')->get('View details'); ?></summary>
                                <?php if($transaction->direction === 'conversion'): ?>
                                    <dl class="mt-2 mb-0">
                                        <dt><?php echo app('translator')->get('Converted from'); ?></dt>
                                        <dd><?php echo e($metadata['from_currency'] ?? 'USD'); ?> <?php echo e(number_format((float)($metadata['from_amount'] ?? 0), 2)); ?></dd>
                                        <dt><?php echo app('translator')->get('Conversion fee'); ?></dt>
                                        <dd><?php echo e(number_format((float)($metadata['fee_percent'] ?? 0), 2)); ?>%</dd>
                                    </dl>
                                <?php elseif($transaction->provider === 'manual' && $transaction->direction === 'deposit'): ?>
                                    <div class="mt-2">
                                        <strong><?php echo e($metadata['gateway_name'] ?? 'Manual gateway'); ?></strong>
                                        <?php $__currentLoopData = ($metadata['submitted_fields'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="mb-2">
                                                <strong><?php echo e($field['label'] ?? 'Payment detail'); ?>:</strong>
                                                <?php if(($field['type'] ?? '') === 'file' && !empty($field['value'])): ?>
                                                    <a href="<?php echo e(getFile(config('location.deposit.path') . ltrim($field['value'], '/'))); ?>" target="_blank" rel="noopener noreferrer"><?php echo app('translator')->get('View uploaded proof'); ?></a>
                                                <?php else: ?>
                                                    <span><?php echo e($field['value'] ?? ''); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php elseif($transaction->provider === 'manual' && $transaction->direction === 'withdrawal'): ?>
                                    <dl class="mt-2 mb-0">
                                        <dt><?php echo app('translator')->get('Account name'); ?></dt>
                                        <dd><?php echo e($metadata['account_name'] ?? ''); ?></dd>
                                        <dt><?php echo app('translator')->get('Account or mobile number'); ?></dt>
                                        <dd><?php echo e($metadata['account_number'] ?? ''); ?></dd>
                                        <?php if(!empty($metadata['bank_name'])): ?>
                                            <dt><?php echo app('translator')->get('Bank'); ?></dt>
                                            <dd><?php echo e($metadata['bank_name']); ?></dd>
                                        <?php endif; ?>
                                        <?php if(!empty($metadata['payment_notes'])): ?>
                                            <dt><?php echo app('translator')->get('Additional details'); ?></dt>
                                            <dd><?php echo e($metadata['payment_notes']); ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                <?php else: ?>
                                    <dl class="mt-2 mb-0">
                                        <dt><?php echo app('translator')->get('Provider reference'); ?></dt>
                                        <dd><?php echo e($transaction->provider_reference ?: '—'); ?></dd>
                                        <?php if(!empty($metadata['payhero_status'])): ?>
                                            <dt>PayHero status</dt>
                                            <dd><?php echo e($metadata['payhero_status']); ?></dd>
                                        <?php endif; ?>
                                        <?php if(!empty($metadata['payhero_callback']['receipt_number'])): ?>
                                            <dt>M-Pesa receipt</dt>
                                            <dd><?php echo e($metadata['payhero_callback']['receipt_number']); ?></dd>
                                        <?php endif; ?>
                                        <?php if($transaction->last_status_checked_at): ?>
                                            <dt><?php echo app('translator')->get('Last status check'); ?></dt>
                                            <dd><?php echo e($transaction->last_status_checked_at->format('Y-m-d H:i')); ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                <?php endif; ?>
                                <?php if(!empty($transaction->failure_reason)): ?>
                                    <div class="text-danger mt-2"><strong><?php echo app('translator')->get('Failure reason'); ?>:</strong> <?php echo e($transaction->failure_reason); ?></div>
                                <?php endif; ?>
                                <?php if(!empty($metadata['admin_review']['feedback'])): ?>
                                    <div class="text-muted mt-2"><strong><?php echo app('translator')->get('Admin note'); ?>:</strong> <?php echo e($metadata['admin_review']['feedback']); ?></div>
                                <?php endif; ?>
                            </details>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo e($transaction->status === 'succeeded' ? 'success' : ($transaction->status === 'failed' ? 'danger' : ($transaction->status === 'processing' ? 'info' : 'warning'))); ?>">
                                <?php echo e(ucfirst($transaction->status)); ?>

                            </span>
                        </td>
                        <td>
                            <?php echo e(optional($transaction->updated_at)->format('Y-m-d H:i')); ?><br>
                            <small class="text-muted">Started <?php echo e(optional($transaction->created_at)->format('Y-m-d H:i')); ?></small>
                        </td>
                        <td style="min-width: 230px;">
                            <?php if($transaction->provider === 'manual' && in_array($transaction->status, ['pending', 'processing'], true)): ?>
                                <?php if(adminAccessRoute(array_merge(config('role.payment_log.access.edit', []), config('role.payout_manage.access.edit', [])))): ?>
                                    <form method="post" action="<?php echo e(route('admin.wallet-manual.review', $transaction->id)); ?>" class="mb-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="approve">
                                        <?php if($transaction->direction === 'withdrawal'): ?>
                                            <input class="form-control form-control-sm mb-2" name="provider_reference" maxlength="191" placeholder="<?php echo app('translator')->get('Payout transfer reference (optional)'); ?>">
                                            <button class="btn btn-sm btn-success" type="submit"><?php echo app('translator')->get('Mark paid'); ?></button>
                                        <?php else: ?>
                                            <input class="form-control form-control-sm mb-2" name="provider_reference" maxlength="191" placeholder="<?php echo app('translator')->get('Payment reference (optional)'); ?>">
                                            <button class="btn btn-sm btn-success" type="submit"><?php echo app('translator')->get('Approve deposit'); ?></button>
                                        <?php endif; ?>
                                    </form>
                                    <form method="post" action="<?php echo e(route('admin.wallet-manual.review', $transaction->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="reject">
                                        <textarea class="form-control form-control-sm mb-2" name="feedback" rows="2" maxlength="500" placeholder="<?php echo app('translator')->get('Reason for rejection'); ?>" required></textarea>
                                        <button class="btn btn-sm btn-danger" type="submit"><?php echo app('translator')->get('Reject'); ?></button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo app('translator')->get('Review permission required'); ?></span>
                                <?php endif; ?>
                            <?php elseif($transaction->provider === 'manual'): ?>
                                <span class="text-muted"><?php echo app('translator')->get('Reviewed'); ?></span>
                            <?php else: ?>
                                <span class="text-muted"><?php echo app('translator')->get('Automatic'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center"><?php echo app('translator')->get('No wallet transactions found.'); ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?php echo e($transactions->links()); ?></div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/wallet-manual/index.blade.php ENDPATH**/ ?>