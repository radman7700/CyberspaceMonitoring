<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramGroup;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Illuminate\Support\Facades\DB;
use Pishgaman\CyberspaceMonitoring\Services\StatisticsCalculator;

class PayeshInformationController extends Controller
{
    private $validActions = [
        'home',
        'telegramStatistics',
        'ReleaseProcess'
    ];

    protected $validMethods = [
        'GET' => ['home','telegramStatistics','ReleaseProcess'], // Added 'createAccessLevel' as a valid method-action pair
        'POST' => [], // Added 'createAccessLevel' as a valid action for POST method
        'PUT' => [],
        'DELETE' => []
    ];

    protected $user;
    protected $logRepository;
    protected $calculator;
    public function __construct(logRepository $logRepository,StatisticsCalculator $calculator)
    {
        $this->logRepository = $logRepository;
        $this->calculator = $calculator;
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

    public function home(Request $request)
    {
        if (!$this->isValidAction('home', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  
    

        //ابر کلمات به دست‌ آمده
        $wordCounts = DB::table('telegram_word_counts')
        ->select('word', DB::raw('SUM(word_count) as total_word_count'))
        ->groupBy('word')
        ->orderBy('total_word_count', 'desc')
        ->take($request->wordNumber ?? 100)
        ->where('word', '!=', '') // حذف داده‌های نامعتبر که متن خالی دارند
        ->get()
        ->keyBy('word')
        ->map(function ($item) {
            return [
                'text'=>$item->word,
                'weight'=>(int) $item->total_word_count,
            ];
        })
        ->values()
        ->toJson();        
        
        $PayeshInformation = [
            'wordCounts' => $wordCounts,
        ];

        return response()->json(['PayeshInformation' => $PayeshInformation], 200);
    }

    public function ReleaseProcess()
    {
        if (!$this->isValidAction('ReleaseProcess', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  

        $endDate = now(); // تاریخ فعلی
        $startDate = now()->subDays(30); // تاریخ 30 روز پیش از تاریخ فعلی
        
        $options = [
            'selectRaw' => 'DATE(date) as day, COUNT(*) as count',
            'groupby' => 'day',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'get' => true, // برای دریافت نتایج
        ];
        $ReleaseProcess = $this->calculator->ReleaseProcess($options);
        $dates = array();
        $count = array();

        foreach ($ReleaseProcess as $item) {
            $dates[] = $item['day'];
            $count[] = $item['count'];
        }        

        $ReleaseProcess = [
            'ReleaseProcessDates' => $dates,
            'ReleaseProcessCount' => $count
        ];

        return response()->json(['ReleaseProcess' => $ReleaseProcess], 200);
    }

    public function telegramStatistics()
    {
        if (!$this->isValidAction('telegramStatistics', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  

        $groupCount = $this->calculator->getTelegramGroupCount();
        $messageCount = $this->calculator->getTelegramMessageCount();
        $sessionCount = $this->calculator->getTelegramSessionCount();
        $UserCount = $this->calculator->getTelegramUsersCount();

        $telegramStatistics = [
            'UserCount' => $UserCount,
            'TelegramGroupCount' => $groupCount,
            'TelegramMessageCount' => $messageCount,
            'sessionCount' => $sessionCount,
        ];

        return response()->json(['telegramStatistics' => $telegramStatistics], 200);        
    }
}
