<?php

namespace wisder\yii\elasticsearch\log;

use wisder\yii\elasticsearch\Connection;
use yii\di\Instance;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;
use yii\queue\Queue;

class ElasticSearchLogTarget extends Target
{
    /**
     * @var string ElasticSearch index to save log messages
     */
    public $index;

    /**
     * @var Queue|string|array|null use Queue or not, set null to disable
     */
    public $queue;

    /**
     * @var Connection|string the ElasticSearch connection component id
     */
    public $elasticsearch = 'elasticsearch';

    private $queueElasticsearch;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if ($this->queue !== null) {
            $this->queueElasticsearch = $this->elasticsearch;
            $this->queue = Instance::ensure($this->queue, Queue::class);
        }

        $this->elasticsearch = Instance::ensure($this->elasticsearch, Connection::class, \Yii::$app);
    }

    public function export()
    {
        $messages = [];

        foreach ($this->messages as $message) {
            $messages[] = $this->formatMessage($message);
        }

        if ($this->queue) {
            $this->queue->push(new LogJob([
                'index' => $this->index,
                'messages' => $messages,
                'elasticsearch' => $this->queueElasticsearch,
            ]));
            return ;
        }

        $writer = new LogWriter();
        $writer->write($this->elasticsearch, $messages, $this->index);
    }

    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }

        $given = \DateTime::createFromFormat('U.u', YII_BEGIN_TIME, new \DateTimeZone('UTC'));
        if ($given instanceof \DateTime) {
            $timestamp = $given->format('Y-m-d H:i:s.u');
        } else {
            $timestamp = null;
        }
        unset($given);

        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}: {$trace['line']}";
            }
        }
        $stackTrace = implode("\n", $traces);
        unset($traces, $trace);

        $requestId = ''; // TODO: request id

        $result = [
            '@timestamp' => $timestamp,
            'category' => $category,
            'level' => $level,
            'message' => $text,
            'stack_trace' => $stackTrace,
            'request_id' => $requestId,
        ];

        return $result;
    }
}
