<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

class Import extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    public function execute()
    {
        $this->_uploader = new \Magento\Framework\File\Uploader("datafeed");
        if ($this->_uploader->getFileExtension() != "dfm") {
            $this->messageManager->addError(__("Wrong file type (") . $this->_uploader->getFileExtension() . __(").<br>Choose a dfm file."));
        } else {

            $rootDir = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
            $this->_uploader->save($rootDir . "/var/tmp", "import-datafeedmanager.csv");
            // récuperer le contenu
            $file = new \Magento\Framework\Filesystem\Driver\File;
            $dfm = new \Magento\Framework\File\Csv($file);
            $data = $dfm->getData($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());

            if ($this->coreHelper->getStoreConfig("datafeedmanager/system/trans_domain_export")) {
                $key = "dfm-empty-key";
            } else {
                $key = $this->coreHelper->getStoreConfig("datafeedmanager/license/activation_code");
            }
            $template = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($data[0][0]), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
            if ($this->dfmModel->load(0)->getResource()->importDataFeed($template)) {
                $this->messageManager->addSuccess(__("The data feed has been imported."));
            } else {
                $this->messageManager->addError(__("An error occured when importing the data feed."));
            }
            $file->deleteFile($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());
        }

        $result = $this->_resultRedirectFactory->create()->setPath("datafeedmanager/feeds/index");
        return $result;
    }

}
