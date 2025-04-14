<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminSetting
 * 
 * @property int $id
 * @property string|null $currency_symbol
 * @property string|null $currency_code
 * @property float $commission_fee
 * @property float $shipping_fee
 * @property string|null $logo
 * @property string|null $logo_2
 * @property string|null $text_logo
 * @property string|null $favicon
 * @property string $maintenance_mode
 * @property int $vat
 * @property bool $advertisement_status
 *
 * @package App\Models\Base
 */
class AdminSetting extends Model
{
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
		'advertisement_status'
	];
}
