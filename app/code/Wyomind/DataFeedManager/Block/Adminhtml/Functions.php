<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml;

/**
 * Prepare the functions admin page
 */
class Functions extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_functions';
        $this->_blockGroup = 'Wyomind_DataFeedManager';
        $this->_headerText = __('Manage custom functions');

        parent::_construct();
    }
}
