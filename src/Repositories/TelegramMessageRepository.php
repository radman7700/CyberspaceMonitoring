<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;
use Log;
class TelegramMessageRepository
{
    public function getIdCount($startDate,$endDate)
    {
        return TelegramMessage::select('id')->count();
    }

    public function TelegramMessageGet(array $options, $perPage = 10)
    {
        $query = $options['query'];
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
                    // افزودن شرط به تابع
                    if ($condition['operator'] === 'between' && is_array($condition['value'])) {
                        $query->whereBetween($condition['column'], $condition['value']);
                    } else {
                        $query->where($condition['column'], $condition['operator'], $condition['value']);
                    }
                }
            }
        }

        if (isset($options['searchString'])) {
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
        Log::info('Query Log: ' . $query->toSql());

        return [$result,$query->count()];
    }   

    function getMessageCounts(array $options, $perPage = 10)
    {
        $query = $options['query'];
        $query = $query->selectRaw($options['selectRaw']);

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
