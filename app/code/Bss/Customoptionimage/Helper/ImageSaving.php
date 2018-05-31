<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Customoptionimage
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Customoptionimage\Helper;

class ImageSaving
{
    public $uploader;

    public $filesystem;

    public $storeManager;

    public $fileDriver;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->storeManager = $storeManager;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
    }

    public function moveImage($value)
    {
        $baseUrl = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    );
        $mediaRootDir = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath();
        if ($value->getData('bss_image_button')) {
            $file = substr($value->getData('bss_image_button'), strlen($baseUrl));
        } elseif ($value->getData('image_url')) {
            $file = substr($value->getData('image_url'), strlen($baseUrl));
        } else {
            return '';
        }
        $fileNamePieces = explode('/', $file);
        $fileName = end($fileNamePieces);
        $mediaDirectory = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );
        $newPath = 'bss/coi/' . $value->getOptionId() . '/';
        
        $this->fileDriver->createDirectory($mediaDirectory->getAbsolutePath($newPath));
        $checkDuplicateName = $fileName;
        if ($file !== $newPath . $fileName) {
            $checkTime = 0;
            while ($this->fileDriver->isFile($mediaRootDir . $newPath . $checkDuplicateName)) {
                $checkDuplicateName = '(' . $checkTime . ')' . $fileName;
                $checkTime++;
            }
            $this->fileDriver->rename($mediaRootDir . $file, $mediaRootDir . $newPath . $checkDuplicateName);
        }
        return $baseUrl . $newPath . $checkDuplicateName;
    }
    public function cleanTempFile()
    {
        $mediaRootDir = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath();
        if ($this->fileDriver->isDirectory($mediaRootDir . 'bss/temp/')) {
            $this->fileDriver->deleteDirectory($mediaRootDir . 'bss/temp/');
        }
    }
    public function saveTemporaryImage($opOrder, $valueOrder)
    {
        try {
            $fieldName = 'temporary_image';
            $baseMediaPath = 'bss/temp/' . $opOrder . '_' . $valueOrder . '/';
            $uploader = $this->uploader->create(['fileId' => $fieldName ]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            );
            $result = $uploader->save($mediaDirectory->getAbsolutePath($baseMediaPath));

            $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
            $result['path'] = str_replace('\\', '/', $result['path']);
            $result['url'] = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . $this->getFilePath($baseMediaPath, $result['file']);
            $result['name'] = $result['file'];
            $data['bss_image'] = $baseMediaPath.$result['file'];
            return  $result['url'];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }
}
