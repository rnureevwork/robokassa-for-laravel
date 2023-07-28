<?php

namespace Icekristal\RobokassaForLaravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class WebhookController extends BaseController
{
    public function index(Request $request): Response
    {
        if (!$request->has('InvId') || !$request->has('OutSum') || !$request->has('SignatureValue')) return new Response("bad params", 400);

        $invId = $request->get('InvId');
        $outSum = $request->get('OutSum');
        $signatureValue = $request->get('SignatureValue');



        return new Response("OK{$invId}", 200);
    }
}
