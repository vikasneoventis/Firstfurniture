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
namespace Bss\Customoptionimage\Block\Render;

class PluginBlock extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\Customoptionimage\Helper\ModuleConfig $moduleConfig,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->setTemplate('Bss_Customoptionimage::select/image-render.phtml');
    }

    public function getConfigHelper()
    {
        return $this->moduleConfig;
    }

    public function getImageList()
    {
        $result = [];
        $values = $this->getOption()->getValues();
        foreach ($values as $key => $value) {
            $valueData = $value->getData();
            if ($valueData['image_url']) {
                $result[] = [
                    'id' => $valueData['option_type_id'],
                    'url' => $valueData['image_url'],
                    'title' => $value['title']
                ];
            }
        }
        return $this->jsonEncoder->encode($result);
    }
}
