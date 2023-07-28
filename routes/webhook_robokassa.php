<?php

use Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

if(!is_null(config('services.robokassa.result_url'))) {
    Route::get(config('services.robokassa.result_url'), [WebhookController::class, 'index'])->name("wh_robokassa.get")->domain(config('services.robokassa.result_domain'));
    Route::post(config('services.robokassa.result_url'), [WebhookController::class, 'index'])->name("wh_robokassa.post")->domain(config('services.robokassa.result_domain'));
}
