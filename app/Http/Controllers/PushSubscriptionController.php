<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function update(Request $request)
    {
        $this->validate($request, [
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);

        $endpoint = $request->endpoint;
        $key = $request->keys['p256dh'];
        $token = $request->keys['auth'];
        $contentEncoding = $request->contentEncoding ?? 'aesgcm';

        $user = $request->user();
        
        $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $this->validate($request, ['endpoint' => 'required']);
        
        $request->user()->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }
}
