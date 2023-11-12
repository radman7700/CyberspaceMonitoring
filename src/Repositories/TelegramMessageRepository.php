<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Pishgaman\CyberspaceMonitoring\Database\models\TelegramMessage;

class TelegramMessageRepository
{
    public function getIdCount()
    {
        return TelegramMessage::select('id')->count();
    }

    public function TelegramMessageGet(array $options, $perPage = 10)
    {
        $query = TelegramMessage::query();

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
        return $query->paginate($perPage, ['*'], 'page', $page);
    }   

    function getMessageCounts(array $options, $perPage = 10)
    {
        $query = TelegramMessage::selectRaw($options['selectRaw']);

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