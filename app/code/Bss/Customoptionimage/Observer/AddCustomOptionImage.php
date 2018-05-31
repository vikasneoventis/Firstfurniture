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
namespace Bss\Customoptionimage\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use Bss\Customoptionimage\Ui\DataProvider\Product\Form\Modifier\BssCustomOptions;

class AddCustomOptionImage implements ObserverInterface
{
    public $imageSaving;

    public $moduleConfig;

    public $version;

    public function __construct(
        \Bss\Customoptionimage\Helper\ImageSaving $imageSaving,
        \Bss\Customoptionimage\Helper\ModuleConfig $moduleConfig,
        \Magento\Framework\App\ProductMetadataInterface $version
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->imageSaving = $imageSaving;
        $this->version = $version;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->imageSaving->saveImage(
            $this->moduleConfig->isModuleEnable(),
            $observer->getData('controller')->getRequest()->getPost('product'),
            $observer->getData('product')->getEntityId()
        );
    }
}
