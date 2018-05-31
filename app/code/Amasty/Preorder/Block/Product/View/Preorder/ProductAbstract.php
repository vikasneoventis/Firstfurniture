<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Block\Product\View\Preorder;

class ProductAbstract extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * ProductAbstract constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils   $arrayUtils
     * @param \Amasty\Preorder\Helper\Data           $helper
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Amasty\Preorder\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $arrayUtils, $data);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->helper->preordersEnabled();
    }

    /**
     * @return string
     */
    public function getCartLabel()
    {
        return $this->escapeQuote($this->helper->getProductPreorderCartLabel($this->getProduct()));
    }

    /**
     * @return string
     */
    public function getPreorderNote()
    {
        return $this->helper->getProductPreorderNote($this->getProduct());
    }
}
