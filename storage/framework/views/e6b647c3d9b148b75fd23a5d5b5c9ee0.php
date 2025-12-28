<?php $__env->startSection('title', 'Payments'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <h2 class="fw-bold mb-4">
        <i class="fas fa-money-check-alt me-2"></i> Payments Overview
    </h2>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-0">

            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>#</th>
                        <th><i class="fas fa-user"></i> User</th>
                        <th><i class="fas fa-credit-card"></i> Card</th>
                        <th><i class="fas fa-coins"></i> Amount</th>
                        <th><i class="fas fa-check-circle"></i> Status</th>
                        <th><i class="fas fa-wallet"></i> Method</th>
                        <th><i class="fas fa-clock"></i> Date</th>
                        <th><i class="fas fa-eye"></i> View</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="table-row-hover">
                        <td class="fw-bold">#<?php echo e($p->id); ?></td>

                        
                        <td>
                            <i class="fas fa-user-circle text-primary me-1"></i>
                            <?php echo e($p->user->first_name ?? 'Unknown'); ?>

                        </td>

                        
                        <td>
                            <i class="far fa-credit-card text-success me-1"></i>
                            **** <?php echo e(substr($p->card->number ?? '----', -4)); ?>

                        </td>

                        
                        
                        <td class="fw-bold text-dark">
                            <?php echo e(number_format($p->amount)); ?> <small>UZS</small>
                        </td>

                        
                        <td>
                            <span class="badge px-3 py-2 
                                <?php if($p->status === 'confirmed'): ?> bg-success
                                <?php elseif($p->status === 'created'): ?> bg-warning text-dark
                                <?php else: ?> bg-secondary
                                <?php endif; ?>">
                                <?php echo e(ucfirst($p->status)); ?>

                            </span>
                        </td>

                        
                        <td>
                            <i class="fas fa-university text-muted me-1"></i>
                            <?php echo e($p->payment_method); ?>

                        </td>

                        
                        <td>
                            <i class="fas fa-calendar-alt text-muted me-1"></i>
                            <?php echo e($p->created_at->format('d M Y H:i')); ?>

                        </td>

                        <td>
                            <a href="<?php echo e(route('payments.show', $p->id)); ?>"
                               class="btn btn-sm btn-primary rounded-pill">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>

                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-3">
        <?php echo e($payments->links()); ?>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/get-fast/resources/views/admin-views/payments/index.blade.php ENDPATH**/ ?>