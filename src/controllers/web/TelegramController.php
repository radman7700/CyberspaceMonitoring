<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\web;

use Illuminate\Http\Request;
use Pishgaman\PishgamanApi\Services\AuthenticationService;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramGroup;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramUser;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramWordRepository;
use Log;

class TelegramController extends Controller
{
    private $validActions = [
        'saveNewMsg',
        'saveGroup',
        'saveUser'
    ];

    protected $authenticationService;
    protected $TelegramWordRepository;

    public function __construct(AuthenticationService $authenticationService,TelegramWordRepository $TelegramWordRepository)
    {
        $this->authenticationService = $authenticationService;
        $this->TelegramWordRepository = $TelegramWordRepository;
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

        if($apicode != '1fad8dc28946')
        {
            return ['error' => 'Invalid credentials'];
        }

        $result = $this->authenticationService->authenticateUser($username, $password, $apicode);

        if (isset($result['error'])) {
            return response()->json($result, 401);
        }

        $mid = $request->input('mid');
        $gid = $request->input('gid');
        $message = $request->input('message');
        $user_id = $request->input('user_id');

        if($user_id == '-')
        {
            $user_id = null;
        }

        if ($mid) {
            $existingMessage = TelegramMessage::where([['mid', $mid],['gid',$gid]])->count();

            if ($existingMessage == 0) {
                $telegramMessage = new TelegramMessage();
                $telegramMessage->mid = $mid;
                $telegramMessage->gid = $gid;
                $telegramMessage->user_id = $user_id;
                $telegramMessage->date = $request->input('date');// یا مقدار مورد نظر بر اساس فرمت تاریخ
                $telegramMessage->message = $message;
                $telegramMessage->save();

                $this->TelegramWordRepository->CountTelegramWordMessage($telegramMessage->id);
                return response()->json($result);
            }
            else
            {
                return response()->json('existingMessage');
            }
        }

        return response()->json('mid not found');

    }

    public function saveGroup(Request $request)
    {
        if (!$this->isValidAction('saveGroup')) {
            return abort(404);
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $apicode = $request->input('apicode');

        if($apicode != '1fad8dc28946')
        {
            return ['error' => 'Invalid credentials'];
        }

        $result = $this->authenticationService->authenticateUser($username, $password, $apicode);

        if (isset($result['error'])) {
            return response()->json($result, 401);
        }

        $data = [
            'gid' => $request->input('group_id'),
            'name' => $request->input('group_name'),
            'username' => $request->input('username'),
            'participants_count' => $request->input('group_participants_count'),
            'description' => $request->input('group_description'),
            'session' => $request->input('session'),
        ];
        
        $condition = [
            'gid' => $request->input('gid'),
            'session' => $request->input('session'),
        ];
        
        TelegramGroup::updateOrInsert($condition, $data);

        return response()->json(['success'=>true]);

    }
  
    public function saveUser(Request $request)
    {
        if (!$this->isValidAction('saveGroup')) {
            return abort(404);
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $apicode = $request->input('apicode');

        if($apicode != '1fad8dc28946')
        {
            return ['error' => 'Invalid credentials'];
        }

        $result = $this->authenticationService->authenticateUser($username, $password, $apicode);

        if (isset($result['error'])) {
            return response()->json($result, 401);
        }

        $data = [
            'user_id' => $request->input('telegram_user_id'),
            'first_name' => $request->input('telegram_first_name'),
            'last_name' => $request->input('telegram_last_name'),
            'username' => $request->input('telegram_username'),
        ];
        
        $condition = [
            'user_id' => $request->input('telegram_user_id'),
        ];
        
        TelegramUser::updateOrInsert($condition, $data);

        return response()->json(['success'=>true]);

    }     
}
