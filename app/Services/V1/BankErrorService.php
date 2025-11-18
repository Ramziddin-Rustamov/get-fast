<?php

namespace App\Services\V1;

use App\Models\V1\BankError;
use App\Repositories\V1\BankErrorRepository;


class BankErrorService
{
    public static array $errors = [

        1000 => 'Internal server error',
        1001 => 'Insufficient balance on card',
        1002 => 'The card is not found',
        1003 => 'Wrong action',
        1004 => 'Send OTP is not working',
        1005 => 'The merchant is not found',
        1006 => 'The terminal is not tied to the merchant',
        1007 => 'The commission is not found',
        123321 => 'The otp is wrong',
        1009 => 'Hold method is not available now',
        1010 => 'Confirm method is not available now',
        1011 => 'Missing method field in request',
        1012 => 'Missing params field in request',
        1013 => 'Unknown method',
        1014 => 'Invalid request params',
        1015 => 'Invalid or non-existing payment ID',
        1016 => 'Action closed by admin',
        1017 => 'Merchant account not found',
        1018 => 'Cancellation time is over',
        1019 => 'Remote service error',
        1020 => 'Merchant has insufficient balance',
        1021 => 'Card expired',
        1022 => 'Login or password is incorrect',
        1023 => 'Card BIN not found',
        1024 => 'Card expiry date incorrect',
        1025 => 'Page with such limit not found',
        1026 => 'Limit must be greater than zero',
        1027 => 'Client available after returned time',
        1028 => 'Client available after returned time',
        1029 => 'You can send SMS in 1 minute intervals',
        1030 => 'Payment confirmation code expired',
        1031 => 'Resend SMS time is expired',
        1032 => 'Card already exists in system',
        1033 => 'Card already verified or code expired',
        1034 => 'Card is not active',
        1035 => 'External ID exists in database',
        1036 => 'Something went wrong with SMS',
        1037 => 'Service not found',
        1038 => 'Partner returned timeout error',
        1039 => 'Payment registration failed',
        1040 => 'Fields are required',
        1041 => 'Service not tied to merchant',
        1042 => 'Service not available for this merchant',
        1043 => 'Invalid field reference code',
        1044 => 'Card not tied to merchant',
        1045 => 'Limit is over',
        1047 => 'Method not working, contact admin',
        1048 => 'Method not tied to merchant',
        1049 => 'Method not available for this merchant',
        1050 => 'Merchant account is blocked',
        1051 => 'Payment already processed via P2P',
        1052 => 'Card is limited by the bank',
        1053 => 'Phone number is incorrect',
        1054 => 'Card token expired or card not verified',
        1055 => 'Details not found',
        1056 => 'URL expired',
        1057 => 'Session interrupted, try after 5 minutes',
        1058 => 'SMS already sent, try after 1 minute',
        1059 => 'Amount exceeds allowed limit',
        1060 => 'Payment unsuccessful for provided RRN',
        1061 => 'Method works only at specific times',
        1062 => 'Card is not Uzcard',
        1063 => 'Phone number has multiple cards with same last digits',
        1064 => 'Payment with this external_id already exists',
        1065 => 'This card type not allowed',
        1066 => 'This method not allowed for this merchant today',
    ];

    // ERROR KODLARIDAN QAYSI BIRLARI SMS TALAB QILADI
    public static array $smsErrors = [
        1004,
        1029,
        1030,
        1031,
        1036,
        1058,
        123321,
    ];

    public static function getMessage($code): string
    {
        return self::$errors[$code] ?? 'Unknown error';
    }

    public static function smsRequired($code): bool
    {
        return in_array($code, self::$smsErrors);
    }
}