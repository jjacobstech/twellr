<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminSetting
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $email_verification
 * @property string $email_no_reply
 * @property string $email_admin
 * @property string $currency_symbol
 * @property string $currency_code
 * @property int $min_subscription_amount
 * @property int $fee_commission
 * @property string $currency_position
 * @property int $days_process_withdrawals
 * @property string $google_login
 * @property string $logo
 * @property string $logo_2
 * @property string $text_logo
 * @property string $favicon
 * @property string $avatar
 * @property string $maintenance_mode
 * @property string $vat
 * @property string $wallet_format
 * @property bool $advertisement_status
 * @property string $referral_system
 * @property bool $push_notification_status
 * @property string $onesignal_appid
 * @property string $onesignal_restapi
 *
 * @package App\Models
 */
class AdminSetting extends Model
{
    protected $table = 'admin_settings';
    public $timestamps = false;

    protected $casts = [
        'fee_commission' => 'int',
        'days_process_withdrawals' => 'int',
        'advertisement_status' => 'bool',
        'push_notification_status' => 'bool'
    ];

    protected $fillable = [
        'title',
        'description',
        'email_verification',
        'email_no_reply',
        'email_admin',
        'currency_symbol',
        'currency_code',
        'min_subscription_amount',
        'fee_commission',
        'currency_position',
        'days_process_withdrawals',
        'google_login',
        'logo',
        'logo_2',
        'text_logo',
        'favicon',
        'avatar',
        'maintenance_mode',
        'vat',
        'wallet_format',
        'advertisement_status',
        'referral_system',
        'push_notification_status',
        'onesignal_appid',
        'onesignal_restapi'
    ];
}
