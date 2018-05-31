<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-12T11:23:27+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Source/Ftp.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Source;

use Magento\Framework\DataObject;
use Xtento\TrackingImport\Model\Log;

class Ftp extends AbstractClass
{
    const TYPE_FTP = 'ftp';
    const TYPE_FTPS = 'ftps';

    /*
     * Download files from a FTP server
     */
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

        if ($this->getSource()->getFtpType() == self::TYPE_FTPS) {
            if (function_exists('ftp_ssl_connect')) {
                $this->connection = @ftp_ssl_connect(
                    $this->getSource()->getHostname(),
                    $this->getSource()->getPort(),
                    $this->getSource()->getTimeout()
                );
            } else {
                $this->getTestResult()->setSuccess(false)->setMessage(
                    __('No FTP-SSL functions found. Please compile PHP with SSL support.')
                );
                return false;
            }
        } else {
            if (function_exists('ftp_connect')) {
                $this->connection = @ftp_connect(
                    $this->getSource()->getHostname(),
                    $this->getSource()->getPort(),
                    $this->getSource()->getTimeout()
                );
            } else {
                $this->getTestResult()->setSuccess(false)->setMessage(
                    __('No FTP functions found. Please compile PHP with FTP support.')
                );
                return false;
            }
        }

        if (!$this->connection) {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __(
                    'Could not connect to FTP server. Please make sure that there is no firewall blocking the outgoing connection to the FTP server and that the timeout is set to a high enough value. If this error keeps occurring, please get in touch with your server hoster / server administrator AND with the server hoster / server administrator of the remote FTP server. A firewall is probably blocking ingoing/outgoing FTP connections.'
                )
            );
            return false;
        }

        if (!@ftp_login(
            $this->connection,
            $this->getSource()->getUsername(),
            $this->encryptor->decrypt($this->getSource()->getPassword())
        )
        ) {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __('Could not log into FTP server. Wrong username or password.')
            );
            return false;
        }

        if ($this->getSource()->getFtpPasv()) {
            // Enable passive mode
            @ftp_pasv($this->connection, true);
            # if (...) {$this->getTestResult()->setSuccess(false)->setMessage(__('Could not enable passive mode for FTP connection.'));
            #$this->getSource()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
            #return false;}
        }

        if (!@ftp_chdir($this->connection, $this->getSource()->getPath())) {
            $this->getTestResult()->setSuccess(false)->setMessage(
                __(
                    'Could not change directory on FTP server to import directory. Please make sure the directory exists (base path must be exactly the same) and that we have rights to read in the directory.'
                )
            );
            return false;
        }

        $this->getTestResult()->setSuccess(true)->setMessage(__('Connection with FTP server tested successfully.'));
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

        $filelist = ftp_nlist($this->connection, "");
        /* Alternative code for some broken FTP servers: */
        /*
        $filelist = ftp_rawlist($this->connection, "");
        $results = [];
        foreach ($filelist as $line) {
            $name = array_pop(explode(" ", $line));
            if ($name == '.' || $name == '..') continue;
            $results[] = $name;
        }
        $filelist = $results;
        */
        if (!$filelist) {
            $logEntry->setResult(Log::RESULT_WARNING);
            $logEntry->addResultMessage(
                __(
                    'Source "%1" (ID: %2): Could not get file listing for import directory. You should try enabling Passive Mode in the modules FTP configuration.',
                    $this->getSource()->getName(),
                    $this->getSource()->getId()
                )
            );
            return false;
        }
        foreach ($filelist as $filename) {
            if (!preg_match($this->getSource()->getFilenamePattern(), $filename)) {
                continue;
            }
            if (@ftp_chdir($this->connection, $filename)) {
                // This is a directory.. do not try to download it.
                ftp_chdir($this->connection, '..');
                continue;
            }
            $tempHandle = fopen('php://temp', 'r+');
            if (@ftp_fget($this->connection, $tempHandle, $filename, FTP_BINARY, 0)) {
                rewind($tempHandle);
                $buffer = '';
                while (!feof($tempHandle)) {
                    $buffer .= fgets($tempHandle, 4096);
                }
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
                        false,
                        false
                    );
                }
            } else {
                $logEntry->setResult(Log::RESULT_WARNING);
                $logEntry->addResultMessage(
                    __(
                        'Source "%1" (ID: %2): Could not download file "%3" from FTP server. Please make sure we\'ve got rights to read the file. You can also try enabling Passive Mode in the configuration section of the extension.',
                        $this->getSource()->getName(),
                        $this->getSource()->getId(),
                        $filename
                    )
                );
            }
        }

        // Close FTP connection
        ftp_close($this->connection);

        return $filesToProcess;
    }

    public function archiveFiles($filesToProcess, $forceDelete = false, $chDir = true, $closeConnection = true)
    {
        $logEntry = $this->_registry->registry('trackingimport_log');

        // Reconnect to archive files
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

        if ($this->connection) {
            if ($forceDelete) {
                foreach ($filesToProcess as $file) {
                    if ($file['source_id'] !== $this->getSource()->getId()) {
                        continue;
                    }
                    if (!@ftp_delete($this->connection, $file['path'] . '/' . $file['filename'])) {
                        $logEntry->setResult(Log::RESULT_WARNING);
                        $logEntry->addResultMessage(
                            __(
                                'Source "%1" (ID: %2): Could not delete file "%3%4" from the FTP import directory after processing it. Please make sure the directory exists and that we have rights to read/write in the directory. Also, make sure the import path is an absolute path, i.e. that it begins with a slash (/).',
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
                        if (!@ftp_chdir($this->connection, $this->getSource()->getArchivePath())) {
                            $logEntry->setResult(Log::RESULT_WARNING);
                            $logEntry->addResultMessage(
                                __(
                                    'Source "%1" (ID: %2): Could not change directory on FTP server to archive directory. Please make sure the directory exists and that we have rights to read/write in the directory. Also, make sure the archive path is an absolute path, i.e. that it begins with a slash (/).',
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
                        if (!@ftp_rename(
                            $this->connection,
                            $file['path'] . '/' . $file['filename'],
                            $file['filename']
                        )
                        ) {
                            $logEntry->setResult(Log::RESULT_WARNING);
                            $logEntry->addResultMessage(
                                __(
                                    'Source "%1" (ID: %2): Could not move file "%3%4" to the FTP archive directory located at "%5". Please make sure the directory exists and that we have rights to read/write in the directory. Also, make sure the archive path is an absolute path, i.e. that it begins with a slash (/).',
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
                            if (!@ftp_delete($this->connection, $file['path'] . '/' . $file['filename'])) {
                                $logEntry->setResult(Log::RESULT_WARNING);
                                $logEntry->addResultMessage(
                                    __(
                                        'Source "%1" (ID: %2): Could not delete file "%3%4" from the FTP import directory after processing it. Please make sure the directory exists and that we have rights to read/write in the directory. Also, make sure the import path is an absolute path, i.e. that it begins with a slash (/).',
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
            if ($closeConnection) {
                @ftp_close($this->connection);
            }
        }
    }
}