<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\web;

use Illuminate\Http\Request;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;

class PayeshTelegramController extends Controller
{
    private $validActions = [
        'telegramChannelMessagesList',
        'messagesList',
        'userList'
        // 'other_action',  // Add other safe actions here
    ];

    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }    
    /**
     * Validate the requested action name.
     */
    private function isValidAction($functionName)
    {
        return in_array($functionName, $this->validActions);
    }

    /**
     * Handle the "messagesList" action.
     */
    public function messagesList(Request $request)
    {
        // Execute the "messagesList" method only if it is a valid action.
        if (!$this->isValidAction('messagesList')) {
            return abort(404);
        }

        $mix = ['packages/pishgaman/CyberspaceMonitoring/src/resources/vue/messagesListApp.js'];

        return view('CyberspaceMonitoringView::messagesList',['mix' => $mix]);
    }

    public function telegramChannelMessagesList(Request $request)
    {
        // Execute the "messagesList" method only if it is a valid action.
        if (!$this->isValidAction('telegramChannelMessagesList')) {
            return abort(404);
        }

        $mix = ['packages/pishgaman/CyberspaceMonitoring/src/resources/vue/messagesTelegramChannelListApp.js'];

        return view('CyberspaceMonitoringView::messagesList',['mix' => $mix]);
    }   
    
    /**
     * Handle the "userList" action.
     */
    public function userList(Request $request)
    {
        // Execute the "userList" method only if it is a valid action.
        if (!$this->isValidAction('userList')) {
            return abort(404);
        }

        $mix = ['packages/pishgaman/CyberspaceMonitoring/src/resources/vue/userListApp.js'];

        return view('CyberspaceMonitoringView::userList',['mix' => $mix]);
    } 

}
