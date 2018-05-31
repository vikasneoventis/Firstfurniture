<?php
namespace Shiv\ProductAttribute\Block;

class ProductCategories extends \Magento\Framework\View\Element\Template
{
    protected $_productRepository;
    protected $_categoryFactory;
    protected $_category;
    protected $_categoryHelper;
    protected $_categoryRepository;

    public function __construct(        
        \Magento\Framework\View\Element\Template\Context $context,   
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        array $data = [] 
    ){        
        parent::__construct($context,$data);
        $this->_productRepository = $productRepository;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryRepository = $categoryRepository;
    }
    
    /**
     * Get category object
     * Using $_categoryFactory
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory($categoryId) 
    {
        $this->_category = $this->_categoryFactory->create();
        $this->_category->load($categoryId);        
        return $this->_category;
    }
    
    /**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds($categoryId = false)
    {
        $category = $this->getCategory($categoryId);
        if($category->getLevel() == 4 && in_array(4782, $category->getParentIds()))
        {
            return $category;
        }
        return false;       
    }
    
    /**
     * Get category object
     * Using $_categoryRepository
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryById($categoryId) 
    {
        return $this->_categoryRepository->get($categoryId);
    }

    public function getAllCategories($productId){
        $product = $this->_productRepository->getById($productId);
        $cats = $product->getCategoryIds();
        //echo "<pre>"; print_r($cats); echo $productId;die;
        // Fetch the 'category_ids' attribute from the Data Model.
        //if ($categoryIds = $product->getCustomAttribute('category_ids'))
        if(is_array($cats) && count($cats)>0)
        {
            //foreach ($categoryIds->getValue() as $categoryId)
            foreach ($cats as $categoryId)
            {
                $category = $this->getParentIds($categoryId);
                if($category)
                {
                    return $category;
                }
            }
        }
    }  
}
