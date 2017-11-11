<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product mass attribute update categories tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Paulmillband\AdminProductsToCategory\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Store\Model\Group;

/**
 * @api
 * @since 100.0.2
 */
class Categories extends \Magento\Backend\Block\Widget
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $categoryHelper;
    protected $categoryFactory;
    protected $storeManager;
    protected $categoryTreeFactory;
    protected $categoryFlatState;
    protected $resultPageFactory;
    protected $categoryCollection;
    protected $context;
    protected $layout;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
//        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    ) {
        $this->context = $context;
        $this->categoryHelper = $categoryHelper;
        $this->categoryTreeFactory = $categoryTreeFactory;
        $this->categoryFlatState = $categoryFlatState;
        $this->resultPageFactory = $resultPageFactory;
        $this->layout = $layout;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Tab settings
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Categories');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Categories');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve current store level 2 category
     *
     * @param bool|string $sorted (if true display collection sorted as name otherwise sorted as based on id asc)
     * @param bool $asCollection (if true display all category otherwise display second level category menu visible category for current store)
     * @param bool $toLoad
     */

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }

    /**
     * @return Category[]
     */
    public function getCategoryCollection()
    {
        if(!$this->categoryCollection){
            $this->categoryCollection = $this->getStoreCategories(true, false, true);
        }
        return $this->categoryCollection;
    }

    public function setCategoryCollection($categoryCollection)
    {
        $this->categoryCollection = $categoryCollection;
    }
    /**
     * Return categories helper
     */
    public function getCategoryHelper()
    {
        return $this->categoryHelper;
    }

    /**
     * Retrieve child store categories
     *
     */
    public function getChildCategories($category)
    {
        if ($this->categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }

    public function getSubBlockHtml($categories){
        $class = get_class($this);
        $template = $this->getTemplate();
        $block = $this->layout
            ->createBlock($class)
            ->setTemplate($template);
        $block->setCategoryCollection($categories);
        $html = $block->toHtml();
        return $html;
    }

    /**
     * @param Group $group
     * @return array
     */
    public function getStoreCollection(Group $group)
    {
        return $group->getStores();
    }



}
