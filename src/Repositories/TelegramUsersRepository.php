<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramUser;
use Log;

class TelegramUsersRepository
{
    public function getIdCount()
    {
        return TelegramUser::select('id')->count();
    }

    public function TelegramUserGet(array $options, $perPage = 10)
    {
        $query = TelegramUser::query();
        // Log::info($options);

        // اضافه کردن 'orderby' اختیاری
        if (isset($options['sortings']) && is_array($options['sortings'])) {
            foreach ($options['sortings'] as $sorting) {
                if (isset($sorting['column'])) {
                    $order = isset($sorting['order']) && strtolower($sorting['order']) === 'desc' ? 'desc' : 'asc';
                    $query->orderBy($sorting['column'], $order);
                }
            }
        }
    
        // اضافه کردن 'groupby' اختیاری
        if (isset($options['groupby'])) {
            $query->groupBy($options['groupby']);
        }
    
        // اضافه کردن شروط اضافی اختیاری
        if (isset($options['conditions']) && is_array($options['conditions'])) {
            foreach ($options['conditions'] as $condition) {
                if (isset($condition['column']) && isset($condition['operator']) && isset($condition['value'])) {
                    $query->where($condition['column'], $condition['operator'], $condition['value']);
                }
            }
        }

        if (isset($options['searchString'])) {
            // $searchString = str_replace(' ','',$options['searchString']);
            // $searchString = str_replace('and','#',$options['searchString']);
            // $searchString = str_replace('not','#',$options['searchString']);
            // $searchString = str_replace('or','#',$options['searchString']);
            // $word = explode('#',$searchString);
            // $query->where('message',like, $condition['value']);
            // Log::info($word);

            $keywords = preg_split("/\s+(or|and)\s+/i", $options['searchString'], -1, PREG_SPLIT_DELIM_CAPTURE);

            // ایجاد شرط SQL
            $sqlCondition = '';
            foreach ($keywords as $key => $word) {
                if ($key % 2 == 0) {
                    // کلماتی که بین عبارات "OR" یا "AND" هستند
                    $sqlCondition .= "message LIKE '%$word%'";
                } else {
                    // عبارات "OR" یا "AND"
                    $sqlCondition .= strtoupper($word) . ' ';
                }
            }
    
            // افزودن شرط به کوئری
            $query->whereRaw($sqlCondition);
        }        
    
        // اضافه کردن مشخصات برای select
        if (isset($options['select']) && is_array($options['select'])) {
            $query->select($options['select']);
        }
    
        // اضافه کردن شمارش رکوردها
        $count = isset($options['count']) && $options['count'] ? true : false;
    
        if ($count) {
            return $query->count();
        }
        
        $get = isset($options['get']) && $options['get'] ? true : false;
        if ($get) {
            return $query->get();
        }

        if (isset($options['with']) && is_array($options['with'])) {
            $query->with($options['with']);
        }

        // اضافه کردن صفحه‌بندی
        $page = isset($options['page']) ? $options['page'] : 1;
        $result = $query->paginate($perPage, ['*'], 'page', $page);
        // Log::info('Query Log: ' . $query->toSql());

        return $result;
    }   

    function getUsersCounts(array $options, $perPage = 10)
    {
        $query = TelegramUser::selectRaw($options['selectRaw']);

        if (isset($options['groupby'])) {
            $query->groupBy($options['groupby']);
        }        
    
        if ($options['startDate'] && $options['endDate']) {
            $query->whereBetween('date', [$options['startDate'], $options['endDate']]);
        }
    
        $count = isset($options['count']) && $options['count'] ? true : false;
        if ($count) {
            return $query->count();
        }
        
        $get = isset($options['get']) && $options['get'] ? true : false;
        if ($get) {
            return $query->get();
        }

        // اضافه کردن صفحه‌بندی
        $page = isset($options['page']) ? $options['page'] : 1;
        return $query->paginate($perPage, ['*'], 'page', $page);
    }    
}
