<?php

namespace wisder\yii\elasticsearch\log;

use wisder\yii\elasticsearch\Connection;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\queue\JobInterface;
use yii\queue\Queue;

class LogJob extends BaseObject implements JobInterface
{
    public $index;
    public $messages;

    /**
     * @var string
     */
    public $elasticsearch;

    public function execute($queue)
    {
        try {
            /** @var Connection $elasticsearch */
            $elasticsearch = Instance::ensure($this->elasticsearch, Connection::class);
        } catch (InvalidConfigException $exception) {
            \Yii::error('ElasticSearch client init err');
            return;
        }

        $writer = new LogWriter();
        $writer->write($elasticsearch, $this->messages, $this->index);
    }
}
