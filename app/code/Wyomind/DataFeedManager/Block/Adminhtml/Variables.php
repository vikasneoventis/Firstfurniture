<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml;

/**
 * Prepare the Variables admin page
 */
class Variables extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_variables';
        $this->_blockGroup = 'Wyomind_DataFeedManager';
        $this->_headerText = __('Manage custom variables');

        parent::_construct();
    }
}
