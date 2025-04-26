<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
    use HasFactory;
	protected $table = 'admin_settings';
	public $timestamps = false;

    protected $casts = [
        'commission_fee' => 'float',
        'shipping_fee' => 'float',
        'vat' => 'int',
        'advertisement_status' => 'bool'
    ];

    protected $fillable = [
        'currency_symbol',
        'currency_code',
        'commission_fee',
        'shipping_fee',
        'logo',
        'logo_2',
        'text_logo',
        'favicon',
        'maintenance_mode',
        'vat',
        'advertisement_status',
        'currency'
    ];
}
