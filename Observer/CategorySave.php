<?php
namespace Paulmillband\AdminProductsToCategory\Observer;

use Magento\Backend\App\Action;

/**
 * Class Save
 */
 class CategorySave
    implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var  \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var      \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var
     */
    protected $productCollection;

     /**
      *  @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
      */
     protected $attributeHelper;


     protected $request;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
    ) {
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->messageManager = $messageManager;
        $this->attributeHelper = $attributeHelper;
        $this->request = $request;
    }

     /**
      * @return \Magento\Framework\App\Request\Http
      */
     protected function getRequest()
     {
         return $this->request;
     }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection(){
        if(!$this->productCollection){
            $this->productCollection = $this->attributeHelper->getProducts();
        }
        return $this->productCollection;
    }

    /**
     * @param array $categoryIds
     */
    public function addProductToCategory($categoryIds=[]){
        if(!count($categoryIds)){
            return;
        }
        foreach($this->getProductCollection() as $product) {
            $categoryIdsArray = array_unique(
                array_merge($categoryIds, $product->getCategoryIds()),
                SORT_STRING
            );
            $this->categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                $categoryIdsArray
            );
        }
    }

    /**
     * @param array $categoryIds
     */
    public function removeProductToCategory($categoryIds=[]){
        if(!count($categoryIds)){
            return;
        }
        foreach($this->getProductCollection() as $product) {
            $categoryIdsArray = array_diff($product->getCategoryIds(), $categoryIds);
            $this->categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                $categoryIdsArray
            );
        }
    }

     /**
      * @param \Magento\Framework\Event\Observer $observer
      * @return mixed
      */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->getProductCollection()) {
            return ;
        }
        /* Collect Data */
        $categoryRemoveData = $this->getRequest()->getParam('remove_category_ids', []);
        $categoryAddData = $this->getRequest()->getParam('add_category_ids', []);
        try {
            if (!empty($categoryAddData)
                || !empty($categoryRemoveData)
            ) {
                if ($categoryAddData) {
                    $this->addProductToCategory($categoryAddData);
                }
                if ($categoryRemoveData) {
                    $this->removeProductToCategory($categoryRemoveData);
                }

                $this->messageManager
                    ->addSuccess(__(
                        'A total of %1 record(s) were updated.',
                        count($this->attributeHelper->getProductIds())
                    ));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) categories.')
            );
        }
    }
}
