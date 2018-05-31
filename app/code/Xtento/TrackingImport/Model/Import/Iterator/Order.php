<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-08T14:38:31+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Iterator/Order.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Iterator;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Xtento\TrackingImport\Model\Log;
use Xtento\TrackingImport\Model\Processor\Mapping\ActionFactory;

class Order extends AbstractIterator
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ActionFactory
     */
    protected $mappingActionFactory;

    /**
     * Order constructor.
     *
     * @param Registry $frameworkRegistry
     * @param ObjectManagerInterface $objectManager
     * @param ActionFactory $mappingActionFactory
     * @param array $data
     */
    public function __construct(
        Registry $frameworkRegistry,
        ObjectManagerInterface $objectManager,
        ActionFactory $mappingActionFactory,
        array $data = []
    ) {
        $this->registry = $frameworkRegistry;
        $this->objectManager = $objectManager;
        $this->mappingActionFactory = $mappingActionFactory;

        parent::__construct($data);
    }

    public function processUpdates($updatesInFilesToProcess)
    {
        $logEntry = $this->registry->registry('trackingimport_log');
        $profileConfiguration = $this->getProfile()->getConfiguration();

        $totalRecordCount = 0;
        $updatedRecordCount = 0;

        $importModel = $this->objectManager->create(
            'Xtento\TrackingImport\Model\Import\Entity\\' . ucfirst($this->getProfile()->getEntity())
        );
        $importModel->setImportType($this->getImportType());
        $importModel->setTestMode($this->getTestMode());
        $importModel->setProfile($this->getProfile());

        // Get actions to apply
        $actionMapping = $this->mappingActionFactory->create();
        $actionMapping->setMappingData(isset($profileConfiguration['action']) ? $profileConfiguration['action'] : []);
        $importModel->setActionFields($actionMapping->getMappingFields());
        $importModel->setActions($actionMapping->getMapping());

        if (!$importModel->prepareImport($updatesInFilesToProcess)) {
            $logEntry->setResult(Log::RESULT_WARNING);
            $logEntry->addResultMessage(
                __(
                    "Files have been parsed, however, the prepareImport function complains that there were problems preparing the import data. Stopping import. Make sure your import processor is set up right."
                )
            );
            return false; // No updates to import.
        }

        foreach ($updatesInFilesToProcess as $updateFile) {
            $path = (isset($updateFile['FILE_INFORMATION']['path'])) ? $updateFile['FILE_INFORMATION']['path'] : '';
            $filename = $updateFile['FILE_INFORMATION']['filename'];
            $sourceId = $updateFile['FILE_INFORMATION']['source_id'];

            #ini_set('xdebug.var_display_max_depth', 10);
            #Zend_Debug::dump($updateFile);
            #die();
            $updatesToProcess = $updateFile['ROWS'];

            foreach ($updatesToProcess as $rowIdentifier => $updateData) {
                $totalRecordCount++;
                try {
                    if (empty($rowIdentifier)) {
                        continue;
                    }
                    if (isset($updateData['SKIP_FLAG']) && $updateData['SKIP_FLAG'] === true) {
                        $logEntry->addDebugMessage(
                            __(
                                "Row with identifier '%1' was skipped because of 'skip' field configuration XML set up in profile.",
                                str_replace('_SKIP', '', $rowIdentifier)
                            )
                        );
                        continue;
                    }

                    $importResult = $importModel->process($rowIdentifier, $updateData);

                    if (!$importResult || isset($importResult['error'])) {
                        $logEntry->addDebugMessage(
                            __("Notice: %1 | File '%2'", $importResult['error'], $path . $filename)
                        );
                        continue;
                    } else {
                        if (isset($importResult['changed']) && $importResult['changed']) {
                            $updatedRecordCount++;
                        }
                        if (isset($importResult['debug'])) {
                            $logEntry->addDebugMessage(
                                sprintf("%s", $importResult['debug'])
                            ); // | File '" . $path . $filename . "'", $importResult['debug']));
                        }
                    }
                } catch (\Exception $e) {
                    // Don't break execution, but log the error.
                    $logEntry->addDebugMessage(
                        __("Exception catched for row with ID '%1' specified in '%2' from source ID '%3':\n%4"),
                        $rowIdentifier,
                        $path . $filename,
                        $sourceId,
                        $e->getMessage()
                    );
                    continue;
                }
            }
        }

        $importModel->afterRun();

        $importResult = ['total_record_count' => $totalRecordCount, 'updated_record_count' => $updatedRecordCount];
        return $importResult;
    }
}