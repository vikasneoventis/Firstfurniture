<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-11T12:58:55+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Profile/Edit/Tab/Mapping/Action.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Profile\Edit\Tab\Mapping;

class Action extends AbstractMapping
{
    public $mappingId = 'action';
    public $mappingModel = 'Xtento\TrackingImport\Model\Processor\Mapping\Action';
    public $fieldLabel = 'Action';
    public $valueFieldLabel = 'Value';
    public $hasDefaultValueColumn = true;
    public $hasValueColumn = false;
    public $defaultValueFieldLabel = 'Value';
    public $addFieldLabel = 'Add new action';
    public $addAllFieldLabel = 'Add all actions';
    public $selectLabel = '--- Select action ---';
}
