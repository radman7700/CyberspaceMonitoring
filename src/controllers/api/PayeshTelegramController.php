<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;

class PayeshTelegramController extends Controller
{
    private $validActions = [
        'getMessageList'
    ];

    protected $validMethods = [
        'GET' => ['getMessageList'], // Added 'createAccessLevel' as a valid method-action pair
        'POST' => [], // Added 'createAccessLevel' as a valid action for POST method
        'PUT' => [],
        'DELETE' => []
    ];

    protected $user;
    protected $logRepository;
    protected $TelegramMessageRepository;
    public function __construct(logRepository $logRepository,TelegramMessageRepository $TelegramMessageRepository)
    {
        $this->logRepository = $logRepository;
        $this->TelegramMessageRepository = $TelegramMessageRepository;
        $this->middleware(CheckMenuAccess::class);
        $this->user = auth()->user();
    }

    public function action(Request $request)
    {
        if ($request->has('action')) {
            $functionName = $request->action;
            $method = $request->method();
            // Log::error('method: ' . $method);
            // Log::error('functionName: ' . $functionName);

            if ($this->isValidAction($functionName, $method)) {
                return $this->$functionName($request);
            } else {
                return response()->json(['errors' => 'requestNotAllowed'], 422);
            }
        }

        return abort(404);
    }

    private function isValidAction($functionName, $method)
    {
        return in_array($functionName, $this->validActions) && in_array($functionName, $this->validMethods[$method]);
    }

    public function getMessageList($request)
    {
        if (!$this->isValidAction('getMessageList', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $options = [
            'sortings' => [
                ['column' => 'date', 'order' => 'desc'], 
            ],
        ];
        
        $messages = $this->TelegramMessageRepository->TelegramMessageGet($options);        

        return response()->json(['MessageList' => $messages], 200);

    }
}
