<?php

namespace Delsis\SupportBot;

use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main\Diag\LogFormatter;
use Bitrix\Main\Type\DateTime;
use Psr\Log\LogLevel;

class Logger extends FileLogger
{
    protected array $context = [];

    public const FILE_PATH = __DIR__ . '/../debug.log';

    public function __construct()
    {
        parent::__construct(self::FILE_PATH);
        $this->setFormatter(new LogFormatter());
    }

    public function log($level, $message, array $context = [])
    {
        $this->context = $context;

        parent::log($level, $message, $context);
    }

    protected function logMessage(string $level, $message)
    {
        if (is_array($this->context['data'])) {
            $message .= PHP_EOL . ' DATA -> ' . print_r($this->context['data'], true);
        } elseif (is_string($this->context['data'])) {
            $message .= PHP_EOL . ' DATA -> ' . $this->context['data'];
        }

        $logFormat = sprintf('{delimiter}'. PHP_EOL .' {date} - %s: %s ', $level, $message);

        $formatterParams = [
            'date' => new DateTime(),
        ];

        if ($level == LogLevel::ERROR) {
            $formatterParams['trace'] = debug_backtrace();
            $logFormat .= PHP_EOL . ' TRACE -> {trace}';
        }

        $logFormat .= PHP_EOL;

        parent::logMessage($level, $this->getFormatter()->format($logFormat, $formatterParams));
    }

    public static function create(string $id, $params = []): Logger
    {
        $id = strtoupper($id);
        static $loggers = [];

        if (!$loggers[$id]) {
            $loggers[$id] = new self();
        }

        return $loggers[$id];
    }
}
