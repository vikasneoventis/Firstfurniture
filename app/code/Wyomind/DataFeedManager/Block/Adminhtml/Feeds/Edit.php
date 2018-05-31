<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Wyomind_DataFeedManager';
        $this->_controller = 'adminhtml_feeds';

        parent::_construct();

        $this->removeButton('save');
        $this->removeButton('reset');


        $this->updateButton('delete', 'label', __('Delete'));

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->addButton(
                'export',
                [
                    'label' => __('Export'),
                    'class' => 'add',
                    "onclick" => "setLocation('" . $this->getUrl('*/*/export', ['id' => $id]) . "')",
                ]
            );

            $this->addButton(
                'duplicate',
                [
                    'label' => __('Duplicate'),
                    'class' => 'add',
                    'onclick' => "jQuery('#id').remove(); jQuery('#back_i').val('1'); jQuery('#edit_form').submit();",
                ]
            );
            $this->addButton(
                'generate',
                [
                    'label' => __('Generate'),
                    'class' => 'save',
                    'onclick' => "require([\"dfm_configuration\"], function (configuration) {configuration.generate(); });",
                ]
            );
        }

        $this->addButton(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save',
                'onclick' => "if (jQuery('#categories').val() == '') jQuery('#categories').val('[]');jQuery('#back_i').val('1'); jQuery('#edit_form').submit();",
            ]
        );
    }
}
