<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2017-03-22T12:17:45+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Profile.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model;

class Profile extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var Import\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * @var Import\Condition\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Xtento\TrackingImport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var \Xtento\XtCore\Helper\Cron
     */
    protected $cronHelper;

    /**
     * Profile constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Import\Condition\CombineFactory $combineFactory
     * @param Import\Condition\ActionFactory $actionFactory
     * @param \Xtento\TrackingImport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SourceFactory $sourceFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Xtento\TrackingImport\Model\Import\Condition\CombineFactory $combineFactory,
        \Xtento\TrackingImport\Model\Import\Condition\ActionFactory $actionFactory,
        \Xtento\TrackingImport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Xtento\TrackingImport\Model\SourceFactory $sourceFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionFactory = $actionFactory;
        $this->moduleHelper = $moduleHelper;
        $this->cronHelper = $cronHelper;
        $this->request = $request;
        $this->sourceFactory = $sourceFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Xtento\TrackingImport\Model\ResourceModel\Profile');
        $this->_collectionName = 'Xtento\TrackingImport\Model\ResourceModel\Profile\Collection';
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        $this->_registry->register('trackingimport_profile', $this, true);
        return $this->combineFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionFactory->create();
    }

    public function getSources()
    {
        $sourceIds = array_filter(explode("&", $this->getData('source_ids')));
        $sources = [];
        foreach ($sourceIds as $sourceId) {
            if (!is_numeric($sourceId)) {
                continue;
            }
            $source = $this->sourceFactory->create()->load($sourceId);
            if ($source->getId()) {
                $sources[] = $source;
            }
        }
        // Return sources
        return $sources;
    }

    public function beforeSave()
    {
        // Only call the "rule" model parents _beforeSave function if the profile is modified in the backend, as otherwise the "conditions" ("import filters") could be lost
        if ($this->request->getModuleName() == 'xtento_trackingimport' && $this->request->getControllerName(
            ) == 'profile'
        ) {
            parent::beforeSave();
        } else {
            if (!$this->getId()) {
                $this->isObjectNew(true);
            }
        }
        return $this;
    }

    public function afterSave()
    {
        parent::afterSave();
        if ($this->request->getModuleName() == 'xtento_trackingimport' && ($this->request->getControllerName() == 'profile' || $this->request->getControllerName() == 'tools')) {
            $this->updateCronjobs();
        }
        if ($this->_registry->registry('xtento_trackingimport_update_cronjobs_after_profile_save') !== null) {
            // Can be registered by third party developers, so after they call ->save() on a profile, it will update the profiles cronjobs, added in version 2.1.5
            $this->updateCronjobs();
        }
        return $this;
    }

    protected function _afterLoad() {
        // Fix renamed "Object" condition class to "ObjectCondition" in import conditions, changed in version 2.0.9
        $this->setConditionsSerialized(str_replace('s:51:"Xtento\TrackingImport\Model\Import\Condition\Object"', 's:60:"Xtento\TrackingImport\Model\Import\Condition\ObjectCondition"', $this->getConditionsSerialized()));
        return parent::_afterLoad();
    }

    /**
     * Update database via cron helper
     */
    protected function updateCronjobs()
    {
        // Remove existing cronjobs
        $this->cronHelper->removeCronjobsLike('trackingimport_profile_' . $this->getId() . '_%');

        if (!$this->getEnabled()) {
            return $this; // Profile not enabled
        }
        if (!$this->getCronjobEnabled()) {
            return $this; // Cronjob not enabled
        }

        $cronRunModel = 'Xtento\TrackingImport\Cron\Import::execute';
        if ($this->getCronjobFrequency(
            ) == \Xtento\TrackingImport\Model\System\Config\Source\Cron\Frequency::CRON_CUSTOM
            || ($this->getCronjobFrequency() == '' && $this->getCronjobCustomFrequency() !== '')
        ) {
            // Custom cron expression
            $cronFrequencies = $this->getCronjobCustomFrequency();
            if (empty($cronFrequencies)) {
                return $this;
            }
            $cronFrequencies = array_unique(explode(";", $cronFrequencies));
            $cronCounter = 0;
            foreach ($cronFrequencies as $cronFrequency) {
                $cronFrequency = trim($cronFrequency);
                if (empty($cronFrequency)) {
                    continue;
                }
                $cronCounter++;
                $cronIdentifier = 'trackingimport_profile_' . $this->getId() . '_cron_' . $cronCounter;
                $this->cronHelper->addCronjob(
                    $cronIdentifier,
                    $cronFrequency,
                    $cronRunModel
                );
            }
        } else {
            // No custom cron expression
            $cronFrequency = $this->getCronjobFrequency();
            if (empty($cronFrequency)) {
                return $this;
            }
            $cronIdentifier = 'trackingimport_profile_' . $this->getId() . '_cron';
            $this->cronHelper->addCronjob(
                $cronIdentifier,
                $cronFrequency,
                $cronRunModel
            );
        }

        return $this;
    }

    public function saveLastExecutionNow()
    {
        $write = $this->getResource()->getConnection();
        $write->update(
            $this->getResource()->getMainTable(),
            ['last_execution' => (new \DateTime)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)],
            ["`{$this->getResource()->getIdFieldName()}` = {$this->getId()}"]
        );
    }
}