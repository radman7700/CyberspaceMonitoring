<?php

namespace Pishgaman\CyberspaceMonitoring\Exports;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelegramUsersExport implements FromCollection
{
    public function collection()
    {
        $nextDay = Carbon::now();
        $beforday = (new Carbon($nextDay))->subDays(10)->format('Y-m-d h:i:s');
        
        // افزودن پارامترهای ورودی به درخواست
        app('request')->merge([
            'date_start' => request()->input('date_start', $beforday),
            'date_end' => request()->input('date_end', $nextDay),
            'search_text' => request()->input('search_text', ''),
        ]);

        $topUsersQuery = TelegramMessage::query();
        $topUsersQuery->select('user_id', DB::raw('count(*) as total_messages'))
            ->where('user_id', '>', 0)
            ->whereBetween('date', [app('request')->input('date_start'), app('request')->input('date_end')])
            ->groupBy('user_id')
            ->orderByDesc('total_messages')
            ->with(['TelegramUser' => function ($query) {
                $query->select('user_id', 'first_name', 'last_name', 'username');
            }]);
        
        $searchText = app('request')->input('search_text');
        
        if ($searchText != '') {
            $keywords = preg_split("/\s+(or|and)\s+/i", $searchText, -1, PREG_SPLIT_DELIM_CAPTURE);
        
            $sqlCondition = '';
            foreach ($keywords as $key => $word) {
                if ($key % 2 == 0) {
                    $sqlCondition .= "message LIKE '%$word%'";
                } else {
                    $sqlCondition .= strtoupper($word) . ' ';
                }
            }
        
            $topUsersQuery->whereRaw($sqlCondition);
        }
        
        $topUsersResult = $topUsersQuery->get();

        // $Result = [];
        // foreach ($topUsersResult as $key => $value) {
        //     $Result[] = [$value->user_id,($value->telegram_user->first_name ?? ''),($value->telegram_user->last_name ?? ''),($value->telegram_user->username ?? ''),$value->total_messages];
        // }
        return $topUsersResult;
    }
}
