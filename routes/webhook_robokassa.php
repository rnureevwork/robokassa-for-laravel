<?php

use Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

if(!is_null(config('services.robokassa.result_url'))) {
    Route::get(config('services.robokassa.result_url'), [WebhookController::class, 'index'])->name("wh_result_robokassa.get")->domain(config('services.robokassa.webhook_domain'));
    Route::post(config('services.robokassa.result_url'), [WebhookController::class, 'index'])->name("wh_result_robokassa.post")->domain(config('services.robokassa.webhook_domain'));
}

if(!is_null(config('services.robokassa.success_url'))) {
    Route::get(config('services.robokassa.success_url'), [WebhookController::class, 'success'])->name("wh_success_robokassa.get")->domain(config('services.robokassa.webhook_domain'));
    Route::post(config('services.robokassa.success_url'), [WebhookController::class, 'success'])->name("wh_success_robokassa.post")->domain(config('services.robokassa.webhook_domain'));
}

if(!is_null(config('services.robokassa.fail_url'))) {
    Route::get(config('services.robokassa.fail_url'), [WebhookController::class, 'fail'])->name("wh_fail_robokassa.get")->domain(config('services.robokassa.webhook_domain'));
    Route::post(config('services.robokassa.fail_url'), [WebhookController::class, 'dail'])->name("wh_fail_robokassa.post")->domain(config('services.robokassa.webhook_domain'));
}
