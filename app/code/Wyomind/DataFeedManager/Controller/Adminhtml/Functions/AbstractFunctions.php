<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Functions;

/**
 * Delete action
 */
abstract class AbstractFunctions extends \Wyomind\DataFeedManager\Controller\Adminhtml\AbstractAction
{

    public $title = "Data Feed Manager > Custom Functions";
    public $breadcrumbOne = "Data Feed Manager";
    public $breadcrumbTwo = "Manage Custom Functions";
    public $model = "Wyomind\DataFeedManager\Model\Functions";
    public $errorDoesntExist = "This function doesn't exist anymore.";
    public $successDelete = "The function has been deleted.";
    public $msgModify = "Modify custom function";
    public $msgNew = "New custom function";
    public $registryName = "function";
    public $menu = "functions";
}
