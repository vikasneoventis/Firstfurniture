<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2017-04-27T20:05:10+00:00
 * File:          app/code/Xtento/TrackingImport/Helper/Tools.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Helper;

class Tools extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Xtento\TrackingImport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\TrackingImport\Model\SourceFactory
     */
    protected $sourceFactory;

    /**
     * Tools constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Xtento\TrackingImport\Model\ProfileFactory $profileFactory
     * @param \Xtento\TrackingImport\Model\SourceFactory $sourceFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Xtento\TrackingImport\Model\ProfileFactory $profileFactory,
        \Xtento\TrackingImport\Model\SourceFactory $sourceFactory
    ) {
        parent::__construct($context);
        $this->profileFactory = $profileFactory;
        $this->sourceFactory = $sourceFactory;
    }

    /**
     * @param $profileIds
     * @param $sourceIds
     *
     * @return string
     */
    public function exportSettingsAsJson($profileIds, $sourceIds)
    {
        $randIdPrefix = rand(100000, 999999);
        $exportData = [];
        $exportData['profiles'] = [];
        $exportData['sources'] = [];
        foreach ($profileIds as $profileId) {
            $profile = $this->profileFactory->create()->load($profileId);
            if ($profile->getId()) {
                $profile->unsetData('profile_id');
                $profileSourceIds = $profile->getData('source_ids');
                $newSourceIds = [];
                foreach (explode("&", $profileSourceIds) as $sourceId) {
                    if (is_numeric($sourceId)) {
                        $newSourceIds[] = substr($randIdPrefix . $sourceId, 0, 8);
                    }
                }
                $profile->setData('new_source_ids', implode("&", $newSourceIds));
                $exportData['profiles'][] = $profile->toArray();
            }
        }
        foreach ($sourceIds as $sourceId) {
            $source = $this->sourceFactory->create()->load($sourceId);
            if ($source->getId()) {
                $source->setData('new_source_id', substr($randIdPrefix . $sourceId, 0, 8));
                #$source->unsetData('source_id');
                $source->unsetData('password');
                $exportData['sources'][] = $source->toArray();
            }
        }
        return \Zend_Json::encode($exportData);
    }

    /**
     * @param $jsonData
     * @param array $addedCounter
     * @param array $updatedCounter
     * @param bool $updateByName
     * @param string $errorMessage
     *
     * @return bool
     */
    public function importSettingsFromJson($jsonData, &$addedCounter = [], &$updatedCounter = [], $updateByName = true, &$errorMessage = "")
    {
        try {
            $settingsArray = \Zend_Json::decode($jsonData);
        } catch (\Exception $e) {
            $errorMessage = __('Import failed. Decoding of JSON import format failed.');
            return false;
        }
        // Remapped source IDs
        $remappedSourceIds = [];
        // Process sources
        if (isset($settingsArray['sources'])) {
            foreach ($settingsArray['sources'] as $sourceData) {
                if ($updateByName) {
                    $sourceCollection = $this->sourceFactory->create()->getCollection()
                        ->addFieldToFilter('type', $sourceData['type'])
                        ->addFieldToFilter('name', $sourceData['name']);
                    if ($sourceCollection->getSize() === 1) {
                        $remappedSourceIds[$sourceData['new_source_id']] = $sourceCollection->getFirstItem()->getId();
                        unset($sourceData['new_source_id']);
                        $this->sourceFactory->create()->setData($sourceData)->setId(
                            $sourceCollection->getFirstItem()->getId()
                        )->save();
                        $updatedCounter['sources']++;
                    } else {
                        $newSource = $this->sourceFactory->create()->setData($sourceData);
                        if (isset($sourceData['new_source_id'])) {
                            $newSource->setId($sourceData['new_source_id']);
                            unset($sourceData['new_source_id']);
                            $newSource->saveWithId();
                        } else {
                            unset($sourceData['new_source_id']);
                            $newSource->save();
                        }
                        $addedCounter['sources']++;
                    }
                } else {
                    $newSource = $this->sourceFactory->create()->setData($sourceData);
                    if (isset($sourceData['new_source_id'])) {
                        $newSource->setId($sourceData['new_source_id']);
                        unset($sourceData['new_source_id']);
                        $newSource->saveWithId();
                    } else {
                        unset($sourceData['new_source_id']);
                        $newSource->save();
                    }
                    $addedCounter['sources']++;
                }
            }
        }
        // Process profiles
        if (isset($settingsArray['profiles'])) {
            foreach ($settingsArray['profiles'] as $profileData) {
                if ($updateByName) {
                    $profileCollection = $this->profileFactory->create()->getCollection()
                        ->addFieldToFilter('entity', $profileData['entity'])
                        ->addFieldToFilter('name', $profileData['name']);
                    if (isset($profileData['new_source_ids'])) {
                        $newSourceIds = explode("&", $profileData['new_source_ids']);
                        $tempSourceIds = [];
                        foreach ($newSourceIds as $newSourceId) {
                            if (isset($remappedSourceIds[$newSourceId])) {
                                $newSourceId = $remappedSourceIds[$newSourceId];
                            }
                            $tempSourceIds[] = $newSourceId;
                        }
                        $profileData['source_ids'] = implode("&", $newSourceIds);
                        unset($profileData['new_source_ids']);
                    }
                    if ($profileCollection->getSize() === 1) {
                        $this->profileFactory->create()->setData($profileData)->setId($profileCollection->getFirstItem()->getId())->save();
                        $updatedCounter['profiles']++;
                    } else {
                        $this->profileFactory->create()->setData($profileData)->save();
                        $addedCounter['profiles']++;
                    }
                } else {
                    if (isset($profileData['new_source_ids'])) {
                        $profileData['source_ids'] = $profileData['new_source_ids'];
                        unset($profileData['new_source_ids']);
                    }
                    $this->profileFactory->create()->setData($profileData)->save();
                    $addedCounter['profiles']++;
                }
            }
        }
        return true;
    }
}
