<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\Community;

class Related extends \Amasty\Mostviewed\Plugin\Community\AbstractProduct
{

    /**
     * @param $items
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection
     */
    public function afterGetItems($object, $items)
    {
        return $this->_prepareCollection(\Amasty\Mostviewed\Helper\Data::RELATED_PRODUCTS_CONFIG_NAMESPACE, $items);
    }
}
