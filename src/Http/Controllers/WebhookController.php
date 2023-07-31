<?php

namespace Icekristal\RobokassaForLaravel\Http\Controllers;


use Icekristal\RobokassaForLaravel\Enums\RobokassaStatusEnum;
use Icekristal\RobokassaForLaravel\Facades\Robokassa;
use Icekristal\RobokassaForLaravel\Http\Models\Robokassa as RobokassaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class WebhookController extends BaseController
{
    public int|null $invId = null;
    public mixed $outSum = 0;
    public string $signatureValue = '';

    /**
     * @param Request $request
     * @return Response|void
     */
    private function checkParams(Request $request)
    {
        if (!$request->has('InvId') || !$request->has('OutSum') || !$request->has('SignatureValue')) return new Response("bad params", 400);

        $this->invId = $request->get('InvId', null);
        $this->outSum = $request->get('OutSum', 0);
        $this->signatureValue = $request->get('SignatureValue', '');

        if (!Robokassa::isAccessSignature($this->signatureValue, $this->invId, $this->outSum, $request->all())) return new Response("bad signature", 400);
        if (!RobokassaModel::query()->find($this->invId)->exists()) return new Response("bad invId", 400);
    }

    /**
     * @param RobokassaStatusEnum $status
     * @return void
     */
    private function updateStatus(RobokassaStatusEnum $status)
    {
        $robokassa = RobokassaModel::query()->where('id', $this->invId)->first();
        if(!is_null($robokassa)) {
            $robokassa->update([
                'status' => $status->value,
                'paid_at' => $status === RobokassaStatusEnum::PAID ? now() : null,
            ]);
        }

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
