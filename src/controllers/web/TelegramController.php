<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\web;

use Illuminate\Http\Request;
use Pishgaman\PishgamanApi\Services\AuthenticationService;

class TelegramController extends Controller
{
    private $validActions = [
        'saveNewMsg'
    ];

    protected $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }    

    private function isValidAction($functionName)
    {
        return in_array($functionName, $this->validActions);
    }

    public function saveNewMsg(Request $request)
    {
        if (!$this->isValidAction('saveNewMsg')) {
            return abort(404);
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $apicode = $request->input('apicode');

        $result = $this->authenticationService->authenticateUser($username, $password, $apicode);

        if (isset($result['error'])) {
            return response()->json($result, 401);
        }

        $mid = $request->input('mid');

        if ($mid) {
            $existingMessage = TelegramMessage::where('mid', $mid)->first();

            if (!$existingMessage) {
                $telegramMessage = new TelegramMessage();
                $telegramMessage->mid = $mid;
                $telegramMessage->gid = $request->input('gid');
                $telegramMessage->user_id = $request->input('user_id');
                $telegramMessage->date = $request->input('date');// یا مقدار مورد نظر بر اساس فرمت تاریخ
                $telegramMessage->message = $request->input('message');
                $telegramMessage->save();
            }
        }

        return response()->json($result);
    }
}
