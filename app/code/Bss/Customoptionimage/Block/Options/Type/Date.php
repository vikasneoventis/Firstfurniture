<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Customoptionimage
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Customoptionimage\Block\Options\Type;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product\Option\Type\Date as OptionDateModel;
use Bss\Customoptionimage\Helper\ModuleConfig;

class Date extends \Magento\Catalog\Block\Product\View\Options\Type\Date
{
    /**
     * @var ModuleConfig
     */
    private $dataObjectFactory;

    /**
     * Date constructor.
     * @param TemplateContext $context
     * @param PricingHelper $pricingHelper
     * @param CatalogHelper $catalogData
     * @param OptionDateModel $catalogProductOptionTypeDate
     * @param ModuleConfig $moduleConfig
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        PricingHelper $pricingHelper,
        CatalogHelper $catalogData,
        OptionDateModel $catalogProductOptionTypeDate,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $pricingHelper, $catalogData, $catalogProductOptionTypeDate, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Bss_Customoptionimage::date.phtml');
    }

    public function getBssCustomOptionBlock($place)
    {
        $childObject = $this->dataObjectFactory->create();

        $this->_eventManager->dispatch(
            'bss_custom_options_render_file_' . $place,
            ['child' => $childObject]
        );
        $blocks = $childObject->getData() ?: [];
        $output = '';

        foreach ($blocks as $childBlock) {
            $block = $this->getLayout()->createBlock($childBlock);
            $block->setProduct($this->getProduct())->setOption($this->getOption());
            $output .= $block->toHtml();
        }
        return $output;
    }
}
