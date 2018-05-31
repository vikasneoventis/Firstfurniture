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
namespace Bss\Customoptionimage\Plugin;

class OptionValuePlugin
{
    private $imageSaving;

    private $imageUrlFactory;

    public function __construct(
        \Bss\Customoptionimage\Helper\ImageSaving $imageSaving,
        \Bss\Customoptionimage\Model\ImageUrlFactory $imageUrlFactory
    ) {
        $this->imageSaving = $imageSaving;
        $this->imageUrlFactory = $imageUrlFactory;
    }

    public function aroundSave(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $proceed
    ) {
        $imageUrl = $this->imageSaving->moveImage($subject);
        $proceed();
        $imageUrlModel = $this->imageUrlFactory->create()->getCollection()
        ->getItemByColumnValue('option_type_id', $subject->getOptionTypeId());
        if (!$imageUrlModel) {
            $imageUrlModel = $this->imageUrlFactory->create();
        }
        $imageUrlModel
        ->setOptionTypeId($subject->getOptionTypeId())
        ->setImageUrl($imageUrl)
        ->save();
    }

    public function aroundGetData(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $proceed,
        $key = '',
        $index = null
    ) {
        $result = $proceed($key, $index);
        if ($key === '') {
            if (isset($result['option_type_id']) && !isset($result['image_url'])) {
                $imageData = $this->imageUrlFactory->create()->getCollection()
                ->getItemByColumnValue('option_type_id', $result['option_type_id']);
                if ($imageData) {
                    $imageData = $imageData->getImageUrl();
                }
                $result['image_url'] = $imageData;
            }
        }
        if ($key === 'image_url' && $subject->getData('option_type_id') && !$subject->hasData('image_url')) {
            $imageData = $this->imageUrlFactory->create()->getCollection()
            ->getItemByColumnValue('option_type_id', $subject->getData('option_type_id'));
            if ($imageData) {
                $imageData = $imageData->getImageUrl();
            }
            return $imageData;
        }
        return $result;
    }
}
