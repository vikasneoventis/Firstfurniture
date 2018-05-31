<?php
/**
 * Copyright © 2015 Oldenglishbrand . All rights reserved.
 */
namespace Shiv\CategoryMegaMenu\Block\Category;

class MegaMenu extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	 /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatConfig;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection */
    protected $_productCollectionFactory;

    /** @var \Magento\Catalog\Helper\Output */
    private $helper;

    /**
     * @param Template\Context                                        $context
     * @param \Magento\Catalog\Helper\Category                        $categoryHelper
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State      $categoryFlatState
     * @param \Magento\Catalog\Model\CategoryFactory                  $categoryFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollectionFactory
     * @param \Magento\Framework\App\ObjectManager                    $objectManager
     *
     * @internal param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollectionFactory,
        \Magento\Catalog\Helper\Output $helper,
        $data = [ ]
    )
    {
        $this->_categoryHelper           = $categoryHelper;
        $this->_coreRegistry             = $registry;
        $this->categoryFlatConfig        = $categoryFlatState;
        $this->_categoryFactory          = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->helper                    = $helper;

        parent::__construct($context, $data);
    }
    
    /**
     * Get all categories
     *
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Framework\Data\Tree\Node\Collection
     */
    public function getCategories($rootCategory = 0, $sorted = false, $asCollection = false, $toLoad = true)
    { 
		if (!$this->getData('id_path')) {
                throw new \RuntimeException('Parameter id_path is not set.');
        }
		if($rootCategory > 0)
		{
			$rootCategory = $rootCategory;
		}
		else
		{
			$rewriteData = $this->parseIdPath($this->getData('id_path'));
			$rootCategory = $rewriteData[1];
		}
		
		$recursionLevel = 2;
		
		$cacheKey = sprintf('%d-%d-%d-%d', $rootCategory, $sorted, $asCollection, $toLoad);
		if ( isset($this->_storeCategories[ $cacheKey ]) )
		{
		    return $this->_storeCategories[ $cacheKey ];
		}

		/**
		 * Check if parent node of the store still exists
		 */
		$category = $this->_categoryFactory->create();
		
		$storeCategories = $category->getCategories($rootCategory, $recursionLevel, $sorted, $asCollection, $toLoad);
		$this->_storeCategories[ $cacheKey ] = $storeCategories;
  
		return $storeCategories;
    }
    
    /**
     * @param        $category
     * @param string $html
     * @param int    $level
     *
     * @return string
     */
    public function getChildCategoryView($category, $html = '', $level = 2)
    {
		$level = 2;
		// Check if category has children
		if ($category->hasChildren() )
		{
  
		    $childCategories = $this->getSubcategories($category);
  
		    if ( count($childCategories) > 0 )
		    {
  
			   $html .= '<ul class="o-list o-list--unstyled">';
  
			   // Loop through children categories
			   foreach ( $childCategories as $childCategory )
			   {
  
				  $html .= '<li class="level' . $level . ($this->isActive($childCategory) ? ' active' : '') . '">';
				  $html .= '<a href="' . $this->getCategoryUrl($childCategory) . '" title="' . $childCategory->getName() . '" class="' . ($this->isActive($childCategory) ? 'is-active' : '') . '">' . $childCategory->getName() . '</a>';
  
				  if ( $childCategory->hasChildren() )
				  {
					 if ( $this->isActive($childCategory) )
					 {
						$html .= '<span class="expanded"><i class="fa fa-minus"></i></span>';
					 }
					 else
					 {
						$html .= '<span class="expand"><i class="fa fa-plus"></i></span>';
					 }
				  }
  
				  if ( $childCategory->hasChildren() )
				  {
					 $html .= $this->getChildCategoryView($childCategory, '', ($level + 1));
				  }
  
				  $html .= '</li>';
			   }
			   $html .= '</ul>';
		    }
		}
		return $html;
    }
    
    /**
     * Retrieve subcategories
     *
     * @param $category
     *
     * @return array
     */
    public function getSubcategories($category)
    {
        if ( $this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource() )
        {
            return (array)$category->getChildrenNodes();
        }

        return $category->getChildren();
    }

    /**
     * Get current category
     *
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return Category
     */
    public function isActive($category)
    {
        $activeCategory = $this->_coreRegistry->registry('current_category');
        $activeProduct  = $this->_coreRegistry->registry('current_product');

        if ( !$activeCategory )
        {

            // Check if we're on a product page
            if ( $activeProduct !== null )
            {
                return in_array($category->getId(), $activeProduct->getCategoryIds());
            }

            return false;
        }

        // Check if this is the active category
        if ( $this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource() AND
            $category->getId() == $activeCategory->getId()
        )
        {
            return true;
        }

        // Check if a subcategory of this category is active
        $childrenIds = $category->getAllChildren(true);
        if ( !is_null($childrenIds) AND in_array($activeCategory->getId(), $childrenIds) )
        {
            return true;
        }

        // Fallback - If Flat categories is not enabled the active category does not give an id
        return (($category->getName() == $activeCategory->getName()) ? true : false);
    }
    
    /**
     * Return Category Id for $category object
     *
     * @param $category
     *
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->_categoryHelper->getCategoryUrl($category);
    }
    
    /**
     * Render block HTML
     * or return empty string if url can't be prepared
     *
     * @return string
     */
    protected function _toHtml()
    {
		$this->setTemplate($this->getTemplate());
		return parent::_toHtml();
        
    }
    
    public function getTemplate()
    {
        $template = $this->getData('filter_template');
        if($template == 'custom')
        {
            return $this->getData('custom_template');
        }
        else
        {
            return $template;
        }
    }
    
    /**
     * Parse id_path
     *
     * @param string $idPath
     * @throws \RuntimeException
     * @return array
     */
    protected function parseIdPath($idPath)
    {
		
		$rewriteData = explode('/', $idPath);
		if (!isset($rewriteData[0]) || !isset($rewriteData[1])) {
		    throw new \RuntimeException('Wrong id_path structure.');
		}
		return $rewriteData;
	}
	
	public function getCategoryImage($category)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$_category = $objectManager->create('Magento\Catalog\Model\Category')->load($category->getId());
		$_imgHtml = '';
		if ($_imgUrl = $_category->getImageUrl())
		{
			return $_imgUrl;
		}
	}
	
}
