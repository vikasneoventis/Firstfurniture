<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Source;

/**
 * Class ProductConditionType
 *
 * @package Aheadworks\Autorelated\Model\Source
 */
class ProductConditionType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**#@+
     * Product condition types
     */
    const CONDITIONS_COMBINATION = 1;
    const WHO_BOUGHT_THIS_ALSO_BOUGHT = 2;
    const WHO_VIEWED_THIS_ALSO_VIEWED = 3;
    const DEFAULT_TYPE = self::CONDITIONS_COMBINATION;
    /**#@-*/

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CONDITIONS_COMBINATION, 'label' => __('Conditions Combination')],
            ['value' => self::WHO_BOUGHT_THIS_ALSO_BOUGHT, 'label' => __('Who Bought This Also Bought')],
            ['value' => self::WHO_VIEWED_THIS_ALSO_VIEWED, 'label' => __('Who Viewed This Also Viewed')],
        ];
    }

    /**
     * Retrieve label for specified product condition type
     *
     * @param $productConditionType
     * @return string
     */
    public function getProductConditionTypeLabel($productConditionType)
    {
        $productConditionTypeLabels = $this->getLabelArray();
        if (!array_key_exists($productConditionType, $productConditionTypeLabels)) {
            return '';
        }
        return $productConditionTypeLabels[$productConditionType];
    }

    /**
     * Retrieve array of labels for all available product condition types
     *
     * @return array
     */
    public function getLabelArray()
    {
        return [
            self::CONDITIONS_COMBINATION => __('Conditions'),
            self::WHO_BOUGHT_THIS_ALSO_BOUGHT => __('WBTAB'),
            self::WHO_VIEWED_THIS_ALSO_VIEWED => __('WVTAV'),
        ];
    }
}
