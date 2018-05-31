<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Rule\Viewed;

use Aheadworks\Autorelated\Model\Rule\CurrentPageObject;
use Aheadworks\Autorelated\Api\Data\RuleInterface;
use Aheadworks\Autorelated\Model\Source\Type;

/**
 * Class Validator
 *
 * @package Aheadworks\Autorelated\Model\Rule\Viewed
 */
class Validator
{
    /**
     * @var CurrentPageObject
     */
    private $currentPageObject;

    /**
     * @param CurrentPageObject $currentPageObject
     */
    public function __construct(
        CurrentPageObject $currentPageObject
    ) {
        $this->currentPageObject = $currentPageObject;
    }

    /**
     * Is show ARP block
     *
     * @param RuleInterface $rule
     * @param int $blockType
     * @return bool
     */
    public function canShow($rule, $blockType)
    {
        if ($rule->getType() == Type::CUSTOM_BLOCK_TYPE) {
            return true;
        }

        if ($rule->getType() == Type::CATEGORY_BLOCK_TYPE) {
            return $this->canShowOnCategoryPage($rule, $blockType);
        }

        return $this->canShowOnProductAndCartPage($rule, $blockType);
    }

    /**
     * Can show ARP block on the category page
     *
     * @param RuleInterface $rule
     * @param int $blockType
     * @return bool
     */
    private function canShowOnCategoryPage($rule, $blockType)
    {
        $currentCategoryId = $this->currentPageObject->getCurrentCategoryIdForBlock($rule, $blockType);
        if ($currentCategoryId
            && (!$rule->getCategoryIds() || in_array($currentCategoryId, explode(',', $rule->getCategoryIds())))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Can show ARP block on the product and cart page
     *
     * @param RuleInterface $rule
     * @param int $blockType
     * @return bool
     */
    private function canShowOnProductAndCartPage($rule, $blockType)
    {
        $currentProductId = $this->currentPageObject->getCurrentProductIdForBlock($rule, $blockType);
        if (!$currentProductId) {
            return false;
        }

        $conditions = $rule->getViewedProductRule()->getConditions();
        if (isset($conditions)) {
            $match = $rule->getViewedProductRule()->getMatchingProductIds();
            if (in_array($currentProductId, $match)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
}
