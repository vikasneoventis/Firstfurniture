<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Controller\Feeds;

/**
 * Generate action
 */
class Generate extends \Wyomind\DataFeedManager\Controller\Feeds
{

    /**
     * Execute action
     */
    public function execute()
    {
        // http://www.example.com/index.php/datafeedmanager/feeds/generate/id/{data_id}/ak/{YOUR_ACTIVATION_KEY}

        $id = $this->getRequest()->getParam('id');
        $ak = $this->getRequest()->getParam('ak');

        $activationKey = $this->_scopeConfig->getValue("datafeedmanager/license/activation_key", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $resultRaw = $this->_resultRawFactory->create();
        if ($activationKey == $ak) {
            $datafeedmanager = $this->_objectManager->create('Wyomind\DataFeedManager\Model\Feeds');
            $datafeedmanager->setId($id);
            if ($datafeedmanager->load($id)) {
                try {
                    $datafeedmanager->generateFile($this->getRequest());
                    return $resultRaw->setContents(sprintf(__("The data feed ")."\"".$datafeedmanager->getName()."\"".__(" has been generated.")));
                } catch (\Exception $e) {
                    return $resultRaw->setContents($e->getMessage());
                }
            } else {
                return $resultRaw->setContents(__('Unable to find a data feed to generate.'));
            }
        } else {
            return $resultRaw->setContents(__('Invalid activation key'));
        }
    }
}
