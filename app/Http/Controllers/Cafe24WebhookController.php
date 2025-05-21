<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Cafe24Webhook;

class Cafe24WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('📌 Cafe24 Webhook Received: ', $request->all());

        // 데이터 저장
        $webhook = new Cafe24Webhook();
        $webhook->mall_id = $request->input('resource.mall_id');
        $webhook->event_type = $request->input('event_no');
        $webhook->payload = json_encode($request->all());
        $webhook->save();

        return response()->json(['message' => 'Webhook received successfully']);
    }
}
