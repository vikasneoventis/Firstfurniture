<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer;

class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_storeManager = null;
    protected $_dataHelper = null;
    protected $_list = null;
    protected $_io = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Wyomind\DataFeedManager\Helper\Data $dataHelper
     * @param \Magento\Framework\App\Filesystem\DirectoryList $list
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Wyomind\DataFeedManager\Helper\Data $dataHelper,
        \Magento\Framework\App\Filesystem\DirectoryList $list,
        \Magento\Framework\Filesystem\Io\File $io,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_list = $list;
        $this->_io = $io;
    }

    /**
     * Renders grid column
     * @param \Magento\Framework\DataObject $row
     * @return type
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $fileName = preg_replace('/^\//', '', $row->getPath() . ($row->getPath() == "/" ? "" : "/") . $this->_dataHelper->getFinalFilename($row->getDateformat(), $row->getName(), $row->getUpdatedAt())) . $this->_dataHelper->getExtFromType($row->getType());
        $this->_storeManager->setCurrentStore($row->getStoreId());
        $baseurl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, false);
        $url = $baseurl . $fileName;
        $url = preg_replace('/([^\:])\/\//', '$1/', $url);
        $url = str_replace('/pub/', '/', $url);
        $rootdir = $this->_list->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        if ($this->_io->fileExists($rootdir . '/' . $fileName)) {
            return '<a href="' . $url . '?r=' . time() . '" target="_blank">' . $url . '</a>';
        } else {
            return $url;
        }
    }
}
