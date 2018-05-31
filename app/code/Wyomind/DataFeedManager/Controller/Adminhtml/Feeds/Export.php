<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

class Export extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    public function execute()
    {
        $feed = $this->dfmModel;
        $feed->load($this->getRequest()->getParam('id'));

        foreach ($feed->getData() as $field => $value) {
            $fields[] = $field;
            if ($field == "id") {
                $values[] = "NULL";
            } else {
                $values[] = "'" . str_replace(["'", "\\"], ["''", "\\\\"], $value) . "'";
            }
        }
        $sql = "INSERT INTO {{datafeedmanager_feeds}}(" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ");";
        
        if ($this->coreHelper->getStoreConfig("datafeedmanager/system/trans_domain_export")) {
            $key = "dfm-empty-key";
        } else {
            $key = $this->coreHelper->getStoreConfig("datafeedmanager/license/activation_code");
        }

        $content = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $sql, MCRYPT_MODE_CBC, md5(md5($key))));
     
        return $this->coreHelper->sendUploadResponse($feed->getName() . ".dfm", $content);
    }
}
