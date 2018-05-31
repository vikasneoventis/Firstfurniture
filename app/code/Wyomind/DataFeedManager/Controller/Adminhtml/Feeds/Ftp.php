<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 *
 */
class Ftp extends \Magento\Backend\App\Action
{
    
    protected $_ioFtp = null;
    protected $_ioSftp = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\Io\Ftp $ioFtp,
        \Magento\Framework\Filesystem\Io\Sftp $ioSftp
    ) {
        $this->_ioFtp = $ioFtp;
        $this->_ioSftp = $ioSftp;
        parent::__construct($context);
    }

    /**
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_DataFeedManager::main');
    }

    /**
     *
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $host = $params['ftp_host'];
        $port = $params['ftp_port'];
        $login = $params['ftp_login'];
        $password = $params['ftp_password'];
        $sftp = $params['use_sftp'];
        $active = $params['ftp_active'];
        $dir = $params['ftp_dir'];


        if ($sftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }

        try {
            $ftp->open(
                [
                    'host' => $host,
                    'port' => $port,
                    'user' => $login, //ftp
                    'username' => $login, //sftp
                    'password' => $password,
                    'timeout' => '10',
                    'path' => $dir,
                    'passive' => !($active)
                ]
            );
            $ftp->close();
            $content = __("Connection succeeded");
        } catch (\Exception $e) {
            $content = __("Ftp error : ") . $e->getMessage();
        }


        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($content));
    }
}
