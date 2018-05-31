<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml;

class Feeds extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_feeds';
        $this->_blockGroup = 'Wyomind_DataFeedManager';
        $this->_headerText = __('Manage Data feeds');

        $this->_addButtonLabel = __('Create New Data Feed');


        $this->addButton(
                "import", [
            "label" => __("Import a data feed"),
            "class" => "add",
            "onclick" => "require(['dfm_index'], function (index) { index.importDataFeedModal(); });"
                ]
        );

        parent::_construct();
    }

}
