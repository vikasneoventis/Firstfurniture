<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-10-22T14:13:03+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Import
 * The actual import model handling object updates/imports
 *
 * @package Xtento\TrackingImport\Model
 */
class Import extends \Magento\Framework\Model\AbstractModel
{
    // Import entities
    const ENTITY_ORDER_UPDATE = 'order';

    // Import processors
    const PROCESSOR_CSV = 'csv';
    const PROCESSOR_XML = 'xml';

    // Import types
    const IMPORT_TYPE_TEST = 0; // Test Import
    const IMPORT_TYPE_MANUAL = 2; // From "Manual Import" screen
    const IMPORT_TYPE_CRONJOB = 3; // Cronjob Import

    protected $sources;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Xtento\XtCore\Helper\Server
     */
    protected $serverHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\TrackingImport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var \Xtento\TrackingImport\Logger\Logger
     */
    protected $xtentoLogger;

    /**
     * Import constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\TrackingImport\Helper\Module $moduleHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ProfileFactory $profileFactory
     * @param LogFactory $logFactory
     * @param \Xtento\TrackingImport\Logger\Logger $xtentoLogger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\TrackingImport\Helper\Module $moduleHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ProfileFactory $profileFactory,
        LogFactory $logFactory,
        \Xtento\TrackingImport\Logger\Logger $xtentoLogger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->request = $request;
        $this->serverHelper = $serverHelper;
        $this->moduleHelper = $moduleHelper;
        $this->objectManager = $objectManager;
        $this->profileFactory = $profileFactory;
        $this->logFactory = $logFactory;
        $this->xtentoLogger = $xtentoLogger;
    }

    /**
     * Get import entities
     * @return array
     */
    public function getEntities()
    {
        $values = [];
        $values[self::ENTITY_ORDER_UPDATE] = __('Order Update');
        return $values;
    }

    /**
     * Get file processors
     * @return array
     */
    public function getProcessors()
    {
        $values = [];
        $values[self::PROCESSOR_CSV] = __('CSV / TXT / Tab-Delimited / Fixed-Length');
        $values[self::PROCESSOR_XML] = __('XML');
        return $values;
    }

    /**
     * Get import types
     * @return array
     */
    public function getImportTypes()
    {
        $values = [];
        $values[self::IMPORT_TYPE_TEST] = __('Test Import');
        $values[self::IMPORT_TYPE_MANUAL] = __('Manual Import');
        $values[self::IMPORT_TYPE_CRONJOB] = __('Cronjob Import');
        return $values;
    }

    /**
     * Manual import from "Manual Import" screen
     *
     * @param $uploadedFile
     *
     * @return array
     * @throws LocalizedException
     */
    public function manualImport($uploadedFile = false)
    {
        $this->checkStatus();
        $this->setImportType(self::IMPORT_TYPE_MANUAL);
        $this->beforeImport();
        $generatedFiles = $this->runImport($uploadedFile);
        $this->afterImport();
        return $generatedFiles;
    }

    /**
     * Cronjob import
     * @return bool
     * @throws LocalizedException
     */
    public function cronImport()
    {
        $this->setImportType(self::IMPORT_TYPE_CRONJOB);
        $this->beforeImport();
        $importResult = $this->runImport();
        if (empty($importResult)) {
            $this->getLogEntry()->delete();
            return false;
        }
        $this->afterImport();
        return true;
    }

    /**
     * Called by all import routines, initiates the import
     *
     * @param $uploadedFile
     *
     * @return array
     * @throws LocalizedException
     */
    protected function runImport($uploadedFile = false)
    {
        try {
            $this->getLogEntry()->addDebugMessage(__('Starting import...'));
            if ($this->getTestMode()) {
                $this->getLogEntry()->addDebugMessage(
                    __('Test mode enabled. No real data will be imported. This is a tool to preview the import.')
                );
            } else {
                // Real import
                @set_time_limit(0);
                $this->serverHelper->increaseMemoryLimit('2048M');
            }
            if (!$this->getProfile()) {
                throw new LocalizedException(__('No profile to import specified.'));
            }
            if (!$uploadedFile) {
                $filesToProcess = $this->loadFiles();
            } else {
                $filesToProcess = [$uploadedFile];
                $this->getLogEntry()->addDebugMessage(__("Processing just the uploaded file."));
            }
            if (empty($filesToProcess)) {
                throw new LocalizedException(__('0 files have been retrieved from import sources.'));
            } else {
                $this->getLogEntry()->addDebugMessage(
                    __("%1 files have been retrieved from import sources.", count($filesToProcess))
                );
            }
            // Process files
            $processor = $this->objectManager->create(
                'Xtento\TrackingImport\Model\Processor\\' . ucfirst($this->getProfile()->getProcessor())
            )->setProfile($this->getProfile());
            $this->getLogEntry()->addDebugMessage(
                __(
                    "Using %1 processor to parse files.",
                    strtoupper($this->getProfile()->getProcessor())
                )
            );
            $updatesInFilesToProcess = $processor->getRowsToProcess($filesToProcess);
            if (empty($updatesInFilesToProcess)) {
                $this->archiveFiles($filesToProcess);
                $this->getLogEntry()->addDebugMessage(
                    __(
                        "%1 files have been parsed, however, they did not contain any valid updates. Make sure the import processors are set up properly. Try running a test import in the debug section.",
                        count($filesToProcess)
                    )
                );
                $this->getLogEntry()->addDebugMessage(
                    __("Files parsed: <pre>%1</pre>", print_r($filesToProcess, true))
                );
            } else {
                $this->getLogEntry()->addDebugMessage(
                    __(
                        "The following data has been parsed in the import file(s): %1",
                        print_r($updatesInFilesToProcess, true)
                    )
                );
            }
            $this->getLogEntry()->addDebugMessage(__('Trying to import the updates...'));
            // Import/update objects
            $import = $this->objectManager->create(
                'Xtento\TrackingImport\Model\Import\Iterator\\' . ucfirst($this->getProfile()->getEntity())
            );
            $import->setImportType($this->getImportType());
            $import->setTestMode($this->getTestMode());
            $import->setProfile($this->getProfile());
            $importResult = $import->processUpdates($updatesInFilesToProcess);
            if (!$importResult) {
                if (!$uploadedFile) {
                    $this->archiveFiles($filesToProcess);
                }
                throw new LocalizedException(__('0 %1 updates have been imported.', $this->getProfile()->getEntity()));
            }
            // Archive files
            if (!$uploadedFile) {
                $this->archiveFiles($filesToProcess);
            }
            if (is_array($importResult)) {
                $this->getLogEntry()->setRecordsImported($importResult['updated_record_count']);
            }
            return $importResult;
        } catch (\Exception $e) {
            if ($this->getLogEntry()) {
                $result = Log::RESULT_FAILED;
                if (preg_match('/have been imported/', $e->getMessage()) || preg_match(
                        '/have been retrieved/',
                        $e->getMessage()
                    )
                ) {
                    if ($this->getImportType() == self::IMPORT_TYPE_MANUAL) {
                        $result = Log::RESULT_WARNING;
                    } else {
                        return false;
                    }
                }
                $this->getLogEntry()->setResult($result);
                $this->getLogEntry()->addResultMessage($e->getMessage());
                $this->afterImport();
            }
            if ($this->getImportType() == self::IMPORT_TYPE_MANUAL || $this->getImportType() == self::IMPORT_TYPE_TEST
            ) {
                throw new LocalizedException(__($e->getMessage()));
            }
            return [];
        }
    }

    /**
     * Load files from sources
     * @return mixed
     * @throws \Exception
     */
    protected function loadFiles()
    {
        $sourcesChecked = 0;
        $this->sources = $this->getProfile()->getSources();
        foreach ($this->sources as $source) {
            $sourcesChecked++;
            try {
                $filesToProcess = $source->loadFiles();
                if (is_array($this->getFiles()) && is_array($filesToProcess)) {
                    $this->setFiles(array_merge($this->getFiles(), $filesToProcess));
                } else {
                    $this->setFiles($filesToProcess);
                }
            } catch (\Exception $e) {
                $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                $this->getLogEntry()->addResultMessage($e->getMessage());
            }
        }
        if ($sourcesChecked < 1) {
            throw new LocalizedException(
                __(
                    "Fatal Error: No import sources have been enabled for this profile. For manual/automatic imports to run, import sources must be defined where files will be downloaded from, OR a file must be uploaded manually."
                )
            );
        } else {
            $this->getLogEntry()->addDebugMessage(__("%1 import sources have been found.", $sourcesChecked));
        }

        // Dispatch event so files can be retrieved/modified
        $transportObject = new \Magento\Framework\DataObject();
        $transportObject->setFiles($this->getFiles());
        $this->_eventManager->dispatch('xtento_trackingimport_files_load_after', ['transport' => $transportObject]);
        $this->setFiles($transportObject->getFiles());

        // Return files
        return $this->getFiles();
    }

    /**
     * Archive files on sources
     *
     * @param $filesToProcess
     *
     * @return mixed
     */
    protected function archiveFiles($filesToProcess)
    {
        if (!$this->getTestMode()) {
            foreach ($this->sources as $source) {
                try {
                    $source->archiveFiles($filesToProcess);
                } catch (\Exception $e) {
                    $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                    $this->getLogEntry()->addResultMessage($e->getMessage());
                }
            }
        }
        return $this->getFiles();
    }

    /**
     * Called before every import
     */
    protected function beforeImport()
    {
        $this->setBeginTime(time());
        #$memBefore = memory_get_usage();
        #$timeBefore = time();
        #echo "Before import: " . $memBefore . " bytes / Time: " . $timeBefore . "<br>";
        $logEntry = $this->logFactory->create();
        $logEntry->setCreatedAt(time());
        $logEntry->setProfileId($this->getProfile()->getId());
        $logEntry->setSourceIds($this->getProfile()->getSourceIds());
        $logEntry->setImportType($this->getImportType());
        $logEntry->setRecordsImported(0);
        $logEntry->setResultMessage(__('Import started...'));
        $logEntry->save();
        if ($this->getImportType() == self::IMPORT_TYPE_MANUAL
            || $this->getImportType() == self::IMPORT_TYPE_TEST
        ) {
            $logEntry->setLogDebugMessages(true);
        }
        $this->setLogEntry($logEntry);
        $this->_registry->unregister('trackingimport_log');
        $this->_registry->unregister('trackingimport_profile');
        $this->_registry->register('trackingimport_log', $logEntry);
        $this->_registry->register('trackingimport_profile', $this->getProfile());
    }

    /**
     * Called after every import
     */
    protected function afterImport()
    {
        $this->saveLog();
        $this->_registry->unregister('trackingimport_profile');
        #echo "After import: " . memory_get_usage() . " (Difference: " . round((memory_get_usage() - $memBefore) / 1024 / 1024, 2) . " MB, " . (time() - $timeBefore) . " Secs<br>";
        return $this;
    }

    /**
     * Save import log
     */
    protected function saveLog()
    {
        $this->getProfile()->saveLastExecutionNow();
        if (is_array($this->getFiles())) {
            $importedFiles = [];
            foreach ($this->getFiles() as $fileInfo) {
                $importedFiles[] = $fileInfo['filename'];
            }
            $this->getLogEntry()->setFiles(implode("|", $importedFiles));
        }
        if ($this->getTestMode()) {
            $resultMessage = 'Test mode: %1 %2 updates would have been imported in %3 seconds.';
        } else {
            $resultMessage = 'Import of %1 %2 updates finished in %3 seconds.';
        }
        $this->getLogEntry()->setResult(
            $this->getLogEntry()->getResult() ? $this->getLogEntry()->getResult() : Log::RESULT_SUCCESSFUL
        );
        $this->getLogEntry()->setResultMessage(
            $this->getLogEntry()->getResultMessages() ? $this->getLogEntry()->getResultMessages() : __(
                $resultMessage,
                $this->getLogEntry()->getRecordsImported(),
                $this->getProfile()->getEntity(),
                (time() - $this->getBeginTime())
            )
        );
        $this->getLogEntry()->save();
        $this->errorEmailNotification();
    }

    /**
     * On exception, send error email to debug email set in configuration
     * @return $this
     */
    protected function errorEmailNotification()
    {
        if (!$this->moduleHelper->isDebugEnabled() || $this->moduleHelper->getDebugEmail() == '') {
            return $this;
        }
        if ($this->getLogEntry()->getResult() >= Log::RESULT_WARNING) {
            try {
                /** @var \Magento\Framework\Mail\Message $message */
                $message = $this->objectManager->create('Magento\Framework\Mail\MessageInterface');
                $message->setFrom('store@' . $this->request->getServer('SERVER_NAME'), $this->request->getServer('SERVER_NAME'));
                foreach (explode(",", $this->moduleHelper->getDebugEmail()) as $emailAddress) {
                    $emailAddress = trim($emailAddress);
                    $message->addTo($emailAddress, $emailAddress);
                }
                $message->setSubject('Magento Tracking Import Module @ ' . $this->request->getServer('SERVER_NAME'));
                $message->setBody('Warning/Error/Message(s): ' . $this->getLogEntry()->getResultMessages());
                $message->send($this->objectManager->create('\Magento\Framework\Mail\TransportInterfaceFactory')->create(['message' => clone $message]));
            } catch (\Exception $e) {
                $this->getLogEntry()->addResultMessage('Exception: ' . $e->getMessage());
                $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                $this->getLogEntry()->setResultMessage($this->getLogEntry()->getResultMessages());
                $this->getLogEntry()->save();
            }
        }
        return $this;
    }

    /**
     * Check module status
     * @throws LocalizedException
     */
    protected function checkStatus()
    {
        if (!$this->moduleHelper->confirmEnabled(true)) {
            throw new LocalizedException(
                __(
                    str_rot13(
                        'Gur Genpxvat Ahzore Vzcbeg Zbqhyr vf abg ranoyrq. Cyrnfr znxr fher lbh\'er hfvat n inyvq yvprafr xrl naq gung gur zbqhyr unf orra ranoyrq ng Flfgrz > KGRAGB Rkgrafvbaf > Genpxvat Vzcbeg pbasvthengvba.'
                    )
                )
            );
        }
    }
}