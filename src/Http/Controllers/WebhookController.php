<?php

namespace Icekristal\RobokassaForLaravel\Http\Controllers;

use Enums\RobokassaStatusEnum;
use Facades\Robokassa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class WebhookController extends BaseController
{
    public int $invId;
    public mixed $outSum;
    public string $signatureValue;

    /**
     * @param Request $request
     * @return Response|void
     */
    private function checkParams(Request $request)
    {
        if (!$request->has('InvId') || !$request->has('OutSum') || !$request->has('SignatureValue')) return new Response("bad params", 400);

        $this->invId = $request->get('InvId');
        $this->outSum = $request->get('OutSum');
        $this->signatureValue = $request->get('SignatureValue');

        if (!Robokassa::isAccessSignature($this->signatureValue, $this->invId, $this->outSum, $request->all())) return new Response("bad signature", 400);
        if (!\Http\Models\Robokassa::query()->find($this->invId)->exists()) return new Response("bad invId", 400);
    }

    /**
     * @param RobokassaStatusEnum $status
     * @return void
     */
    private function updateStatus(RobokassaStatusEnum $status)
    {
        $robokassa = \Http\Models\Robokassa::query()->find($this->invId);
        $robokassa->update([
            'status' => $status->value,
            'paid_at' => $status === RobokassaStatusEnum::PAID ? now() : null,
        ]);
    }

    /**
     * Result URL
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $this->checkParams($request);
        $this->updateStatus(RobokassaStatusEnum::WAITING);
        return new Response("OK{$this->invId}", 200);
    }

    /**
     * Success URL
     *
     * @param Request $request
     * @return Response
     */
    public function success(Request $request): Response
    {
        $this->checkParams($request);
        $this->updateStatus(RobokassaStatusEnum::PAID);
        return new Response("OK{$this->invId}", 200);
    }

    /**
     * Fail URL
     *
     * @param Request $request
     * @return Response
     */
    public function fail(Request $request): Response
    {
        $this->checkParams($request);
        $this->updateStatus(RobokassaStatusEnum::CANCEL);
        return new Response("OK{$this->invId}", 200);
    }
}
