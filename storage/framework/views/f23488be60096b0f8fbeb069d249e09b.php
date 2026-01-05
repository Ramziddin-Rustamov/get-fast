<?php $__env->startSection('title', 'Payment Details'); ?>

<?php $__env->startSection('content'); ?>

<div class="container py-4">

    <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-dark mb-3 rounded-pill">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-dark text-white rounded-top-4 py-3">
            <h4 class="mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Payment Details
            </h4>
        </div>

        <div class="card-body p-4">

            
            <div class="row g-4">

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-hashtag me-1"></i> Payment ID
                        </h6>
                        <p class="fw-bold fs-5"><?php echo e($payment->id); ?></p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-random me-1"></i> Pay UUID
                        </h6>
                        <p class="fw-bold text-primary"><?php echo e($payment->pay_id); ?></p>
                    </div>
                </div>

            </div>


            
            <h4 class="mt-5 mb-3 fw-bold">
                <i class="fas fa-user-check me-2"></i> User Information
            </h4>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-user me-1"></i> Full Name
                        </h6>
                        <p class="fw-bold">
                            <?php echo e($payment->user->first_name); ?> <?php echo e($payment->user->last_name); ?>

                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-phone me-1"></i> Phone
                        </h6>
                        <p><?php echo e($payment->user->phone); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-user-tag me-1"></i> Role
                        </h6>
                        <span class="badge bg-primary px-3 py-2 fs-6">
                            <?php echo e(ucfirst($payment->user->role)); ?>

                        </span>
                    </div>
                </div>

            </div>


            
            <h4 class="mt-5 mb-3 fw-bold">
                <i class="fas fa-credit-card me-2"></i> Card Information
            </h4>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="far fa-credit-card me-1"></i> Card Number
                        </h6>
                        <p class="fw-bold">**** <?php echo e(substr($payment->card->number ?? '----', -4)); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-calendar me-1"></i> Expire 
                        </h6>
                        <p><?php echo e($payment->card->expiry ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-calendar me-1"></i> Status
                        </h6>
                        <p class="fw-bold text-success" ><?php echo e($payment->card->status ?? 'N/A'); ?></p>
                    </div>
                </div>

            </div>


          

            
            <h4 class="mt-5 mb-3 fw-bold">
                <i class="fas fa-wallet me-2"></i> Payment Information
            </h4>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-coins me-1"></i> Amount
                        </h6>
                        <p class="fw-bold text-success fs-4">
                            <?php echo e(number_format($payment->amount)); ?> UZS
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-check-circle me-1"></i> Status
                        </h6>

                        <span class="badge px-4 py-2 fs-6
                            <?php if($payment->status=='confirmed'): ?> bg-success
                            <?php elseif($payment->status=='created'): ?> bg-warning text-dark
                            <?php else: ?> bg-secondary
                            <?php endif; ?>">
                            <?php echo e(ucfirst($payment->status)); ?>

                        </span>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 shadow-sm">
                        <h6 class="text-secondary">
                            <i class="fas fa-university me-1"></i> Method
                        </h6>
                        <p><?php echo e($payment->payment_method); ?></p>
                    </div>
                </div>

            </div>


            
            <h4 class="mt-5 mb-3 fw-bold">
                <i class="fas fa-history me-2"></i> Timeline
            </h4>

            <ul class="list-group shadow-sm rounded-3">
                <li class="list-group-item">
                    <i class="far fa-clock me-2 text-primary"></i>
                    <strong>Created:</strong> <?php echo e($payment->created_at); ?>

                </li>
                <li class="list-group-item">
                    <i class="fas fa-sync-alt me-2 text-secondary"></i>
                    <strong>Updated:</strong> <?php echo e($payment->updated_at); ?>

                </li>
            </ul>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/admin-views/payments/show.blade.php ENDPATH**/ ?>