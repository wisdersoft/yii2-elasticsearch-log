<?php

namespace wisder\yii\elasticsearch\log;

class LogIndex
{
    public static function index()
    {
        return [
            '@timestamp' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss.SSSSSS',
            ],
            'message' => [
                'type' => 'text',
            ],
            'level' => [
                'type' => 'keyword',
            ],
            'category' => [
                'type' => 'keyword',
            ],
            'stack_trace' => [
                'type' => 'text',
            ],
            'request_id' => [
                'type' => 'text',
            ],
        ];
    }
}
