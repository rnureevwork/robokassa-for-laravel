<?php

namespace Icekristal\RobokassaForLaravel\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string setSum(float|int $sum)
 * @method static string setOwner($owner)
 * @method static string setDescription(string $description)
 * @method static string setEmail(string $email)
 * @method static string setCurrency(string $currency)
 * @method static string setShpParams(array $shpParams)
 * @method static array getShpParams()
 * @method static string getPaymentUrl()
 * @method static string setExpirationDate(Carbon $expirationDate)
 * @method static bool isAccessSignature(string $signatureValue, int $invId, float|int $sum, array $shpParams, $type = 'init')
 *
 */
class Robokassa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ice.robokassa';
    }
}
