<?php

namespace Pishgaman\CyberspaceMonitoring\Repositories;

use Log;

class mainRepository
{
    public function Get(array $options, $perPage = 10)
    {
        $query = $options['query'];

        if (isset($options['sortings']) && is_array($options['sortings'])) {
            foreach ($options['sortings'] as $sorting) {
                if (isset($sorting['column'])) {
                    $order = isset($sorting['order']) && strtolower($sorting['order']) === 'desc' ? 'desc' : 'asc';
                    $query->orderBy($sorting['column'], $order);
                }
            }
        }
    
        if (isset($options['groupby'])) {
            $query->groupBy($options['groupby']);
        }
    
        if (isset($options['conditions']) && is_array($options['conditions'])) {
            foreach ($options['conditions'] as $condition) {
                if (isset($condition['column']) && isset($condition['operator']) && isset($condition['value'])) {
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
    
        if (isset($options['select']) && is_array($options['select'])) {
            $query->select($options['select']);
        }
    
        if (isset($options['count']) && $options['count']) {
            return $query->count();
        }
    
        if (isset($options['get']) && $options['get']) {
            return $query->get();
        }
    
        if (isset($options['with']) && is_array($options['with'])) {
            $query->with($options['with']);
        }
    
        if (isset($options['take'])) {
            $query->take($options['take']);
        }
    
        // اضافه کردن صفحه‌بندی
        $page = isset($options['page']) ? $options['page'] : 1;
        $result = $query->paginate($perPage, ['*'], 'page', $page);
        Log::info('Query Log: ' . $query->toSql());
    
        return [$result, $query->count()];
    }      
}
