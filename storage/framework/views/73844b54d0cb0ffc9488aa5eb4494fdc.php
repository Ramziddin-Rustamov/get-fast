<?php $__env->startSection('title', 'Drivers List'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h1 class="text-center mb-4">🚖 <?php echo e(__("Haydavchilar ")); ?></h1>

    

    <div class="row mb-3">
        <div class="col-md-12 d-flex flex-wrap align-items-center">
            <form action="<?php echo e(route('drivers.index')); ?>" method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control me-2 mb-2" placeholder="🔍 Haydovchi qidiring..." value="<?php echo e($search); ?>">
                <div class="btn-group me-2 mb-2" role="group">
                    <button type="submit" name="status" value="" class="btn btn-outline-secondary <?php echo e($status == '' ? 'active' : ''); ?>">Barchasi</button>
                    <button type="submit" name="status" value="none" class="btn btn-outline-dark <?php echo e($status == 'none' ? 'active' : ''); ?>">None</button>
                    <button type="submit" name="status" value="pending" class="btn btn-outline-warning <?php echo e($status == 'pending' ? 'active' : ''); ?>">Pending</button>
                    <button type="submit" name="status" value="approved" class="btn btn-outline-success <?php echo e($status == 'approved' ? 'active' : ''); ?>">Approved</button>
                    <button type="submit" name="status" value="rejected" class="btn btn-outline-danger <?php echo e($status == 'rejected' ? 'active' : ''); ?>">Rejected</button>
                    <button type="submit" name="status" value="blocked" class="btn btn-outline-dark <?php echo e($status == 'blocked' ? 'active' : ''); ?>">Blocked</button>
                </div>
            </form>
        </div>
    </div>
    
    

    
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Ismi</th>
                    <th>Telefon</th>
                    <th>Ro'li</th>
                    <th>Sms orqali tasdiqlanish</th>
                    <th>Hozirgi holati </th>
                    
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="text-center">
                        <td><?php echo e($driver->id); ?></td>
                        <td><?php echo e($driver->first_name); ?></td>
                        <td><?php echo e($driver->phone); ?></td>
                        <td><?php echo e(ucfirst($driver->role)); ?></td>
                        <td class="text-center">
                            <?php if($driver->is_verified): ?>
                                <span class="badge bg-success">Tasdiqlangan</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Tasdiqlanmagan</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php
                                $statusColors = [
                                    'none' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'blocked' => 'bg-dark text-white'
                                ];
                                $status = $driver->driving_verification_status ?? 'none';
                                $badgeClass = $statusColors[$status] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?> px-3 py-2 rounded-pill">
                                <?php echo e(ucfirst($status)); ?>

                            </span>
                        </td>
                        
                        <td>
                            <a href="<?php echo e(route('drivers.show', $driver->id)); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>

                           
            <?php if($drivers->hasPages()): ?>
            <div class="d-flex justify-content-center mt-3">
                <?php echo e($drivers->links('pagination::bootstrap-5')); ?>

            </div>
            <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No drivers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="d-flex justify-content-center mt-4">
        <?php echo e($drivers->links()); ?>

    </div>
</div>


<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/admin-views/drivers/index.blade.php ENDPATH**/ ?>