<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */
namespace Magebuzz\Timecountdown\Model\Config\Source;

class Styles implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'simple', 'label' => __('Simple')], 
            ['value' => 'colorful', 'label' => __('Colorful')],
            ['value' => 'flip', 'label' => __('Flip')]
        ];
    }
}
