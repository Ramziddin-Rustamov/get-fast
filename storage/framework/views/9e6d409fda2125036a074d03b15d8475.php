<?php $__env->startSection('title', 'Company Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <h2 class="fw-bold mb-4">
        <i class="bi bi-table"></i> Company Transactions
    </h2>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">

            <table class="table table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Trip </th>
                        <th>Booking Client</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($t->id); ?></td>
                            <td class="fw-bold text-primary"><?php echo e(number_format($t->amount)); ?> UZS</td>
                            <td><?php echo e(number_format($t->balance_before)); ?></td>
                            <td><?php echo e(number_format($t->balance_after)); ?></td>
                            <td><?php echo e($t->trip->startQuarter->name ?? 'N/A'); ?> → <?php echo e($t->trip->endQuarter->name ?? 'N/A'); ?></td>
                            <td><?php echo e($t->booking->user->first_name ?? ''); ?> <?php echo e($t->booking->user->last_name ?? ''); ?></td>
                            <td>
                                <span class="badge 
                                    <?php if($t->type=='income'): ?> bg-success 
                                    <?php else: ?> bg-danger <?php endif; ?>">
                                    <?php echo e(ucfirst($t->type)); ?>

                                </span>
                            </td>
                            <td style="max-width: 300px; white-space: wrap;">
                                <?php echo e($t->reason); ?>

                            </td>
                            <td><?php echo e($t->created_at); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-3">
        <?php echo e($transactions->links()); ?>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/admin-views/company/transactions.blade.php ENDPATH**/ ?>