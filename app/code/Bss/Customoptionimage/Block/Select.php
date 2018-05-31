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
namespace Bss\Customoptionimage\Block;

class Select extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    public $opData;

    public $moduleConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Bss\Customoptionimage\Helper\Data $opData,
        \Bss\Customoptionimage\Helper\ModuleConfig $moduleConfig,
        array $data = []
    ) {
        $this->opData = $opData;
        $this->moduleConfig = $moduleConfig;
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }
    public function getValuesHtml()
    {
        if ($this->moduleConfig->isModuleEnable()) {
            return $this->customValues();
        } else {
            return $this->defaultValues();
        }
    }
    private function customValues()
    {
        $_option = $this->getOption();
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN) {
            return $this->defaultDropdown();
        } elseif ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE) {
            return $this->defaultMultiselect();
        } elseif ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO) {
            return $this->customRadio();
        } else {
            return $this->customCheckbox();
        }
    }
    private function customCheckbox()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();

        $this->setSkipJsReloadPrice(1);

        $url = $this->getImgUrlList();
        $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
        $require = $_option->getIsRequire() ? ' required' : '';
        $arraySign = '';

        $type = 'checkbox';
        $class = 'checkbox admin__control-checkbox';
        $arraySign = '[]';

        $count = 1;
        foreach ($_option->getValues() as $_value) {
            $count++;

            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ]
            );

            $htmlValue = $_value->getOptionTypeId();
            $urlString = (($url != null) && array_key_exists($htmlValue, $url))
            ? $url[$htmlValue] : "";
            if ($arraySign) {
                $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
            } else {
                $checked = $configValue == $htmlValue ? 'checked' : '';
            }

            $dataSelector = 'options[' . $_option->getId() . ']';
            if ($arraySign) {
                $dataSelector .= '[' . $htmlValue . ']';
            }

            $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                $require .
                '"><div '. 'class="Bss_image_radio" style="height: ' .
                ($this->moduleConfig->getCheckboxSizeY() + 10) .
                'px"' . '><input type="' .
                $type .
                '" class="' .
                $class .
                ' ' .
                $require .
                ' product-custom-option"' .
                ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                ' name="options[' .
                $_option->getId() .
                ']' .
                $arraySign .
                '" id="options_' .
                $_option->getId() .
                '_' .
                $count .
                '" value="' .
                $htmlValue .
                '" ' .
                $checked .
                ' data-selector="' . $dataSelector . '"' .
                ' price="' .
                $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                '" style="margin-top: ' . ($this->moduleConfig->getCheckboxSizeY()/2 + 6) . 'px"'.'/>';
            if ($urlString) {
                $selectHtml .= '<img alt="" src="' .
                    $urlString .
                    '" title="' .
                    $_value->getTitle() .
                    '" style="height: ' .
                    $this->moduleConfig->getCheckboxSizeX() .
                    'px; width: ' .
                    $this->moduleConfig->getCheckboxSizeY() .
                    'px; float: right;" />';
            }
            $selectHtml .= '<label class="label admin__field-label" for="options_' .
                $_option->getId() .
                '_' .
                $count .'" style="margin-left: 40px;margin-top:'
                . ($this->moduleConfig->getCheckboxSizeY()/2 -4) . 'px;"><span>' .
                $_value->getTitle() .
                '</span> ' .
                $priceStr .
                '</label></div>';
            $selectHtml .= '</div>';
        }
        $selectHtml .= '</div>';

        return $selectHtml;
    }
    private function customRadio()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $this->setSkipJsReloadPrice(1);
        $url = $this->getImgUrlList();
        $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
        $require = $_option->getIsRequire() ? ' required' : '';
        $arraySign = '';
        $type = 'radio';
        $class = 'radio admin__control-radio';
        if (!$_option->getIsRequire()) {
            $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                '<input type="radio" id="options_' .
                $_option->getId() .
                '" class="' .
                $class .
                ' product-custom-option" name="options[' .
                $_option->getId() .
                ']"' .
                ' data-selector="options[' . $_option->getId() . ']"' .
                ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                $_option->getId() .
                '"><span>' .
                __('None') . '</span></label></div>';
        }
        $count = 1;
        foreach ($_option->getValues() as $_value) {
            $count++;

            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ]
            );

            $htmlValue = $_value->getOptionTypeId();
            $urlString = (($url != null) && array_key_exists($htmlValue, $url))
            ? $url[$htmlValue] : "";
            if ($arraySign) {
                $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
            } else {
                $checked = $configValue == $htmlValue ? 'checked' : '';
            }

            $dataSelector = 'options[' . $_option->getId() . ']';
            if ($arraySign) {
                $dataSelector .= '[' . $htmlValue . ']';
            }

            $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                $require .
                '"><div class="Bss_image_radio" style="height: ' .
                ($this->moduleConfig->getRadioSizeY() + 10) .
                'px"><input type="' .
                $type .
                '" class="' .
                $class .
                ' ' .
                $require .
                ' product-custom-option"' .
                ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                ' name="options[' .
                $_option->getId() .
                ']' .
                $arraySign .
                '" id="options_' .
                $_option->getId() .
                '_' .
                $count .
                '" value="' .
                $htmlValue .
                '" ' .
                $checked .
                ' data-selector="' . $dataSelector . '"' .
                ' price="' .
                $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                '" style="margin-top: ' . ($this->moduleConfig->getRadioSizeY()/2 + 6) . 'px"'.'/>';
            if ($urlString) {
                $selectHtml .= '<img alt="" src="' .
                    $urlString .
                    '" title="' .
                    $_value->getTitle() .
                    '" style="height: ' .
                    $this->moduleConfig->getRadioSizeY() .
                    'px; width: ' .
                    $this->moduleConfig->getRadioSizeX() .
                    'px; float: right;" />';
            }
            $selectHtml .= '<label class="label admin__field-label" for="options_' .
                $_option->getId() .
                '_' .
                $count .'"'.
                'style="margin-left: 40px;margin-top:' . ($this->moduleConfig->getRadioSizeY()/2 -4).'px;"><span>' .
                $_value->getTitle() .
                '</span> ' .
                $priceStr .
                '</label></div></div>';
        }
        $selectHtml .= '</div>';

        return $selectHtml;
    }
    private function defaultValues()
    {
        $_option = $this->getOption();
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN) {
            return $this->defaultDropdown();
        } elseif ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE) {
            return $this->defaultMultiselect();
        } elseif ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO) {
            return $this->defaultRadio();
        } else {
            return $this->defaultCheckbox();
        }
    }
    private function defaultCheckbox()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $this->setSkipJsReloadPrice(1);
        $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
        $require = $_option->getIsRequire() ? ' required' : '';
        $arraySign = '';
        $type = 'checkbox';
        $class = 'checkbox admin__control-checkbox';
        $arraySign = '[]';
        $count = 1;
        foreach ($_option->getValues() as $_value) {
            $count++;

            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ]
            );

            $htmlValue = $_value->getOptionTypeId();
            if ($arraySign) {
                $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
            } else {
                $checked = $configValue == $htmlValue ? 'checked' : '';
            }

            $dataSelector = 'options[' . $_option->getId() . ']';
            if ($arraySign) {
                $dataSelector .= '[' . $htmlValue . ']';
            }

            $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                $require .
                '">' .
                '<input type="' .
                $type .
                '" class="' .
                $class .
                ' ' .
                $require .
                ' product-custom-option"' .
                ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                ' name="options[' .
                $_option->getId() .
                ']' .
                $arraySign .
                '" id="options_' .
                $_option->getId() .
                '_' .
                $count .
                '" value="' .
                $htmlValue .
                '" ' .
                $checked .
                ' data-selector="' . $dataSelector . '"' .
                ' price="' .
                $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                '" />' .
                '<label class="label admin__field-label" for="options_' .
                $_option->getId() .
                '_' .
                $count .
                '"><span>' .
                $_value->getTitle() .
                '</span> ' .
                $priceStr .
                '</label>';
            $selectHtml .= '</div>';
        }
        $selectHtml .= '</div>';

        return $selectHtml;
    }
    private function defaultRadio()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $this->setSkipJsReloadPrice(1);
        $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
        $require = $_option->getIsRequire() ? ' required' : '';
        $arraySign = '';
        $type = 'radio';
        $class = 'radio admin__control-radio';
        if (!$_option->getIsRequire()) {
            $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                '<input type="radio" id="options_' .
                $_option->getId() .
                '" class="' .
                $class .
                ' product-custom-option" name="options[' .
                $_option->getId() .
                ']"' .
                ' data-selector="options[' . $_option->getId() . ']"' .
                ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                $_option->getId() .
                '"><span>' .
                __('None') . '</span></label></div>';
        }
        $count = 1;
        foreach ($_option->getValues() as $_value) {
            $count++;

            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ]
            );

            $htmlValue = $_value->getOptionTypeId();
            if ($arraySign) {
                $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
            } else {
                $checked = $configValue == $htmlValue ? 'checked' : '';
            }

            $dataSelector = 'options[' . $_option->getId() . ']';
            if ($arraySign) {
                $dataSelector .= '[' . $htmlValue . ']';
            }

            $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                $require .
                '">' .
                '<input type="' .
                $type .
                '" class="' .
                $class .
                ' ' .
                $require .
                ' product-custom-option"' .
                ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                ' name="options[' .
                $_option->getId() .
                ']' .
                $arraySign .
                '" id="options_' .
                $_option->getId() .
                '_' .
                $count .
                '" value="' .
                $htmlValue .
                '" ' .
                $checked .
                ' data-selector="' . $dataSelector . '"' .
                ' price="' .
                $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                '" />' .
                '<label class="label admin__field-label" for="options_' .
                $_option->getId() .
                '_' .
                $count .
                '"><span>' .
                $_value->getTitle() .
                '</span> ' .
                $priceStr .
                '</label>';
            $selectHtml .= '</div>';
        }
        $selectHtml .= '</div>';

        return $selectHtml;
    }
    private function defaultMultiselect()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $this->setSkipJsReloadPrice(1);
        $require = $_option->getIsRequire() ? ' required' : '';
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => 'select_' . $_option->getId(),
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );
        $select->setName('options[' . $_option->getid() . '][]');
        $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');

        foreach ($_option->getValues() as $_value) {
            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ],
                false
            );
            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)]
            );
        }
        $extraParams = ' multiple="multiple"';

        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }

        return $select->getHtml();
    }
    private function defaultDropdown()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $this->setSkipJsReloadPrice(1);
        $require = $_option->getIsRequire() ? ' required' : '';
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => 'select_' . $_option->getId(),
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );
        $select->setName('options[' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));
        foreach ($_option->getValues() as $_value) {
            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $_value->getPriceType() == 'percent',
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                ],
                false
            );
            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)]
            );
        }
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }

        return $select->getHtml();
    }
    public function getImgUrlList()
    {
        $allOptionValues = $this->opData->getUrlData($this->getProduct()->getId());
        $result = (array_key_exists($this->getOption()->getId(), $allOptionValues))
        ? $allOptionValues[$this->getOption()->getId()] : null;
        return $result;
    }
}
