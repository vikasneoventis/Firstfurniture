<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Load library action
 */
class Librarysample extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Does the menu is allowed
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_DataFeedManager::main');
    }

    /**
     * Execute action
     */
    public function execute()
    {

        $code = $this->getRequest()->getParam('code');
        $storeId = $this->getRequest()->getParam('store_id');

        // attribute type
        $attribute = $this->attributeRepository->get(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $code);
        $attributeType = $attribute->getFrontendInput();

        // sample products

        $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
        if ($code != "category_ids") {
            $filterGroup = $this->_objectManager->create('\Magento\Framework\Api\Search\FilterGroup');
            $filterNotNull = $this->_objectManager->create('\Magento\Framework\Api\Filter');
            $filterNotNull->setField($code);
            $filterNotNull->setConditionType('notnull');
            $filterNotNull->setValue(true);
            $filterNEq = $this->_objectManager->create('\Magento\Framework\Api\Filter');
            $filterNEq->setField($code);
            $filterNEq->setConditionType('neq');
            $filterNEq->setValue("");
            $filterGroup->setFilters([$filterNotNull,$filterNEq]);
            $searchCriteria->setFilterGroups([$filterGroup]);
        }

        $searchCriteria->setPageSize(5);
        $searchCriteria->setCurrentPage(1);

        $collection = $this->productRepository->getList($searchCriteria);
        
        // method to retrieve the attibute value
        $method = "get" . $this->coreHelper->camelize($code);

        
        
        
        // possible values of the attribute
        $attributeLabels = $this->productAttributeRepository->getItems($code);
        $attributesLabelsList = [];
        foreach ($attributeLabels as $attributeLabel) {
            $attributesLabelsList[$attributeLabel['value']][$storeId] = $attributeLabel['label'];
        }

        $data = [];

        // select / multiselect
        if ($code != "attribute_code" && ($attributeType == "select" || $attributeType == "multiselect")) {
            // for each product
            foreach ($collection->getItems() as $product) {
                // get the attribute values
                $values = explode(',', $product->$method());
                // if more than one value
                $vals = [];
                // foreach value
                foreach ($values as $v) {
                    // get the frontend label
                    if (isset($attributesLabelsList[$v][$storeId])) {
                        $vals[] = $attributesLabelsList[$v][$storeId];
                    } elseif (isset($attributesLabelsList[$v][0])) {
                        $vals[] = $attributesLabelsList[$v][0];
                    }
                }
                $val = implode(', ', $vals);
                $val = $this->removeInvalidChar($val);
                $data[] = ['name' => $product->getName(), 'sku' => $product->getSku(), 'attribute' => $val];
            }
            // all other types
        } else {
            // for each product
            foreach ($collection->getItems() as $product) {
                $val = $product->$method();
                if ($code == "category_ids") {
                    $val = implode(',', $product->$method());
                }
                $val = $this->removeInvalidChar($val);
                $data[] = ['name' => $product->getName(), 'sku' => $product->getSku(), 'attribute' => $val];
            }
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($data));
    }

    /**
     * Remove invalid chars
     * @param string $val
     * @return string
     */
    protected function removeInvalidChar($val)
    {
        $val = preg_replace(
            '/' .
            '[\x00-\x1F\x7F]' .
            '|[\x00-\x7F][\x80-\xBF]+' .
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|' .
            '(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})' .
            '/S',
            ' ',
            $val
        );
        $val = str_replace('&#153;', '', $val);
        return $val;
    }
}
