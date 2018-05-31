<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Mostviewed\Model\Config\Source;

class DataSource implements \Magento\Framework\Option\ArrayInterface
{
    const SOURCE_VIEWED = 0;
    const SOURCE_BOUGHT = 1;
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => self::SOURCE_VIEWED, 'label' => __('Viewed together')], ['value' => self::SOURCE_BOUGHT, 'label' => __('Bought together')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::SOURCE_VIEWED => __('Viewed together'), self::SOURCE_BOUGHT => __('Bought together')];
    }
}
