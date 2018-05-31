<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-09T13:06:54+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Source/Custom.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Source;

use Magento\Framework\DataObject;

class Custom extends AbstractClass
{
    public function testConnection()
    {
        $this->initConnection();
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setSource($this->sourceFactory->create()->load($this->getSource()->getId()));
        $testResult = new DataObject();
        $this->setTestResult($testResult);
        if (!@$this->objectManager->create($this->getSource()->getCustomClass())) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Custom class NOT found.'));
            $this->getSource()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage(
                $this->getTestResult()->getMessage()
            )->save();
            return false;
        } else {
            $this->getTestResult()->setSuccess(true)->setMessage(__('Custom class found and ready to use.'));
            $this->getSource()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage(
                $this->getTestResult()->getMessage()
            )->save();
            return true;
        }
    }

    public function loadFiles()
    {
        // Init connection
        if (!$this->initConnection()) {
            return false;
        }
        // Call custom class
        $filesToProcess = $this->objectManager->create($this->getSource()->getCustomClass())->loadFiles();
        return $filesToProcess;
    }

    public function archiveFiles($filesToProcess, $forceDelete = false)
    {
        // Init connection
        if (!$this->initConnection()) {
            return false;
        }
        @$this->objectManager->create($this->getSource()->getCustomClass())->archiveFiles();
    }
}