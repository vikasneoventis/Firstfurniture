<?php

namespace Wyomind\DataFeedManager\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{

    public $fileName = '/var/log/DataFeedManager.log';
    public $loggerType = \Monolog\Logger::NOTICE;
}
