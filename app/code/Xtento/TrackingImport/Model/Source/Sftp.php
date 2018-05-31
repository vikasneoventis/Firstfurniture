<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-07-29T14:51:27+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Source/Sftp.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Source;

use Magento\Framework\DataObject;
use Xtento\TrackingImport\Model\Log;

class Sftp extends AbstractClass
{
    public function testConnection()
    {
        $this->initConnection();
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setSource($this->sourceFactory->create()->load($this->getSource()->getId()));
        $testResult = new DataObject();
        $this->setTestResult($testResult);

        if (class_exists('\Net_SFTP')) { // Magento 2.0
            $this->connection = new \Net_SFTP($this->getSource()->getHostname(), $this->getSource()->getPort(), $this->getSource()->getTimeout());
        } elseif (class_exists('\phpseclib\Net\SFTP')) { // Magento 2.1
            $this->connection = new \phpseclib\Net\SFTP($this->getSource()->getHostname(), $this->getSource()->getPort(), $this->getSource()->getTimeout());
        } else {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __('No SFTP functions found. The Net_SFTP class is missing.')
            );
            return false;
        }

        if (!$this->connection) {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __(
                    'Could not connect to SFTP server. Please make sure that there is no firewall blocking the outgoing connection to the SFTP server and that the timeout is set to a high enough value. If this error keeps occurring, please get in touch with your server hoster / server administrator AND with the server hoster / server administrator of the remote SFTP server. A firewall is probably blocking ingoing/outgoing SFTP connections.'
                )
            );
            return false;
        }

        // Pub/Private key support - make sure to use adjust the loadKey function with the right key format: http://phpseclib.sourceforge.net/documentation/misc_crypt.html WARNING: Magentos version of phpseclib actually only implements CRYPT_RSA_PRIVATE_FORMAT_PKCS1.
        /*$pk = new Crypt_RSA();
        $pk->setPassword($this->getData('password'));
        #$private_key = file_get_contents('c:\\TEMP\\keys\\coreftp_rsa_no_pw.privkey'); // Or load the private key from a file
        $private_key = <<<KEY
-----BEGIN DSA PRIVATE KEY-----
....
-----END DSA PRIVATE KEY-----
KEY;

        if ($pk->loadKey($private_key, CRYPT_RSA_PRIVATE_FORMAT_PKCS1) === false) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Could not load private key supplied. Make sure it is the PKCS1 format (openSSH) and that the supplied password is right.'));
            return false;
        }*/

        if (!@$this->connection->login(
            $this->getSource()->getUsername(),
            $this->encryptor->decrypt($this->getSource()->getPassword())
        )
        ) {
            #if (!@$this->connection->login($this->getSource()->getUsername(), $pk)) { // If using pubkey authentication
            $this->getTestResult()->setSuccess(false)->setMessage(
                __(
                    'Connection to SFTP server failed (make sure no firewall is blocking the connection). This error could also be caused by a wrong login for the SFTP server.'
                )
            );
            return false;
        }

        if (!@$this->connection->chdir($this->getSource()->getPath())) {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __(
                    'Could not change directory on SFTP server to import directory. Please make sure the directory exists and that we have rights to read in the directory.'
                )
            );
            return false;
        }

        $this->getTestResult()->setSuccess(true)->setMessage(__('Connection with SFTP server tested successfully.'));
        $this->getSource()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage(
            $this->getTestResult()->getMessage()
        )->save();
        return true;
    }

    public function loadFiles()
    {
        $filesToProcess = [];

        $logEntry = $this->_registry->registry('trackingimport_log');
        // Test connection
        $testResult = $this->testConnection();
        if (!$testResult->getSuccess()) {
            $logEntry->setResult(Log::RESULT_WARNING);
            $logEntry->addResultMessage(
                __(
                    'Source "%1" (ID: %2): %3',
                    $this->getSource()->getName(),
                    $this->getSource()->getId(),
                    $testResult->getMessage()
                )
            );
            return false;
        }

        $filelist = @$this->connection->rawlist();
        foreach ($filelist as $filename => $fileinfo) {
            if (!preg_match($this->getSource()->getFilenamePattern(), $filename)) {
                continue;
            }
            if (!isset($fileinfo['size'])) {
                continue; // This is a directory.
            }
            $fs_filename = $filename;
            if ($buffer = @$this->connection->get($fs_filename)) {
                if (!empty($buffer)) {
                    $filesToProcess[] = [
                        'source_id' => $this->getSource()->getId(),
                        'path' => $this->getSource()->getPath(),
                        'filename' => $filename,
                        'data' => $buffer
                    ];
                } else {
                    $this->archiveFiles(
                        [
                            [
                                'source_id' => $this->getSource()->getId(),
                                'path' => $this->getSource()->getPath(),
                                'filename' => $filename
                            ]
                        ],
                        false,
                        false
                    );
                }
            } else {
                $logEntry->setResult(Log::RESULT_WARNING);
                $logEntry->addResultMessage(
                    __(
                        'Source "%1" (ID: %2): Could not download file "%3" from SFTP server. Please make sure we\'ve got rights to read the file.',
                        $this->getSource()->getName(),
                        $this->getSource()->getId(),
                        $filename
                    )
                );
            }
        }

        return $filesToProcess;
    }

    public function archiveFiles($filesToProcess, $forceDelete = false, $chDir = true)
    {
        $logEntry = $this->_registry->registry('trackingimport_log');

        if ($this->connection) {
            if ($forceDelete) {
                foreach ($filesToProcess as $file) {
                    if ($file['source_id'] !== $this->getSource()->getId()) {
                        continue;
                    }
                    if (!@$this->connection->delete($file['path'] . '/' . $file['filename'])) {
                        $logEntry->setResult(Log::RESULT_WARNING);
                        $logEntry->addResultMessage(
                            __(
                                'Source "%1" (ID: %2): Could not delete file "%3%4" from the SFTP import directory after processing it. Please make sure the directory exists and that we have rights to read/write in the directory.',
                                $this->getSource()->getName(),
                                $this->getSource()->getId(),
                                $file['path'],
                                $file['filename']
                            )
                        );
                    }
                }
            } else {
                if ($this->getSource()->getArchivePath() !== "") {
                    if ($chDir) {
                        if (!@$this->connection->chdir($this->getSource()->getArchivePath())) {
                            $logEntry->setResult(Log::RESULT_WARNING);
                            $logEntry->addResultMessage(
                                __(
                                    'Source "%1" (ID: %2): Could not change directory on SFTP server to archive directory. Please make sure the directory exists and that we have rights to read/write in the directory.',
                                    $this->getSource()->getName(),
                                    $this->getSource()->getId()
                                )
                            );
                            return false;
                        }
                    }
                    foreach ($filesToProcess as $file) {
                        if ($file['source_id'] !== $this->getSource()->getId()) {
                            continue;
                        }
                        if (!@$this->connection->rename(
                            $file['path'] . '/' . $file['filename'],
                            $this->getSource()->getArchivePath() . '/' . $file['filename']
                        )
                        ) {
                            $logEntry->setResult(Log::RESULT_WARNING);
                            $logEntry->addResultMessage(
                                __(
                                    'Source "%1" (ID: %2): Could not move file "%3%4" to the SFTP archive directory located at "%5". Please make sure the directory exists and that we have rights to read/write in the directory.',
                                    $this->getSource()->getName(),
                                    $this->getSource()->getId(),
                                    $file['path'],
                                    $file['filename'],
                                    $this->getSource()->getArchivePath()
                                )
                            );
                        }
                    }
                } else {
                    if ($this->getSource()->getDeleteImportedFiles() == true) {
                        foreach ($filesToProcess as $file) {
                            if ($file['source_id'] !== $this->getSource()->getId()) {
                                continue;
                            }
                            if (!@$this->connection->delete($file['path'] . '/' . $file['filename'])) {
                                $logEntry->setResult(Log::RESULT_WARNING);
                                $logEntry->addResultMessage(
                                    __(
                                        'Source "%1" (ID: %2): Could not delete file "%3%4" from the SFTP import directory after processing it. Please make sure the directory exists and that we have rights to read/write in the directory.',
                                        $this->getSource()->getName(),
                                        $this->getSource()->getId(),
                                        $file['path'],
                                        $file['filename']
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}