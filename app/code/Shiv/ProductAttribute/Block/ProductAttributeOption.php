<?php
namespace Shiv\ProductAttribute\Block;

class ProductAttributeOption extends \Magento\Framework\View\Element\Template
{
    protected $_productAttributeRepository;

    public function __construct(        
        \Magento\Framework\View\Element\Template\Context $context,   
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        array $data = [] 
    ){        
        parent::__construct($context,$data);
        $this->_productAttributeRepository = $productAttributeRepository;
    } 

    public function getAllMeterials(){
        $meterialOptions = $this->_productAttributeRepository->get('material')->getOptions();       
        $values = array();
        foreach ($meterialOptions as $meterialOption) { 
           //$manufacturerOption->getValue();  // Value
            $values[] = $meterialOption->getLabel();  // Label
        }
        return $values;
    }  
}