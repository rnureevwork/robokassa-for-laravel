<?php

namespace Icekristal\RobokassaForLaravel\Services;

use Carbon\Carbon;

class IceRobokassaService
{

    private string $login;
    private string $password1;
    private string $password2;
    private string $testPassword1;
    private string $testPassword2;
    private string $testMode;
    private string $paymentUrl;
    private string $testPaymentUrl;
    private float|int $sum;
    private int $invId;
    private string $description;
    private Carbon $expirationDate;
    private string $signatureValue;
    private string $culture = 'ru';
    private string $encoding = 'utf-8';
    private string $currency = 'RUB';
    private ?array $shpParams = null;
    private array $mainParams;


    public function __construct()
    {
        $this->login = config('services.robokassa.login');
        $this->password1 = config('services.robokassa.password_one');
        $this->password2 = config('services.robokassa.password_two');
        $this->testPassword1 = config('services.robokassa.test_password_one');
        $this->testPassword2 = config('services.robokassa.test_password_two');
        $this->testMode = config('robokassa.is_test_mode', false);

        $this->setMainParams([
            'MerchantLogin' => $this->login,
            'InvId' => null,
            'OutSum' => 0,
            'Description' => '',
            'SignatureValue' => '',
            'Culture' => $this->culture,
            'IncCurrLabel' => '',
            'Encoding' => $this->encoding,
            'IsTest' => $this->testMode,
        ]);
    }

    public function isAccessSignature(string $signatureValue, int $invId, float|int $sum): bool
    {
        return true;
    }

    /**
     * @param array $mainParams
     */
    public function setMainParams(array $mainParams): void
    {
        $this->mainParams = $mainParams;
    }

    /**
     * @return array
     */
    public function getMainParams(): array
    {
        return $this->mainParams;
    }

    /**
     * @return Carbon
     */
    public function getExpirationDate(): Carbon
    {
        return $this->expirationDate;
    }

    /**
     * @param Carbon $expirationDate
     * @return IceRobokassaService
     */
    public function setExpirationDate(Carbon $expirationDate): static
    {
        $this->expirationDate = $expirationDate;
        $this->mainParams['ExpirationDate'] = $expirationDate->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * @param float|int $sum
     * @return IceRobokassaService
     */
    public function setSum(float|int $sum): IceRobokassaService
    {
        $this->sum = $sum;
        $this->mainParams['OutSum'] = $sum;
        return $this;
    }

    /**
     * @param string $description
     * @return IceRobokassaService
     */
    public function setDescription(string $description): IceRobokassaService
    {
        $this->description = $description;
        $this->mainParams['Description'] = $description;
        return $this;
    }

    /**
     * @param string $currency
     * @return IceRobokassaService
     */
    public function setCurrency(string $currency): IceRobokassaService
    {
        $this->currency = $currency;
        $this->mainParams['IncCurrLabel'] = $currency;
        return $this;
    }

    /**
     * @param array|null $shpParams
     * @return IceRobokassaService
     */
    public function setShpParams(?array $shpParams): IceRobokassaService
    {
        $this->shpParams = $shpParams;
        return $this;
    }


    private function getCustomParamsString(array $source): string
    {
        $params = [];

        foreach ($source as $key => $val) {
            if (stripos($key, 'shp_') === 0) {
                $params[$key] = $val;
            }
        }

        ksort($params);

        $params = implode(':', array_map(static function ($key, $value) {
            return $key . '=' . $value;
        }, array_keys($params), $params));

        return $params ? ':' . $params : '';
    }


    /**
     * @param $name
     * @return mixed|null
     */
    public function getCustomParam($name): mixed
    {
        $key = 'shp_' . $name;

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

}
