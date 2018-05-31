<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Autorelated\Api\Data\RuleSearchResultsInterface;
use Aheadworks\Autorelated\Api\Data\RuleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Autorelated rule CRUD interface
 *
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * Save rule
     *
     * @param RuleInterface $rule
     * @return RuleInterface
     * @throws LocalizedException
     */
    public function save(RuleInterface $rule);

    /**
     * Retrieve rule
     *
     * @param int $ruleId
     * @return RuleInterface
     * @throws NoSuchEntityException
     */
    public function get($ruleId);

    /**
     * Retrieve rules matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RuleSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rule
     *
     * @param RuleInterface $rule
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RuleInterface $rule);

    /**
     * Delete rule by ID
     *
     * @param int $ruleId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($ruleId);
}
