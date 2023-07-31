<?php

namespace Icekristal\RobokassaForLaravel\Services;

use Carbon\Carbon;
use Exception;
use Icekristal\RobokassaForLaravel\Enums\RobokassaStatusEnum;
use Icekristal\RobokassaForLaravel\Http\Models\Robokassa;
use Illuminate\Support\Facades\Log;

class IceRobokassaService
{

    private string $login;
    private string $password1;
    private string $password2;
    private string $testMode;
    private string $paymentUrl;
    private string $email;
    private float|int $sum;
    private int|null $invId;
    private string $description;
    private Carbon $expirationDate;
    private string $signatureValue;
    private string $culture = 'ru';
    private string $encoding = 'utf-8';
    private string $currency = 'RUB';
    private array $shpParams = [];
    private array $mainParams;
    private mixed $owner = null;


    public function __construct()
    {
        $this->login = config('services.robokassa.login');
        $this->testMode = config('robokassa.is_test_mode', false);

        if ($this->testMode) {
            $this->password1 = config('services.robokassa.password_test_one');
            $this->password2 = config('services.robokassa.password_test_two');
        } else {
            $this->password1 = config('services.robokassa.password_one');
            $this->password2 = config('services.robokassa.password_two');
        }

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
     * @param int|null $invId
     * @param float|int $sum
     * @param array $shpParams
     * @param string $typePassword
     * @return bool
     */
    public function isAccessSignature(string $signatureValue, int|null $invId, float|int $sum, array $shpParams, string $typePassword = 'init'): bool
    {
        $signature = vsprintf('%u:%u:%s%s', [
            $sum,
            $invId,
            $typePassword == 'init' ? $this->password1 : $this->password2,
            $this->getShpParamsString($shpParams)
        ]);

        return strtoupper(md5($signature)) === strtoupper($signatureValue);
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
        $sum = round($sum, 2);
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
        foreach ($shpParams as $key => $shpParam) {
            if (stripos($key, 'shp_') === 0) {
                $setArray[] = $shpParam;
            } else {
                $setArray['shp_'.$key] = $shpParam;
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
        $this->signatureValue = vsprintf('%s:%u:%u:%s', [
            $this->login,
            $this->sum,
            $this->invId,
            $this->password1
        ]);

        if (count($this->shpParams) >= 1) {
            ksort($this->shpParams);

            $this->signatureValue .= ':' . implode(':', array_map(static function ($key, $value) {
                    return $key . '=' . $value;
                }, array_keys($this->shpParams), $this->shpParams));
        }

        $this->setSignatureValue(md5($this->signatureValue));

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
        $transactionDb = $this->newTransactionDb();
        $this->setInvId($transactionDb?->id);

        if (empty($this->description)) {
            throw new \Exception('Error description robokassa');
        }

        $this->getSignatureValue();

        $data = http_build_query($this->mainParams, null, '&');
        $shp = http_build_query($this->shpParams, null, '&');
        $this->paymentUrl = config('robokassa.base_url', 'https://auth.robokassa.ru/Merchant/Index.aspx?') . $data . ($shp ? '&' . $shp : '');
        $this->sendTransactionDb($transactionDb);
        return $this->paymentUrl;
    }


    /**
     * @return ?Robokassa
     */
    private function newTransactionDb(): ?Robokassa
    {
        try {
            return Robokassa::query()->create([
                'owner_type' => $this->owner?->getMorphClass() ?? null,
                'owner_id' => $this->owner?->id ?? null,
                'description' => $this->description ?? null,
                'amount' => $this->sum ?? null,
                'lang' => $this->culture ?? 'ru',
                'email' => $this->email ?? null,
                'currency' => $this->currency,
                'status' => RobokassaStatusEnum::NEW->value,
                'is_send' => false,
                'payment_url' => null,
                'expiration_date' => $this->expirationDate ?? null,
                'send_data' => null,
                'answer_data' => null,
                'send_at' => null,
            ]);
        } catch (Exception $e) {
            Log::warning('Error save robokassa', [$e->getMessage()]);
            return null;
        }
    }

    /**
     * @param Robokassa $robokassa
     * @return void
     */
    private function sendTransactionDb(Robokassa $robokassa): void
    {
        try {
            $robokassa->updateQuietly([
                'is_send' => true,
                'status' => RobokassaStatusEnum::WAITING->value,
                'payment_url' => $this->paymentUrl,
                'send_data' => array_merge($this->mainParams, $this->shpParams),
                'send_at' => now(),
            ]);
        } catch (Exception $exception) {
            Log::error('Error send robokassa', [$exception->getMessage()]);
        }
    }

    /**
     * @param string $signatureValue
     * @return IceRobokassaService
     */
    public function setSignatureValue(string $signatureValue): IceRobokassaService
    {
        $this->signatureValue = $signatureValue;
        $this->mainParams['SignatureValue'] = $signatureValue;
        return $this;
    }

    /**
     * @param $owner
     * @return $this
     */
    public function setOwner($owner): IceRobokassaService
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner(): mixed
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return int|null
     */
    public function getInvId(): ?int
    {
        return $this->invId;
    }

    /**
     * @param int|null $invId
     * @return $this
     */
    public function setInvId(?int $invId): IceRobokassaService
    {
        $this->invId = $invId;
        $this->mainParams['InvId'] = $invId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return IceRobokassaService
     */
    public function setEmail(string $email): IceRobokassaService
    {
        $this->email = $email;
        $this->mainParams['Email'] = $email;
        return $this;
    }

}
