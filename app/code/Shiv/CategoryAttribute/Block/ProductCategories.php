<?php 
namespace Shiv\CategoryAttribute\Block;

class ProductCategories extends \Magento\Framework\View\Element\Template
{    
    protected $_categoryCollectionFactory;
    protected $_productRepository;
    protected $_registry;
    protected $_product = null;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        array $data = []
        )
        {
            $this->_categoryCollectionFactory = $categoryCollectionFactory;
            $this->_productRepository = $productRepository;
            $this->_registry = $registry;
            parent::__construct($context, $data);
    }
    
    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');        
        
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
                
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }
        
        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
        
        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize); 
        }    
        
        return $collection;
    }
    
    public function getProductById($id)
    {        
        return $this->_productRepository->getById($id);
    }
    
    public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    }
    
    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_registry->registry('product');
        }
        return $this->_product;
    }
}
?>