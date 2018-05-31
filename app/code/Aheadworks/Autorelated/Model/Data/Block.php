<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Data;

use Aheadworks\Autorelated\Api\Data\BlockInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Block data model
 *
 * @codeCoverageIgnore
 */
class Block extends AbstractExtensibleObject implements BlockInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRule()
    {
        return $this->_get(self::RULE);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIds()
    {
        return $this->_get(self::PRODUCT_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setRule($rule)
    {
        return $this->setData(self::RULE, $rule);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductIds($productIds)
    {
        return $this->setData(self::PRODUCT_IDS, $productIds);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Aheadworks\Autorelated\Api\Data\BlockExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
