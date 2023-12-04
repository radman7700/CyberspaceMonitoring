<?php

namespace Pishgaman\CyberspaceMonitoring\Controllers\api;

use Illuminate\Http\Request;
use Pishgaman\Pishgaman\Repositories\LogRepository;
use Pishgaman\Pishgaman\Middleware\CheckMenuAccess;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;
use Pishgaman\CyberspaceMonitoring\Repositories\mainRepository;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramUser;
use Pishgaman\CyberspaceMonitoring\Database\models\TelegramChannelMessage;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Log;
use DB;
use Pishgaman\CyberspaceMonitoring\Jobs\ExportTelegramUsersJob;

class PayeshTelegramController extends Controller
{
    private $validActions = [
        'getMessageList',
        'getTelegramChannelMessagesList',
        'getUserList',
        'exportSubjectUsers'
    ];

    protected $validMethods = [
        'GET' => [
            'getMessageList',
            'getTelegramChannelMessagesList',
            'getUserList',
            'exportSubjectUsers'
        ], // Added 'createAccessLevel' as a valid method-action pair
        'POST' => [], // Added 'createAccessLevel' as a valid action for POST method
        'PUT' => [],
        'DELETE' => []
    ];

    protected $user;
    protected $logRepository;
    protected $TelegramMessageRepository;
    protected $mainRepository;

    public function __construct(logRepository $logRepository,TelegramMessageRepository $TelegramMessageRepository,mainRepository $mainRepository)
    {
        $this->logRepository = $logRepository;
        $this->mainRepository = $mainRepository;
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

    public function exportSubjectUsers($request)
    {
        if (!$this->isValidAction('exportSubjectUsers', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }
        
        $nextDay = Carbon::now();
        $beforday = (new Carbon($nextDay))->subDays(1)->format('Y-m-d h:i:s');
        
        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start;
        $endDate = (($request->date_end ?? '') == '') ? $nextDay : $request->date_end;

        dispatch(new ExportTelegramUsersJob());
        return 'Export job has been dispatched!';        
    }

    public function getUserList($request)
    {
        if (!$this->isValidAction('getUserList', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        if(($request->type ?? 1) == 1)
        {
            $options = [
                'query'=>TelegramUser::query(),
                'page'=>$request->page ?? 1, 
                // 'with' => ['TelegramGroup:id,gid,name','TelegramUser:id,first_name,last_name,username']          
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
            
            $UserList = $this->mainRepository->Get($options);        
    
            return response()->json(['UserList' => $UserList[0],'UserCount'=>$UserList[1]], 200);
        }
        else{
            $nextDay = Carbon::now();
            $beforday = (new Carbon($nextDay))->subDays(2)->format('Y-m-d h:i:s');
            
            $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start;
            $endDate = (($request->date_end ?? '') == '') ? $nextDay : $request->date_end;
    
            $topUsers = TelegramMessage::query();
            $topUsers->select('user_id', DB::raw('count(*) as total_messages'))
            ->where('user_id', '>', 0)
            ->whereBetween('date', [$startDate, $endDate]) // Add this line for date range
            ->groupBy('user_id')
            ->orderByDesc('total_messages')
            ->with(['TelegramUser' => function ($query) {
                $query->select('user_id', 'first_name', 'last_name', 'username');
            }]);
            
            
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
            $topUsersResult = $topUsers->paginate(10); 
    
            
            return response()->json(['UserList' => $topUsersResult,'UserCount'=>'-'], 200);
            
        }
    }

    public function getMessageList($request)
    {
        if (!$this->isValidAction('getMessageList', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $today = Carbon::now();
        $nextDay = $today->addDay()->format('Y-m-d h:i:s');
        $beforday = $today->subDays(60)->format('Y-m-d h:i:s');

        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start ;
        $endDate = (($request->date_end ?? '')== '') ? $nextDay : $request->date_end ;

        $options = [
            'query'=>TelegramMessage::query(),
            'sortings' => [
                ['column' => 'date', 'order' => 'desc'], 
            ],
            'conditions' => [
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],            
            'page'=>$request->page ?? 1, 
            'with' => ['TelegramGroup:id,gid,name','TelegramUser:id,first_name,last_name,username']          
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
        
        $messages = $this->TelegramMessageRepository->TelegramMessageGet($options);        

        return response()->json(['MessageList' => $messages[0],'MessageListCount'=>$messages[1]], 200);

    }

    public function getTelegramChannelMessagesList($request)
    {
        if (!$this->isValidAction('getTelegramChannelMessagesList', 'GET')) {
            return response()->json(['errors' => 'requestNotAllowed'], 422);
        }

        $today = Carbon::now();
        $nextDay = $today->addDay()->format('Y-m-d h:i:s');
        $beforday = $today->subDays(60)->format('Y-m-d h:i:s');

        $startDate = (($request->date_start ?? '') == '') ? $beforday : $request->date_start ;
        $endDate = (($request->date_end ?? '')== '') ? $nextDay : $request->date_end ;

        $options = [
            'query'=>TelegramChannelMessage::query(),
            'sortings' => [
                ['column' => 'date', 'order' => 'desc'], 
            ],
            'conditions' => [
                [
                    'column' => 'date',
                    'operator' => 'between',
                    'value' => [$startDate, $endDate],
                ],
            ],            
            'page'=>$request->page ?? 1, 
            'with' => ['TelegramGroup:id,gid,name','TelegramUser:id,first_name,last_name,username']          
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
        
        $messages = $this->TelegramMessageRepository->TelegramMessageGet($options);        

        return response()->json(['MessageList' => $messages[0],'MessageListCount'=>$messages[1]], 200);

    }    
}
