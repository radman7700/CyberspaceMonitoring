<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\web;

use Illuminate\Http\Request;

class TelegramMessages extends Controller
{
    private $validActions = [
        'home',
        'messagesList'
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

}
