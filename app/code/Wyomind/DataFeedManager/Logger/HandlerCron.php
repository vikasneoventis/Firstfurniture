<?php

namespace Wyomind\DataFeedManager\Logger;

class HandlerCron extends \Magento\Framework\Logger\Handler\Base
{
    public $fileName = '/var/log/DataFeedManager-cron.log';
    public $loggerType = \Monolog\Logger::NOTICE;
}
