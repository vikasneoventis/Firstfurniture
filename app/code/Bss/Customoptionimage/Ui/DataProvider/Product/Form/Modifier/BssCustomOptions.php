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
namespace Bss\Customoptionimage\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\HtmlContent;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Boolean;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;

class BssCustomOptions extends CustomOptions
{
    private $dataObjectFactory;

    private $eventManager;

    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ConfigInterface $productOptionsConfig,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct(
            $locator,
            $storeManager,
            $productOptionsConfig,
            $productOptionsPrice,
            $urlBuilder,
            $arrayManager
        );
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
    }

    public function getCommonContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => $this->getCoapCommonChildren()
        ];

        if ($this->locator->getProduct()->getStoreId()) {
            $useDefaultConfig = [
                'service' => [
                    'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
                ]
            ];
            $titlePath = $this->arrayManager->findPath(static::FIELD_TITLE_NAME, $commonContainer, null)
                . static::META_CONFIG_PATH;
            $commonContainer = $this->arrayManager->merge($titlePath, $commonContainer, $useDefaultConfig);
        }

        return $commonContainer;
    }

    /**
     * @return array
     */
    public function getCoapCommonChildren()
    {
        $customTitle = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Option Title'),
                        'component' => 'Magento_Catalog/component/static-type-input',
                        'valueUpdate' => 'input',
                        'imports' => [
                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id'
                        ]
                    ],
                ],
            ],
        ];
        $childs = [
            10 => ['index' => static::FIELD_OPTION_ID, 'field' => $this->getOptionIdFieldConfig(10)],
            20 => ['index' => static::FIELD_TITLE_NAME, 'field' => $this->getTitleFieldConfig(20, $customTitle)],
            30 => ['index' => static::FIELD_TYPE_NAME, 'field' => $this->getTypeFieldConfig(30)],
            40 => ['index' => static::FIELD_IS_REQUIRE_NAME, 'field' => $this->getIsRequireFieldConfig(40)]
        ];

        $childObject = $this->dataObjectFactory->create()->addData($childs);

        $this->eventManager->dispatch(
            'bss_custom_options_common_container_add_child_before',
            ['child' => $childObject]
        );
        $sortedChild = $childObject->getData();
        ksort($sortedChild);
        $result = [];
        foreach ($sortedChild as $key => $child) {
            $result[$child['index']] = $child['field'];
        }
        return $result;
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     */
    public function getSelectTypeGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Value'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => $this->getSelectTypeGridChildConfig()
                ]
            ]
        ];
    }

    public function getSelectTypeGridChildConfig()
    {
        $childs = [
            50 => [
                'index' => static::FIELD_TITLE_NAME,
                'field' => $this->getTitleFieldConfig(50)
            ],
            100 => [
                'index' => static::FIELD_PRICE_NAME,
                'field' => $this->getPriceFieldConfig(100)
            ],
            150 => [
                'index' => static::FIELD_PRICE_TYPE_NAME,
                'field' => $this->getPriceTypeFieldConfig(150, ['fit' => true])
            ],
            200 => [
                'index' => static::FIELD_SKU_NAME,
                'field' => $this->getSkuFieldConfig(200)
            ],
            250 => [
                'index' => static::FIELD_SORT_ORDER_NAME,
                'field' => $this->getPositionFieldConfig(250)
            ],
            300 => [
                'index' => static::FIELD_IS_DELETE,
                'field' => $this->getIsDeleteFieldConfig(300)
            ]
        ];

        $childObject = $this->dataObjectFactory->create()->addData($childs);

        $this->eventManager->dispatch(
            'bss_custom_options_select_type_add_child_before',
            ['child' => $childObject]
        );
        $sortedChild = $childObject->getData();
        ksort($sortedChild);
        $result = [];
        foreach ($sortedChild as $key => $child) {
            $result[$child['index']] = $child['field'];
        }
        return $result;
    }
}
