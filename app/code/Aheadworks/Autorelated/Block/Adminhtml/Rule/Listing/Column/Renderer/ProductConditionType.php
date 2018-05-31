<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

use Magento\Framework\DataObject;
use Aheadworks\Autorelated\Model\Source;
use Magento\Backend\Block\Template\Context;

/**
 * Class ProductConditionType
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer
 */
class ProductConditionType extends AbstractRenderer
{
    /**
     * @var Source\ProductConditionType
     */
    private $productConditionTypeSource;

    /**
     * @param Context $context
     * @param Source\ProductConditionType $productConditionTypeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Source\ProductConditionType $productConditionTypeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productConditionTypeSource = $productConditionTypeSource;
    }

    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $position = $row->getProductConditionType();

        return $this->productConditionTypeSource->getProductConditionTypeLabel($position);
    }
}
