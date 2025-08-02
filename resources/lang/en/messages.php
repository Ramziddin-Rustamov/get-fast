<?php
return [
    'title' => 'Balance Transactions',
    'id' => 'ID',
    'type' => 'Type',
    'amount' => 'Amount',
    'before' => 'Before',
    'after' => 'After',
    'status' => 'Status',
    'reason' => 'Reason',
    'created_at' => 'Created At',
    'contact' => 'Contact',
    'driver_booking_reason' => 'You received a new booking (Booking ID: :booking_id) for your trip (Trip ID: :trip_id). Total received: :total_price UZS. Service fee: :service_fee UZS. Net earnings (after tax): :net_income UZS.',
    'user_booking_reason' => 'You booked a trip (Trip ID: :trip_id) with :seats seat(s). Total amount: :total_price UZS, including service fee: :service_fee UZS. Booking ID: :booking_id. Amount has been deducted from your balance.',
   
    'driver_cancel_self' => 'You cancelled your own booking. 85% refunded, 15% penalty applied.',
    'driver_cancel_user' => 'Trip cancelled by the driver. You have received a full refund.',
];

