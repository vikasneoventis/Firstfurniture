<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Api\Data;

use Aheadworks\Autorelated\Api\Data\RuleInterface;

/**
 * Autorelated block interface
 *
 * @api
 */
interface BlockInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RULE = 'rule';
    const PRODUCT_IDS = 'product_ids';
    /**#@-*/

    /**
     * Get rule
     *
     * @return RuleInterface|null
     */
    public function getRule();

    /**
     * Get product ids
     *
     * @return int[]|null
     */
    public function getProductIds();

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Autorelated\Api\Data\BlockExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set rule
     *
     * @param RuleInterface $rule
     * @return BlockInterface
     */
    public function setRule($rule);

    /**
     * Set product ids
     *
     * @param int $productIds
     * @return BlockInterface
     */
    public function setProductIds($productIds);

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Autorelated\Api\Data\BlockExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Autorelated\Api\Data\BlockExtensionInterface $extensionAttributes
    );
}
