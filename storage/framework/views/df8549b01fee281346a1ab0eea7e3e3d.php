<?php $__env->startSection('title', 'Orders List'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2 class="mb-4 text-center">📦 Orders List</h2>

    
    <form method="GET" class="mb-3 d-flex justify-content-start gap-2">
        <select name="status" class="form-select w-auto">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo e(request('status')=='pending'?'selected':''); ?>>Pending</option>
            <option value="confirmed" <?php echo e(request('status')=='confirmed'?'selected':''); ?>>Confirmed</option>
            <option value="canceled" <?php echo e(request('status')=='canceled'?'selected':''); ?>>Canceled</option>
            <option value="completed" <?php echo e(request('status')=='completed'?'selected':''); ?>>Completed</option>
        </select>
        
        <select name="date" class="form-select w-auto">
            <option value="">All Dates</option>
            <option value="today" <?php echo e(request('date')=='today'?'selected':''); ?>>Today</option>
            <option value="week" <?php echo e(request('date')=='week'?'selected':''); ?>>This Week</option>
            <option value="last_week" <?php echo e(request('date')=='last_week'?'selected':''); ?>>Last Week</option>
        </select>
        
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-secondary">Reset</a>
    </form>

    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-clock text-primary fs-5"></i>
        <span class="text-secondary fw-semibold fs-6">
            <?php echo e(\Carbon\Carbon::now()->locale('en')->translatedFormat('l, d F Y H:i')); ?>

        </span>
    </div>
    

    
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0 text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Trip</th>
                    <th>Client</th>
                    <th>Driver</th>
                    <th>Booked Seats</th>
                    <th>Total Price</th>
                    <th>Price P/Seat</th>
                    <th>Status</th>
                    <th>Start/End Time</th>
                    <th>Data/ C and U</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($booking->id); ?></td>
                    <td>
                        <?php if($booking->trip): ?>
                        <div>
                            <strong><?php echo e($booking->trip->startQuarter->name ?? 'N/A'); ?> → <?php echo e($booking->trip->endQuarter->name ?? 'N/A'); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo e($booking->trip->vehicle->model ?? 'Vehicle N/A'); ?></small>
                            <small class="text-muted"><?php echo e($booking->trip->vehicle->car_number ?? 'Vehicle N/A'); ?></small>
                        </div>
                        <?php else: ?>
                        <span class="text-muted">No Trip</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo e($booking->user->first_name ?? ''); ?> <?php echo e($booking->user->last_name ?? 'Unknown'); ?><br>
                        <small class="text-muted"><?php echo e($booking->user->phone ?? '-'); ?></small>
                    </td>
                    <td>
                        <?php echo e($booking->trip->driver->first_name ?? ''); ?> <?php echo e($booking->trip->driver->last_name ?? 'Unknown'); ?><br>
                        <small class="text-muted"><?php echo e($booking->trip->driver->phone ?? '-'); ?></small>
                    </td>
                    <td><?php echo e($booking->seats_booked); ?></td>
                    <td><?php echo e(number_format($booking->total_price, 2)); ?> UZS</td>
                    <td><?php echo e(number_format($booking->trip->price_per_seat, 2)); ?> UZS</td>

                    <td>
                        <span class="badge
                            <?php if($booking->status == 'pending'): ?> bg-secondary
                            <?php elseif($booking->status == 'confirmed'): ?> bg-success
                            <?php elseif($booking->status == 'cancelled'): ?> bg-danger 
                            <?php endif; ?>
                        ">
                            <?php echo e(ucfirst($booking->status)); ?>

                        </span>
                    </td>
                    

                    <td>
                        <small class="text-muted">Start At: <?php echo e($booking->trip->start_time); ?></small><br>
                        <small class="text-muted">End At: <?php echo e($booking->trip->end_time); ?></small><br>
                    </td>


                    <td>
                        <small class="text-muted">Cr At: <?php echo e($booking->created_at->format('d.m.Y H:i')); ?></small><br>
                        <small class="text-muted">Up At: <?php echo e($booking->updated_at->format('d.m.Y H:i')); ?></small>
                    </td>
                    <td>
                        <a href="<?php echo e(route('orders.show', $booking->id)); ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-muted">No orders found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <?php echo e($bookings->links()); ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/admin-views/orders/index.blade.php ENDPATH**/ ?>