<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-11T16:20:56+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Action/AbstractAction.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Action;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Xtento\TrackingImport\Model\Processor\Mapping\Action\Configuration;

abstract class AbstractAction extends AbstractModel
{
    /**
     * @var Configuration
     */
    protected $actionConfiguration;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * AbstractAction constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Configuration $actionConfiguration
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configuration $actionConfiguration,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->actionConfiguration = $actionConfiguration;
        $this->registry = $registry;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected $debugMessages = [];
    protected $hasUpdatedObject = false;

    protected function getActionSettingByField($fieldName, $fieldToRetrieve)
    {
        if ($fieldToRetrieve == 'enabled' || $fieldToRetrieve == 'value') {
            $fieldToRetrieve = 'default_value'; // "Enabled" and "value" are synonyms and are both stored in the default_value field
        }
        $actions = $this->getActions();
        foreach ($actions as $actionId => $actionData) {
            if ($actionData['field'] == $fieldName) {
                if (isset($actionData[$fieldToRetrieve])) {
                    #var_dump($actionData[$fieldToRetrieve]); die();
                    if ($fieldToRetrieve == 'default_value') {
                        $manipulatedFieldValue = $this->actionConfiguration->setValueBasedOnFieldData(
                            $this->registry->registry('xtento_trackingimport_updatedata'),
                            $actionData['config']
                        );
                        if ($manipulatedFieldValue !== -99) {
                            $actionData['default_value'] = $manipulatedFieldValue;
                        }
                    }
                    return $actionData[$fieldToRetrieve];
                } else {
                    return "";
                }
            }
        }
        return false;
    }

    protected function getActionSettingByFieldBoolean($fieldName, $fieldToRetrieve)
    {
        return (bool)$this->getActionSettingByField($fieldName, $fieldToRetrieve);
    }

    protected function addDebugMessage($message)
    {
        array_push($this->debugMessages, $message);
        return $this;
    }

    public function getDebugMessages()
    {
        return (array)$this->debugMessages;
    }

    protected function setHasUpdatedObject($bool)
    {
        $this->hasUpdatedObject = $bool;
        return $this;
    }

    public function getHasUpdatedObject()
    {
        return (bool)$this->hasUpdatedObject;
    }

    protected function getProfile()
    {
        return $this->registry->registry('trackingimport_profile');
    }

    protected function getProfileConfiguration()
    {
        return new DataObject($this->getProfile()->getConfiguration());
    }
}