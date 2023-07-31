<?php

namespace Http\Models;

use Enums\RobokassaStatusEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $owner_type
 * @property integer $owner_id
 * @property integer $inv_id
 * @property string $description
 * @property float $amount
 * @property string $lang
 * @property string $email
 * @property string $currency
 * @property RobokassaStatusEnum $status
 * @property boolean $is_send
 * @property string $payment_url
 * @property \Carbon\Carbon $expiration_date
 * @property \Carbon\Carbon $paid_at
 * @property object $send_data
 * @property object $answer_data
 * @property \Carbon\Carbon $send_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Robokassa extends Model
{

    protected $table = 'robokassa';

    protected $fillable = [
        'owner_type', 'owner_id', 'inv_id', 'description', 'amount', 'lang', 'email', 'currency', 'status', 'is_send',
        'payment_url', 'expiration_date', 'paid_at', 'send_data', 'answer_data', 'send_at',
    ];

    protected $casts = [
        'send_data' => 'object',
        'answer_data' => 'object',
        'send_at' => 'datetime',
        'paid_at' => 'datetime',
        'expiration_date' => 'datetime',
        'amount' => 'float',
        'is_send' => 'boolean',
        'status' => RobokassaStatusEnum::class,
    ];
}
