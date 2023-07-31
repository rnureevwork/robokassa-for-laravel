<?php

namespace Services;

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

        $this->mainParams = [
            'MerchantLogin' => $this->login,
            'InvId' => null,
            'OutSum' => 0,
            'Description' => '',
            'SignatureValue' => '',
            'Culture' => $this->culture,
            'IncCurrLabel' => '',
            'Encoding' => $this->encoding,
            'IsTest' => $this->testMode,
        ];
    }

    /**
     * @param string $signatureValue
     * @param int $invId
     * @param float|int $sum
     * @param string $password
     * @return bool
     */
    public function isAccessSignature(string $signatureValue, int $invId, float|int $sum, string $password): bool
    {
        $signature = vsprintf('%u:%u:%s%s', [
            $sum,
            $invId,
            $password,
            $this->getShpParamsString($this->getShpParams())
        ]);

        return md5($signature) === strtolower($signatureValue);
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
     * @param array $shpParams
     * @return IceRobokassaService
     */
    public function setShpParams(array $shpParams): IceRobokassaService
    {
        $setArray = [];
        foreach ($shpParams as $shpParam) {
            if (stripos($shpParam, 'shp_') === 0) {
                $setArray[] = $shpParam;
            } else {
                $setArray[] = 'shp_' . $shpParam;
            }
        }
        $this->shpParams = $setArray;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getShpParams(): ?array
    {
        return $this->shpParams;
    }


    private function getShpParamsString(array $source): string
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
    public function getShpParam($name): mixed
    {
        $key = 'shp_' . $name;

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getSignatureValue(): string
    {
        $this->signatureValue = vsprintf('%s:%01.2F:%u:%s', [
            $this->login,
            $this->sum,
            $this->invId,
            $this->password1
        ]);

        if (!is_null($this->shpParams)) {
            ksort($this->shpParams);

            $this->signatureValue .= ':' . implode(':', array_map(static function ($key, $value) {
                    return $key . '=' . $value;
                }, array_keys($this->shpParams), $this->shpParams));
        }

        $this->setSignatureValue($this->signatureValue);

        return $this->signatureValue;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getPaymentUrl(): string
    {
        if ($this->sum < 0) {
            throw new \Exception('Error sum robokassa');
        }
        if ($this->invId < 0) {
            throw new \Exception('Error invId robokassa');
        }
        if (empty($this->description)) {
            throw new \Exception('Error description robokassa');
        }

        $this->getSignatureValue();

        $data = http_build_query($this->mainParams, null, '&');
        $shp = http_build_query($this->shpParams, null, '&');
        $this->paymentUrl = config('robokassa.base_url', 'https://auth.robokassa.ru/Merchant/Index.aspx?') . $data . ($shp ? '&' . $shp : '');
        return $this->paymentUrl;
    }

    /**
     * @param string $signatureValue
     * @return IceRobokassaService
     */
    public function setSignatureValue(string $signatureValue): IceRobokassaService
    {
        $this->signatureValue = $signatureValue;
        $this->mainParams['signatureValue'] = $signatureValue;
        return $this;
    }
}
