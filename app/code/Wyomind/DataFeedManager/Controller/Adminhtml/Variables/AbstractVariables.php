<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Variables;

abstract class AbstractVariables extends \Wyomind\DataFeedManager\Controller\Adminhtml\AbstractAction
{

    public $title = "Data Feed Manager > Custom Variables";
    public $breadcrumbOne = "Data Feed Manager";
    public $breadcrumbTwo = "Manage Custom Variables";
    public $model = "Wyomind\DataFeedManager\Model\Variables";
    public $errorDoesntExist = "This variable no longer exists.";
    public $successDelete = "The variable has been deleted.";
    public $msgModify = "Modify custom variable";
    public $msgNew = "New custom variable";
    public $registryName = "variables";
    public $menu = "variables";
}
