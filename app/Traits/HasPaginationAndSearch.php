<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasPaginationAndSearch
{
    /**
     * 從 Request 解析分頁、排序、搜尋參數
     * 也會把其他動態欄位放到 'filters' 陣列
     */
    public function parseListParams(Request $request, array $extraFields = []): array
    {
        $filters = [];
        foreach ($extraFields as $field) {
            $value = $request->get($field, null);
            if ($value !== null && $value !== '') {
                $filters[$field] = trim($value);
            }
        }

        return [
            'page' => (int) $request->get('page', 1),
            'per_page' => (int) $request->get('per_page', 10),
            'search' => trim($request->get('search', '')),
            'sort' => $request->get('sort', 'created_at'),
            'direction' => $request->get('direction', 'desc'),
            'filters' => $filters,
        ];
    }
}
