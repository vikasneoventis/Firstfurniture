<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header;

use Aheadworks\Autorelated\Model\Source;
use Magento\Backend\Block\Template\Context;

/**
 * Class ProductConditionType
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header
 */
class ProductConditionType extends DefaultRenderer
{
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Autorelated::listing/column/header/product_condition_type.phtml';

    /**
     * @var Source\Type
     */
    private $ruleTypeSource;

    /**
     * @param Context $context
     * @param Source\Type $ruleTypeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Source\Type $ruleTypeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ruleTypeSource = $ruleTypeSource;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->toHtml();
    }

    /**
     * Retrieve header name
     *
     * @return string
     */
    public function getHeaderName()
    {
        return $this->getColumn()->getHeader();
    }

    /**
     * Retrieve header tooltip text
     *
     * @return string
     */
    public function getHeaderTooltipText()
    {
        return $this->ruleTypeSource->getProductConditionTypeTooltip($this->getListingType());
    }
}
