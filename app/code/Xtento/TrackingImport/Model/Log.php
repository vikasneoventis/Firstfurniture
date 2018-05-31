<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:37:14+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Log.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model;

/**
 * Class Log
 * Log model which keeps track of successful/failed import attempts
 * @package Xtento\TrackingImport\Model
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    protected $resultMessages = [];
    protected $debugMessages = [];

    // Log result types
    const RESULT_NORESULT = 0;
    const RESULT_SUCCESSFUL = 1;
    const RESULT_WARNING = 2;
    const RESULT_FAILED = 3;

    protected function _construct()
    {
        $this->_init('Xtento\TrackingImport\Model\ResourceModel\Log');
        $this->_collectionName = 'Xtento\TrackingImport\Model\ResourceModel\Log\Collection';
    }

    public function setResult($resultLevel)
    {
        if ($this->getResult() === null) {
            $this->setData('result', $resultLevel);
        } else {
            if ($resultLevel > $this->getResult()) { // If result is failed, do not reset to warning for example.
                $this->setData('result', $resultLevel);
            }
        }
    }

    public function addResultMessage($message)
    {
        array_push($this->resultMessages, $message);
    }

    public function getResultMessages()
    {
        if (empty($this->resultMessages)) {
            return false;
        }
        return (count($this->resultMessages) > 1) ? implode("\n", $this->resultMessages) : $this->resultMessages[0];
    }

    public function addDebugMessage($message)
    {
        if ($this->getLogDebugMessages()) {
            array_push($this->debugMessages, $message);
        }
    }

    public function getDebugMessages()
    {
        if (empty($this->debugMessages)) {
            return false;
        }
        return (count($this->debugMessages) > 1) ? implode("\n", $this->debugMessages) : $this->debugMessages[0];
    }
}