<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 *
 * @package Aheadworks\Autorelated\Model\Source
 */
class Type implements ArrayInterface
{
    /**#@+
     * Listing type
     */
    const PRODUCT_BLOCK_TYPE = 1;
    const CART_BLOCK_TYPE = 2;
    const CATEGORY_BLOCK_TYPE = 3;
    const CUSTOM_BLOCK_TYPE = 4;
    /**#@-*/

    /**
     * Return listing type
     *
     * @return array
     */
    public function toOptionArray()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                'type' => self::PRODUCT_BLOCK_TYPE,
                'code' => 'product',
                'title' => __('Related Product rules'),
                'description' => __('Create blocks with related products to be displayed at product pages. Displaying directly at product pages allows making highly specified suggestions and ensure a better click-through rate.'),
            ],
            [
                'type' => self::CART_BLOCK_TYPE,
                'code' => 'cart',
                'title' => __('Shopping Cart rules'),
                'description' => __('Create blocks with related products to be displayed at shopping cart. Displaying at shopping cart is an additional window of opportunity to increase order size by promoting complementary items.'),
            ],
            [
                'type' => self::CATEGORY_BLOCK_TYPE,
                'code' => 'cart',
                'title' => __('Category rules'),
                'description' => __('Create blocks with related products to be displayed at category pages. Displaying at category pages serves to promote entire groups of related products.'),
            ],
            [
                'type' => self::CUSTOM_BLOCK_TYPE,
                'code' => 'custom',
                'title' => __('Custom position rules'),
                'description' => __('Create blocks with related products to be displayed in custom positions at any pages using Widgets. Displaying blocks in custom positions allows promoting products to the customers wherever they are at'),
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Retrieve tooltip text for product condition type column header of rule grid form
     *
     * @param int $type
     * @return \Magento\Framework\Phrase
     */
    public function getProductConditionTypeTooltip($type)
    {
        $tooltipText = __("This column shows the mode the related products are displayed in:<br>"
            . "- <b>Conditions</b> - based on specified conditions;<br>"
            . "- <b>Who Bought This Also Bought</b> (WBTAB) - based on purchase history;<br>"
            . "- <b>Who Viewed This Also Viewed</b> (WVTAV) - based on views history;");
        if ($type === self::CUSTOM_BLOCK_TYPE || $type === self::CATEGORY_BLOCK_TYPE) {
            $tooltipText = __("<b>Conditions Combination</b> mode only is available for this rule type");
        }
        return $tooltipText;
    }
}
