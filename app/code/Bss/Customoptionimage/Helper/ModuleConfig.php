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

use Magento\Catalog\Model\Product\Option;

class ModuleConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $scopeConfig;

    public $storeManager;

    public $storeId;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/Customoptionimage/Enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
    public function getCheckboxSizeX()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Checkbox_x',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 50 : (int)$size;
    }
    public function getCheckboxSizeY()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Checkbox_y',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 50 : (int)$size;
    }
    public function getRadioSizeX()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Radio_x',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 50 : (int)$size;
    }
    public function getRadioSizeY()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Radio_y',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 50 : (int)$size;
    }
    public function getMultipleSizeX()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Multiple_x',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 40 : (int)$size;
    }
    public function getMultipleSizeY()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Multiple_y',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 40 : (int)$size;
    }
    public function getDropdownSizeX()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Dropdown_x',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 60 : (int)$size;
    }
    public function getDropdownSizeY()
    {
        $size = $this->scopeConfig->getValue(
            'Bss_Commerce/image_size/Dropdown_y',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($size === null) ? 60 : (int)$size;
    }
    public function getDropdownView()
    {
        $config = $this->scopeConfig->getValue(
            'Bss_Commerce/frontend_view/Dropdown',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($config === null) ? 0 : (int)$config;
    }
    public function getMultipleSelectView()
    {
        $config = $this->scopeConfig->getValue(
            'Bss_Commerce/frontend_view/Multiple',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return ($config === null) ? 0 : (int)$config;
    }

    public function getImageY($type)
    {
        switch ($type) {
            case Option::OPTION_TYPE_DROP_DOWN:
                return $this->getDropdownSizeY();
            
            case Option::OPTION_TYPE_MULTIPLE:
                return $this->getMultipleSizeY();
            
            case Option::OPTION_TYPE_RADIO:
                return $this->getRadioSizeY();
            
            case Option::OPTION_TYPE_CHECKBOX:
                return $this->getCheckboxSizeY();
        }
    }

    public function getImageX($type)
    {
        switch ($type) {
            case Option::OPTION_TYPE_DROP_DOWN:
                return $this->getDropdownSizeX();

            case Option::OPTION_TYPE_MULTIPLE:
                return $this->getMultipleSizeX();
            
            case Option::OPTION_TYPE_RADIO:
                return $this->getRadioSizeX();
            
            case Option::OPTION_TYPE_CHECKBOX:
                return $this->getCheckboxSizeX();
        }
    }

    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    public function isCoapInstalled()
    {
        return $this->_moduleManager->isEnabled('Bss_CustomOptionAbsolutePriceQuantity');
    }

    /**
     * @return string
     */
    public function getTooltipMessage()
    {
        return $this->scopeConfig->getValue(
            'coapnqty_config/tooltip/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param string $priceType
     * @return bool
     */
    public function isEnableTooltip($priceType = 'abs')
    {
        $result = $this->scopeConfig->getValue(
            'coapnqty_config/tooltip/enabled_tooltip',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $priceType === 'abs' && $result;
    }
}
