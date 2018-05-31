<?php

/* *
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    protected $_coreHelper = null;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Wyomind\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
    
        parent::__construct($context, $jsonEncoder, $data);
        $this->_coreHelper = $coreHelper;
    }

    public function render(\Magento\Framework\DataObject $row)
    {

        $actions = [
            [// Edit
                'caption' => __('Edit'),
                'url' => [
                    'base' => '*/*/edit'
                ],
                'field' => 'id'
            ],
            [// Generate
                'caption' => __('Generate'),
                'url' => "javascript:void(require(['dfm_index'], function (index) { index.generate('" . $this->getUrl('datafeedmanager/feeds/generate', ['id' => $row->getId()]) . "'); }))"
            ],
            [// Preview
                'caption' => __('Preview (%1 items)', $this->_coreHelper->getDefaultConfig("datafeedmanager/system/preview")),
                'url' => [
                    'base' => '*/*/preview'
                ],
                'field' => 'id',
                'popup' => true
            ],
            [// Delete
                'caption' => __('Delete'),
                'url' => "javascript:void(require(['dfm_index'], function (index) { index.delete('" . $this->getUrl('datafeedmanager/feeds/delete', ['id' => $row->getId()]) . "'); }))"
            ]
        ];
        

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
