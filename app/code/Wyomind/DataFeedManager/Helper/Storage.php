<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Helper;

class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_ioFtp = null;
    protected $_ioSftp = null;
    protected $_directoryList = null;
    protected $_messageManager = null;
    protected $_directoryRead = null;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Io\Ftp $ioFtp,
        \Magento\Framework\Filesystem\Io\Sftp $ioSftp,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->_ioFtp = $ioFtp;
        $this->_ioSftp = $ioSftp;
        $this->_messageManager = $messageManager;
        $this->_directoryList = $directoryList;
        $this->_directoryRead = $directoryRead->create($this->getAbsoluteRootDir());
        parent::__construct($context);
    }

    public function ftpUpload(
        $useSftp,
        $ftpPassive,
        $ftpHost,
        $ftpLogin,
        $ftpPassword,
        $ftpDir,
        $path,
        $file,
        $ftpPort = null
    ) {
        
        if ($useSftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }

        $rtn = false;
        try {
            $host = str_replace(["ftp://","ftps://"], "", $ftpHost);
            if ($useSftp && $ftpPort != null) {
                $host .= ":".$ftpPort;
            }
            $ftp->open(
                [
                        'host' => $host,
                        'port' => $ftpPort, // only ftp
                        'user' => $ftpLogin,
                        'username' => $ftpLogin, // only sftp
                        'password' => $ftpPassword,
                        'timeout' => '120',
                        'path' => $ftpDir,
                        'passive' => $ftpPassive // only ftp
                    ]
            );
            
            if ($useSftp) {
                $ftp->cd($ftpDir);
            }
            
            if (!$useSftp && $ftp->write($file, $this->getAbsoluteRootDir() . $path . $file)) {
                $this->_messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                $rtn = true;
            } elseif ($useSftp && $ftp->write($file, $this->getAbsoluteRootDir() . $path . $file)) {
                $this->_messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                $rtn = true;
            } else {
                $this->_messageManager->addError(sprintf(__("Unable to upload '%s'on %s"), $file, $ftpHost) . ".");
                $rtn = false;
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError(__("Ftp upload error : ") . $e->getMessage());
        }
        $ftp->close();
        return $rtn;
    }
    
    public function getAbsoluteRootDir()
    {
        return $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
    }
}
