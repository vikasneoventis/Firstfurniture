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
namespace Bss\Customoptionimage\Observer\Adminhtml;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Framework\UrlInterface;

class AddBackendField implements ObserverInterface
{

    const FIELD_UPLOAD_IMAGE_PREVIEW = 'image_url';

    const FIELD_UPLOAD_IMAGE_BUTTON = 'bss_image_button';

    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getChild()->addData($this->getCustomImageField());
    }
    protected function getCustomImageField()
    {
        return [
            230 => ['index' => static::FIELD_UPLOAD_IMAGE_PREVIEW, 'field' => $this->getImagePreviewFieldConfig(230)],
            240 => ['index' => static::FIELD_UPLOAD_IMAGE_BUTTON, 'field' => $this->getUploadButtonFieldConfig(240)]
        ];
    }

    /**
    Bss
     */

    protected function getImagePreviewFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Image'),
                        'componentType' => Field::NAME,
                        'component' => 'Bss_Customoptionimage/js/image_preview',
                        'elementTmpl' => 'Bss_Customoptionimage/image-preview',
                        'dataScope' => static::FIELD_UPLOAD_IMAGE_PREVIEW,
                        'dataType' => Text::NAME,
                        'formElement' => Checkbox::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => ''
                        ]
                    ],
                ],
            ],
        ];
    }

    protected function getUploadButtonFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'fileUploader',
                        'componentType' => 'file',
                        'component' => 'Bss_Customoptionimage/js/upload_field',
                        'elementTmpl' => 'Bss_Customoptionimage/upload-field',
                        'visible' => true,
                        'dataType' => Media::NAME,
                        'required' => false,
                        'label' => __('Upload'),
                        'dataScope' => static::FIELD_UPLOAD_IMAGE_BUTTON,
                        'sortOrder' => $sortOrder,
                        'baseUrl' => $this->urlBuilder->getUrl('bss_coi/json/uploader')
                    ],
                ],
            ],
        ];
    }
}
