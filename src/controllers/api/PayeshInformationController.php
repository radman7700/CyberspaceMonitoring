<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramGroup;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Illuminate\Support\Facades\DB;
use Pishgaman\CyberspaceMonitoring\Services\StatisticsCalculator;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;
use Carbon\Carbon;

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
    protected $TelegramMessageRepository;

    public function __construct(logRepository $logRepository,StatisticsCalculator $calculator,TelegramMessageRepository $TelegramMessageRepository)
    {
        $this->logRepository = $logRepository;
        $this->TelegramMessageRepository = $TelegramMessageRepository;
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
                'link'=>$item->word
            ];
        })
        ->values()
        ->toJson();        
        
        $PayeshInformation = [
            'wordCounts' => $wordCounts,
        ];

        return response()->json(['PayeshInformation' => $PayeshInformation], 200);
    }

    public function ReleaseProcess(Request $request)
    {
        if (!$this->isValidAction('ReleaseProcess', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  

        $endDate = now(); // تاریخ فعلی
        $startDate = now()->subDays(30); // تاریخ 30 روز پیش از تاریخ فعلی
        
        $startDate = (($request->date_start ?? '') == '') ? $startDate : $request->date_start ;
        $endDate = (($request->date_end ?? '')== '') ? $endDate : $request->date_end ;

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

    public function telegramStatistics(Request $request)
    {
        if (!$this->isValidAction('telegramStatistics', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  

        $today = Carbon::now();
        $nextDay = $today->addDay()->format('Y-m-d h:i:s');
        $beforday = $today->subDays(30)->format('Y-m-d h:i:s');

        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start ;
        $endDate = (($request->date_end ?? '')== '') ? $nextDay : $request->date_end ;

        $options = [
            'select' => ['id'],
            'conditions' => [
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],   
            'count'=>true         
        ];

        if(($request->search_user_id ?? '') != '')
        {
            $options['conditions'][] = ['column' => 'user_id', 'operator' => 'like', 'value' => '%'.$request->search_user_id.'%'];
        }

        if(($request->search_group_id ?? '') != '')
        {
            $options['conditions'][] = ['column' => 'gid', 'operator' => 'like', 'value' => '%'.$request->search_group_id.'%'];            
        }        

        if ($request->search_text != '') {
            $options['searchString'] = $request->search_text;
        }

        $groupCount = $this->calculator->getTelegramGroupCount();
        $messageCount =$this->TelegramMessageRepository->TelegramMessageGet($options);
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
