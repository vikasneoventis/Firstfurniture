<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Functions;

/**
 * Backend form container block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected $_coreRegistry = null;

    /**
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Wyomind_DataFeedManager';
        $this->_controller = 'adminhtml_functions';

        parent::_construct();

        $this->removeButton('reset');
        $this->removeButton('save');
        $this->addButton(
            'save',
            [
            'label' => __('Save'),
            'class' => 'save',
            'onclick' => "jQuery('#edit_form').submit();",
                ]
        );
    }
}
