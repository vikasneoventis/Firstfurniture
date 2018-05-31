<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 *
 */
abstract class AbstractFeeds extends \Wyomind\DataFeedManager\Controller\Adminhtml\AbstractAction
{

    public $title = "Data Feed Manager > Data Feeds";
    public $breadcrumbOne = "Data Feed Manager";
    public $breadcrumbTwo = "Manage Data Feeds";
    public $model = "Wyomind\DataFeedManager\Model\Feeds";
    public $errorDoesntExist = "This data feed doesn't exist anymore.";
    public $successDelete = "The data feed has been deleted.";
    public $msgModify = "Modify data feed";
    public $msgNew = "New data feed";
    public $registryName = "data_feed";
    public $menu = "feeds";
}
