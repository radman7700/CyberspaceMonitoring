<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramGroup;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramUser;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramChannelMessage;
use Illuminate\Support\Facades\DB;
use Pishgaman\CyberspaceMonitoring\Services\StatisticsCalculator;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Log;

class PayeshInformationController extends Controller
{
    private $validActions = [
        'home',
        'telegramStatistics',
        'ReleaseProcess',
        'channelHome',
        'channeltelegramStatistics',
        'channelReleaseProcess', 
        'getInfluentialUsers',
        'getInfluentialGroup'       
    ];

    protected $validMethods = [
        'GET' => [
            'home',
            'telegramStatistics',
            'ReleaseProcess',
            'channelHome',
            'channeltelegramStatistics',
            'channelReleaseProcess',
            'getInfluentialUsers',
            'getInfluentialGroup'
        ],
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
            'query'=>TelegramMessage::query(),
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
            $dates[] = Jalalian::fromDateTime( $item['day'])->format('Y/m/d');
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

        $nextDay = Carbon::now();
        $beforday = (new Carbon($nextDay))->subDays(1)->format('Y-m-d h:i:s');
        
        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start;
        $endDate = (($request->date_end ?? '') == '') ? $nextDay : $request->date_end;

        $options = [
            'query' => TelegramMessage::query(),
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

    public function channelHome(Request $request)
    {
        if (!$this->isValidAction('channelHome', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  
    

        //ابر کلمات به دست‌ آمده
        $wordCounts = DB::table('telegram_channel_word_counts')
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

    public function channelReleaseProcess(Request $request)
    {
        if (!$this->isValidAction('channelReleaseProcess', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  

        $endDate = now(); // تاریخ فعلی
        $startDate = now()->subDays(30); // تاریخ 30 روز پیش از تاریخ فعلی
        
        $startDate = (($request->date_start ?? '') == '') ? $startDate : $request->date_start ;
        $endDate = (($request->date_end ?? '')== '') ? $endDate : $request->date_end ;

        $options = [
            'query' => TelegramChannelMessage::query(),
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
            $dates[] = Jalalian::fromDateTime( $item['day'])->format('Y/m/d');
            $count[] = $item['count'];
        }        

        $ReleaseProcess = [
            'ReleaseProcessDates' => $dates,
            'ReleaseProcessCount' => $count
        ];

        return response()->json(['ReleaseProcess' => $ReleaseProcess], 200);
    }

    public function channeltelegramStatistics(Request $request)
    {
        if (!$this->isValidAction('channeltelegramStatistics', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }  

        $today = Carbon::now();

        $nextDay = Carbon::now();
        $beforday = (new Carbon($nextDay))->subDays(1)->format('Y-m-d h:i:s');
        
        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start;
        $endDate = (($request->date_end ?? '') == '') ? $nextDay : $request->date_end;

        $options = [
            'query' => TelegramChannelMessage::query(),
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

        $groupCount = $this->calculator->getTelegramGroupCount('channel');
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
    
    /**
     * بازگرداندن اطلاعات کاربران پرنفوذ بر اساس تعداد پیام‌های ارسالی.
     *
     * @return \Illuminate\Support\Collection
     */
    function getInfluentialUsers(Request $request) 
    {
        if (!$this->isValidAction('getInfluentialUsers', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        } 

        $nextDay = Carbon::now();
        $beforday = (new Carbon($nextDay))->subDays(1)->format('Y-m-d h:i:s');
        
        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start;
        $endDate = (($request->date_end ?? '') == '') ? $nextDay : $request->date_end;

        $topUsers = TelegramMessage::query();
        
        $topUsers->select('user_id', DB::raw('count(*) as total_messages'))
            ->where('user_id', '>', 0)
            ->whereBetween('date', [$startDate, $endDate]) // Add this line for date range
            ->groupBy('user_id')
            ->orderByDesc('total_messages');
        
        if (($request->search_text ?? '') != '') {
            $keywords = preg_split("/\s+(or|and)\s+/i", $request->search_text, -1, PREG_SPLIT_DELIM_CAPTURE);
        
            // Create SQL condition
            $sqlCondition = '';
            foreach ($keywords as $key => $word) {
                if ($key % 2 == 0) {
                    // Words between "OR" or "AND" expressions
                    $sqlCondition .= "message LIKE '%$word%'";
                } else {
                    // "OR" or "AND" expressions
                    $sqlCondition .= strtoupper($word) . ' ';
                }
            }
        
            // Add condition to the query
            $topUsers->whereRaw($sqlCondition);
        }
        
        // Retrieve top users
        $topUsersResult = $topUsers->take(10)->get();

        

        $users = [];
        foreach ($topUsersResult as $user) {
            $userId = $user->user_id;
            $totalMessages = $user->total_messages;
        
            $userData = TelegramUser::where('user_id',$userId)->first(); 
            $users[] =  ($userData->first_name ?? $userId) . ' ' . ($userData->last_name ?? '');      
        }

        $messagesCounts = $topUsers->pluck('total_messages')->toArray();
        
        $influentialUsers = [
            'user' => $users,
            'messages_count' => $messagesCounts
        ];

        return response()->json(['influentialUsers' => $influentialUsers], 200);
    }

    function getInfluentialGroup(Request $request) 
    {
        if (!$this->isValidAction('getInfluentialGroup', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        } 

        $nextDay = Carbon::now();
        $beforday = (new Carbon($nextDay))->subDays(1)->format('Y-m-d h:i:s');
        
        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start;
        $endDate = (($request->date_end ?? '') == '') ? $nextDay : $request->date_end;

        $topUsers = TelegramMessage::query();
        $topUsers->select('gid', DB::raw('count(*) as total_messages'))
            ->where('gid', '>', 0)
            ->whereBetween('date', [$startDate, $endDate]) // Add this line for date range
            ->groupBy('gid')
            ->orderByDesc('total_messages');
        
        if (($request->search_text ?? '') != '') {
            $keywords = preg_split("/\s+(or|and)\s+/i", $request->search_text, -1, PREG_SPLIT_DELIM_CAPTURE);
        
            // Create SQL condition
            $sqlCondition = '';
            foreach ($keywords as $key => $word) {
                if ($key % 2 == 0) {
                    // Words between "OR" or "AND" expressions
                    $sqlCondition .= "message LIKE '%$word%'";
                } else {
                    // "OR" or "AND" expressions
                    $sqlCondition .= strtoupper($word) . ' ';
                }
            }
        
            // Add condition to the query
            $topUsers->whereRaw($sqlCondition);
        }
        
        // Retrieve top users
        $topUsersResult = $topUsers->take(10)->get();
        

        $groups = [];
        foreach ($topUsersResult as $group) {
            $gid = $group->gid;
            $totalMessages = $group->total_messages;
        
            $groupData = TelegramGroup::where('gid',$gid)->first(); 
            $groups[] =  ($groupData->name ?? $gid);      
        }

        $messagesCounts = $topUsers->pluck('total_messages')->toArray();
        
        $groupMessageCounts = [
            'group' => $groups,
            'messages_count' => $messagesCounts
        ];

        return response()->json(['groupMessageCounts' => $groupMessageCounts], 200);
    }    
}
