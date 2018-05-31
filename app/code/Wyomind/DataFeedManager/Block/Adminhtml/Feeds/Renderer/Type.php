<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer;

class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_dataHelper = null;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Wyomind\DataFeedManager\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
    }
    
    /**
     * Renders grid column
     * @param \Magento\Framework\DataObject $row
     * @return type
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->_dataHelper->getExtFromType($row->getType());
    }
}
