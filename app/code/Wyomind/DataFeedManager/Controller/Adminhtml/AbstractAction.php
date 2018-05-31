<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml;

abstract class AbstractAction extends \Magento\Backend\App\Action
{

    protected $_coreRegistery = null;
    protected $_productFactory = null;
    protected $_fileSystem = null;
    protected $_attributeFactory = null;
    protected $_attributeTypeFactory = null;
    protected $_attributeOptionValueCollection = null;
    protected $_resultPageFactory = null;
    protected $_directoryList = null;
    protected $_productCollection = null;
    protected $_resultRedirectFactory = null;
    protected $_attributesHelper = null;

    public $dfmHelper = null;
    public $dfmModel = null;
    public $directoryRead = null;
    public $coreHelper = null;
    public $productAttributeRepository = null;
    public $attributeRepository = null;
    public $productRepository = null;
    public $parserHelper = null;
    public $messageManager = null;
    public $resultForwardFactory = null;
    public $variablesCollectionFactory = null;

    public $title = "";
    public $breadcrumbOne = "";
    public $breadcrumbTwo = "";
    public $menu = "";
    public $model = "";
    public $errorDoesntExist = "";
    public $successDelete = "";
    public $msgModify = "";
    public $msgNew = "";
    public $registryName = "";
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Wyomind\DataFeedManager\Model\Product\Collection $productCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionValueCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\DataFeedManager\Helper\Data $dfmHelper,
        \Wyomind\DataFeedManager\Model\Feeds $dfmModel,
        \Magento\Eav\Model\Entity\TypeFactory $attributeTypeFactory,
        \Wyomind\DataFeedManager\Helper\Parser $parserHelper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeRepository,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Wyomind\DataFeedManager\Model\ResourceModel\Variables\CollectionFactory $variablesCollectionFactory,
        \Wyomind\DataFeedManager\Helper\Attributes $attributesHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_attributeFactory = $attributeFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_config = $config;
        $this->_directoryList = $directoryList;
        $this->_productCollection = $productCollection;
        $this->_attributeOptionValueCollection = $attributeOptionValueCollection;
        $this->_productFactory = $productFactory;
        $this->_attributeTypeFactory = $attributeTypeFactory;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_attributesHelper = $attributesHelper;
        
        $this->dfmHelper = $dfmHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dfmModel = $dfmModel;
        $root = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        if (file_exists($root."/app/code/Wyomind/DataFeedManager")) {
            $this->directoryRead = $directoryRead->create($root."/app/code/Wyomind/DataFeedManager");
        } elseif (file_exists($root."/vendor/wyomind/datafeedmanager")) {
            $this->directoryRead = $directoryRead->create($root."/vendor/wyomind/datafeedmanager");
        }
        $this->coreHelper = $coreHelper;
        $this->parserHelper = $parserHelper;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->messageManager = $context->getMessageManager();
        $this->variablesCollectionFactory = $variablesCollectionFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_DataFeedManager::' . $this->menu);
    }

    public function delete()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create($this->model);
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccess(__($this->successDelete));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__($this->errorDoesntExist));
        }

        $return = $this->_resultRedirectFactory->create()->setPath('datafeedmanager/' . $this->menu . '/index');
        return $return;
    }

    /**
     * Execute Edit action
     * @return type
     */
    public function edit()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Catalog::catalog");
        $resultPage->addBreadcrumb(__($this->breadcrumbOne), __($this->breadcrumbOne));
        $resultPage->addBreadcrumb(__($this->breadcrumbTwo), __($this->breadcrumbTwo));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create($this->model);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__($this->errorDoesntExist));
                return $this->_resultRedirectFactory->create()->setPath('datafeedmanager/' . $this->menu . '/index');
            }
        }
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? (__($this->msgModify)) : __($this->msgNew));

        $this->_coreRegistry->register($this->registryName, $model);

        return $resultPage;
    }

    /**
     * Execute new action
     */
    public function newAction()
    {
        return $this->resultForwardFactory->create()->forward("edit");
    }

    /**
     * Execute index action
     */
    public function index()
    {
        $this->coreHelper->checkHeartbeat();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Catalog::" . $this->menu);
        $resultPage->getConfig()->getTitle()->prepend(__($this->title));
        $resultPage->addBreadcrumb($this->breadcrumbOne, __($this->breadcrumbOne));
        $resultPage->addBreadcrumb($this->breadcrumbTwo, __($this->breadcrumbTwo));
        return $resultPage;
    }
}
