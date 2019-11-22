<?php

namespace wisder\yii\elasticsearch\log;

use wisder\yii\elasticsearch\Connection;

class LogWriter
{
    public function write(Connection $elasticsearch, $messages, $index)
    {
        $params = [];
        foreach ($messages as $message) {
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                ],
            ];
            $params['body'][] = $message;
        }

        $elasticsearch->getClient()->bulk($params);
    }
}
