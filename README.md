# smsint-for-laravel
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
    'result_domain' => env('ROBOKASSA_RESULT_DOMAIN', null),
    'result_url' => env('ROBOKASSA_RESULT_URL', '/payment/result'),
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
