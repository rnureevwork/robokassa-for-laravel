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
     * @return array
     */
    private function checkParams(Request $request)
    {
        $infoCheck = [
            'status' => 200,
            'message' => 'OK',
        ];
        if (!$request->has('InvId') || !$request->has('OutSum') || !$request->has('SignatureValue')) $infoCheck = [
            'status' => 400,
            'message' => 'error check params',
        ];

        $this->invId = $request->get('InvId', null);
        $this->outSum = $request->get('OutSum', 0);
        $this->signatureValue = $request->get('SignatureValue', '');

        if (!Robokassa::isAccessSignature($this->signatureValue, $this->invId, $this->outSum, $request->all())) $infoCheck = [
            'status' => 403,
            'message' => 'error check signature',
        ];

        if (!RobokassaModel::query()->find($this->invId)->exists()) $infoCheck = [
            'status' => 404,
            'message' => 'Not found',
        ];

        return $infoCheck;
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
        $info = $this->checkParams($request);
        if ($info['status'] !== 200) return new Response($info['message'], $info['status']);
        $this->updateStatus(RobokassaStatusEnum::WAITING);
        return new Response("OK{$this->invId}", 200);
    }

    /**
     * Success URL
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|Response|\Illuminate\Routing\Redirector
     */
    public function success(Request $request)
    {
        $info = $this->checkParams($request);
        if ($info['status'] !== 200) return new Response($info['message'], $info['status']);
        $this->updateStatus(RobokassaStatusEnum::PAID);
        if(!is_null(config('robokassa.redirect_success_url'))) {
            return redirect(config('robokassa.redirect_success_url'));
        }
        return new Response("OK{$this->invId}", 200);
    }

    /**
     * Fail URL
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|Response|\Illuminate\Routing\Redirector
     */
    public function fail(Request $request)
    {
        $info = $this->checkParams($request);
        if ($info['status'] !== 200) return new Response($info['message'], $info['status']);
        $this->updateStatus(RobokassaStatusEnum::CANCEL);
        if(!is_null(config('robokassa.redirect_fail_url'))) {
            return redirect(config('robokassa.redirect_fail_url'));
        }
        return new Response("OK{$this->invId}", 200);
    }
}
