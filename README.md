# Robokassa for laravel
Integration service robokassa for laravel

install:

```php
composer require icekristal/robokassa-for-laravel
```

Add to config/services.php

```php
 'robokassa' => [
    'login' => env('ROBOKASSA_LOGIN', 'login'),
    'password_one' => env('ROBOKASSA_PASSWORD_ONE', null),
    'password_two' => env('ROBOKASSA_PASSWORD_TWO', null),
    'password_test_one' => env('ROBOKASSA_PASSWORD_TEST_ONE', null),
    'password_test_two' => env('ROBOKASSA_PASSWORD_TEST_TWO', null),
    'webhook_domain' => env('ROBOKASSA_WEBHOOK_DOMAIN', null),
    'result_url' => env('ROBOKASSA_RESULT_URL', '/robokassa/payment/result'),
    'success_url' => env('ROBOKASSA_SUCCESS_URL', '/robokassa/payment/success'),
    'fail_url' => env('ROBOKASSA_FAIL_URL', '/robokassa/payment/fail'),
],
```

Publish config:
```php
php artisan vendor:publish --provider="Icekristal\RobokassaForLaravel\RobokassaServiceProvider" --tag='config'
```

Publish migrations:
```php
php artisan vendor:publish --provider="Icekristal\RobokassaForLaravel\RobokassaServiceProvider" --tag='migrations'
```

Use:
```php
php artisan migrate
```

Get payment url:
```php
use Icekristal\RobokassaForLaravel\Facades\Robokassa;

$paymentUrl = Robokassa::setSum(100)
->setCurrency("USD")
->setDescription("Description order")->getPaymentUrl();

//optional
$paymentUrl = Robokassa::setSum(100)
->setCurrency("USD")
->setDescription("Description order")
->setOwner(\Illuminate\Database\Eloquent\Model::class) //Model owner order
->setEmail('test@gmail.com') //Email owner order
->setShpParams(['param_1' => 'value_1', 'param_2' => 'value_2']) //Additional params
->setExpirationDate(Carbon::now()->addDay()) //Expiration date payment
->getPaymentUrl();
```
