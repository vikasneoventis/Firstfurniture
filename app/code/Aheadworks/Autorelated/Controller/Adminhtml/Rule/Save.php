<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Controller\Adminhtml\Rule;

use Aheadworks\Autorelated\Api\Data\RuleInterface;
use Aheadworks\Autorelated\Api\Data\RuleInterfaceFactory;
use Aheadworks\Autorelated\Api\RuleRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Aheadworks\Autorelated\Model\Converter\Condition as ConditionConverter;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Autorelated\Model\Rule\TypeResolver;
use Aheadworks\Autorelated\Model\Rule\Viewed\ProductFactory as ViewedProductFactory;
use Aheadworks\Autorelated\Model\Rule\Related\ProductFactory as RelatedProductFactory;
use Aheadworks\Autorelated\Model\Rule\Related\CategoryProductFactory as RelatedCategoryProductFactory;

/**
 * Class Save
 *
 * @package Aheadworks\Autorelated\Controller\Adminhtml\Rule
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RuleInterfaceFactory
     */
    private $ruleDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @var TypeResolver
     */
    private $ruleTypeResolver;

    /**
     * @var ViewedProductFactory
     */
    private $viewedProductFactory;

    /**
     * @var RelatedProductFactory
     */
    private $relatedProductFactory;

    /**
     * @var RelatedCategoryProductFactory
     */
    private $relatedCategoryProductFactory;

    /**
     * @param Context $context
     * @param RuleRepositoryInterface $ruleRepository
     * @param RuleInterfaceFactory $ruleDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param ConditionConverter $conditionConverter
     * @param TypeResolver $ruleTypeResolver
     * @param ViewedProductFactory $viewedProductFactory
     * @param RelatedProductFactory $relatedProductFactory
     * @param RelatedCategoryProductFactory $relatedCategoryProductFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        RuleRepositoryInterface $ruleRepository,
        RuleInterfaceFactory $ruleDataFactory,
        DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        ConditionConverter $conditionConverter,
        TypeResolver $ruleTypeResolver,
        ViewedProductFactory $viewedProductFactory,
        RelatedProductFactory $relatedProductFactory,
        RelatedCategoryProductFactory $relatedCategoryProductFactory
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->ruleDataFactory = $ruleDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
        $this->conditionConverter = $conditionConverter;
        $this->ruleTypeResolver = $ruleTypeResolver;
        $this->viewedProductFactory = $viewedProductFactory;
        $this->relatedProductFactory = $relatedProductFactory;
        $this->relatedCategoryProductFactory = $relatedCategoryProductFactory;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Autorelated::rule';

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->prepareData($data);
            $id = $data['id'];

            try {
                $ruleDataObject = ($this->isRuleAlreadyExist($data))
                    ? $this->ruleRepository->get($id)
                    : $this->ruleDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $ruleDataObject,
                    $data,
                    RuleInterface::class
                );
                if (!$ruleDataObject->getId()) {
                    $ruleDataObject->setId(null);
                }

                $rule = $this->ruleRepository->save($ruleDataObject);

                $this->dataPersistor->clear('aw_autorelated_rule');
                $this->messageManager->addSuccessMessage(__('Rule was successfully saved'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $rule->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the rule'));
            }
            $this->dataPersistor->set('aw_autorelated_rule', $data);
            if ($this->isRuleAlreadyExist($data)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Checks if current rule already exist
     *
     * @param array $data
     * @return bool
     */
    private function isRuleAlreadyExist(array $data)
    {
        return (isset($data['id']) && (!empty($data['id'])));
    }

    /**
     * Prepare data after save
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareData(array $data)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            $data['store_ids'] = [$this->storeManager->getStore(true)->getId()];
        }

        if ($this->ruleTypeResolver->isRulePositionUseCategoryRelatedProductCondition($data['position'])) {
            $data['viewed_condition'] = '';
        } else {
            $viewedCondition = $this->prepareViewedConditionData($data);
            if (!empty($viewedCondition)) {
                $data['viewed_condition'] = $viewedCondition;
            }
            $data['category_ids'] = '';
        }

        $productCondition = $this->prepareProductConditionData($data);
        if (!empty($productCondition)) {
            $data['product_condition'] = $productCondition;
        }

        if (!isset($data['category_ids'])) {
            $data['category_ids'] = '';
        }

        unset($data['rule']);

        return $data;
    }

    /**
     * Prepare viewed condition data
     *
     * @param array $data
     * @return \Aheadworks\Autorelated\Api\Data\ConditionInterface|string
     */
    private function prepareViewedConditionData(array $data)
    {
        $viewedConditionData = '';

        $conditionArray = [];
        if (isset($data['rule']['viewed'])) {
            $conditionArray = $this->convertFlatToRecursive($data['rule'], ['viewed']);
        } elseif ($this->isNeedToSetDefaultViewedCondition($data)) {
            $viewedProductRule = $this->viewedProductFactory->create();
            $defaultConditions = [];
            $defaultConditions['viewed'] = [];
            $defaultConditions['viewed']['viewed_conditions'] = $viewedProductRule
                ->setConditions([])
                ->getConditions()
                ->asArray()
            ;
            $conditionArray = $this->convertFlatToRecursive($defaultConditions, ['viewed']);
        }

        if (isset($conditionArray['viewed'])
            && is_array($conditionArray['viewed']['viewed_conditions'])
        ) {
            $viewedConditionData = $this->conditionConverter
                ->arrayToDataModel($conditionArray['viewed']['viewed_conditions']);
        }

        return $viewedConditionData;
    }

    /**
     * Checks if need to set default viewed condition
     *
     * @param array $data
     * @return bool
     */
    private function isNeedToSetDefaultViewedCondition(array $data)
    {
        return !($this->isRuleAlreadyExist($data)) || empty($data['viewed_condition']);
    }

    /**
     * Prepare product condition data
     *
     * @param array $data
     * @return \Aheadworks\Autorelated\Api\Data\ConditionInterface|string
     */
    private function prepareProductConditionData(array $data)
    {
        $productConditionData = '';

        $relatedRuleType = 'related';
        if ($this->ruleTypeResolver->isRulePositionUseCategoryRelatedProductCondition($data['position'])) {
            $relatedRuleType = 'category_related';
        }

        $conditionArray = [];
        if (isset($data['rule'][$relatedRuleType])) {
            $conditionArray = $this->convertFlatToRecursive($data['rule'], [$relatedRuleType]);
        } elseif ($this->isNeedToSetDefaultProductCondition($data)) {
            if ($this->ruleTypeResolver->isRulePositionUseCategoryRelatedProductCondition($data['position'])) {
                $defaultProductRule = $this->relatedCategoryProductFactory->create();
            } else {
                $defaultProductRule = $this->relatedProductFactory->create();
            }
            $defaultConditions = [];
            $defaultConditions[$relatedRuleType] = [];
            $defaultConditions[$relatedRuleType][$relatedRuleType . '_conditions'] = $defaultProductRule
                ->setConditions([])
                ->getConditions()
                ->asArray()
            ;
            $conditionArray = $this->convertFlatToRecursive($defaultConditions, [$relatedRuleType]);
        }

        if (isset($conditionArray[$relatedRuleType])
            && is_array($conditionArray[$relatedRuleType][$relatedRuleType . '_conditions'])
        ) {
            $productConditionData = $this->conditionConverter
                ->arrayToDataModel($conditionArray[$relatedRuleType][$relatedRuleType . '_conditions']);
        }

        return $productConditionData;
    }

    /**
     * Checks if need to set default product condition
     *
     * @param array $data
     * @return bool
     */
    private function isNeedToSetDefaultProductCondition(array $data)
    {
        return !($this->isRuleAlreadyExist($data)) || empty($data['product_condition']);
    }

    /**
     * Get conditions data recursively
     *
     * @param array $data
     * @param array $allowedKeys
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function convertFlatToRecursive(array $data, $allowedKeys = [])
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $result;

                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        // Fix for magento UI form, if empty value in array exists
                        if (is_array($v)) {
                            foreach ($v as $dk => $dv) {
                                if (empty($dv)) {
                                    unset($v[$dk]);
                                }
                            }
                            if (!count($v)) {
                                continue;
                            }
                        }

                        $node[$k] = $v;
                    }
                }
            }
        }

        return $result;
    }
}
