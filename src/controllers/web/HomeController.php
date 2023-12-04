<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\web;

use Illuminate\Http\Request;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramWordRepository;

class HomeController extends Controller
{
    private $validActions = [
        'home',
        'channelHome'
        // 'other_action',  // Add other safe actions here
    ];

    protected $user;
    protected $TelegramWordRepository;

    public function __construct(TelegramWordRepository $TelegramWordRepository)
    {
        $this->user = auth()->user();
        $this->TelegramWordRepository = $TelegramWordRepository;
    }    
    /**
     * Validate the requested action name.
     */
    private function isValidAction($functionName)
    {
        return in_array($functionName, $this->validActions);
    }

    /**
     * Handle the "home" action.
     */
    public function channelHome(Request $request)
    {
        // Execute the "home" method only if it is a valid action.
        if (!$this->isValidAction('home')) {
            return abort(404);
        }
        $endDate = now(); // تاریخ فعلی
        $startDate = now()->subDays(30);

        $mix = ['packages/pishgaman/CyberspaceMonitoring/src/resources/vue/MonitoringChannelHomeApp.js'];
        $card = 'میز کار پایش';

        return view('CyberspaceMonitoringView::channelHome',['mix' => $mix]);
    }

    public function home(Request $request)
    {
        // Execute the "home" method only if it is a valid action.
        if (!$this->isValidAction('home')) {
            return abort(404);
        }
        $endDate = now(); // تاریخ فعلی
        $startDate = now()->subDays(30);

        $mix = ['packages/pishgaman/CyberspaceMonitoring/src/resources/vue/MonitoringApp.js'];
        $card = 'میز کار پایش';

        return view('CyberspaceMonitoringView::Home',['mix' => $mix]);
    }    

    public function WordCount(Request $request)
    {
        ini_set('max_execution_time', 0); // 0 به معنی بی‌نهایت (برنامه هیچ وقت متوقف نمی‌شود)

        $this->TelegramWordRepository->CountTelegramWordMessageInDB(50000,100001);
    }
}
