<?php $__env->startSection('title', 'Booking Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <h2 class="mb-4 text-center">🧾 Booking Details</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            Booking Information
        </div>

        <div class="card-body">

            <table class="table table-bordered mb-0">
                <tr>
                    <th>ID</th>
                    <td><?php echo e($order->id); ?></td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge
                            <?php if($order->status=='pending'): ?> bg-secondary
                            <?php elseif($order->status=='confirmed'): ?> bg-success
                            <?php elseif($order->status=='cancelled'): ?> bg-danger text-white
                            <?php endif; ?>
                        ">
                            <?php echo e(ucfirst($order->status)); ?>

                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Total Price</th>
                    <td><?php echo e(number_format($order->total_price,2)); ?> so'm</td>
                </tr>

                <tr>
                    <th>Seats Booked</th>
                    <td><?php echo e($order->seats_booked); ?></td>
                </tr>

                <tr>
                    <th>Booking Created</th>
                    <td><?php echo e($order->created_at); ?></td>
                </tr>

                <tr>
                    <th>Expired At</th>
                    <td><?php echo e($order->expired_at); ?></td>
                </tr>

                <tr>
                    <th>Departed At</th>
                    <td><?php echo e($order->departed_at ?? '—'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            Client Information
        </div>
        <div class="card-body">

            <?php if($order->user): ?>
            <table class="table table-bordered mb-0">
                <tr>
                    <th>Full Name</th>
                    <td><?php echo e($order->user->first_name ?? ''); ?> <?php echo e($order->user->last_name ?? ''); ?></td>
                </tr>

                <tr>
                    <th>Phone</th>
                    <td><?php echo e($order->user->phone); ?></td>
                </tr>

                <tr>
                    <th>Role</th>
                    <td><?php echo e(ucfirst($order->user->role)); ?></td>
                </tr>
            </table>
            <?php else: ?>
                <p class="text-muted">No user data</p>
            <?php endif; ?>

        </div>
    </div>

    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            Trip Information
        </div>

        <div class="card-body">
            <?php if($order->trip): ?>
            <table class="table table-bordered mb-0">
                <tr>
                    <th>Trip ID</th>
                    <td><?php echo e($order->trip->id); ?></td>
                </tr>

                <tr>
                    <th>Driver</th>
                    <td>
                        <?php echo e($order->trip->driver->first_name ?? ''); ?>

                        <?php echo e($order->trip->driver->last_name ?? ''); ?> <br>
                        <small class="text-muted"><?php echo e($order->trip->driver->phone); ?></small>
                    </td>
                </tr>

                <tr>
                    <th>From</th>
                    <td><?php echo e($order->trip->startQuarter->name ?? 'N/A'); ?></td>
                </tr>

                <tr>
                    <th>To</th>
                    <td><?php echo e($order->trip->endQuarter->name ?? 'N/A'); ?></td>
                </tr>

                <tr>
                    <th>Start Time</th>
                    <td><?php echo e($order->trip->start_time); ?></td>
                </tr>

                <tr>
                    <th>End Time</th>
                    <td><?php echo e($order->trip->end_time); ?></td>
                </tr>
            </table>
            <?php else: ?>
                <p class="text-muted">Trip not found</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            Passenger List
        </div>

        <div class="card-body">
            <?php if($order->passengers && count($order->passengers) > 0): ?>
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Passenger Name</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($index+1); ?></td>
                                <td><?php echo e($p->name); ?></td>
                                <td><?php echo e($p->phone); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No passengers</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="text-center mt-3">
        <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-dark px-4">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/admin-views/orders/show.blade.php ENDPATH**/ ?>