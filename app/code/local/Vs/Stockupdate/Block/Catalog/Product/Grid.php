<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Vs_Stockupdate_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $isenhanced = true;
    private $columnSettings = array();
    private $isenabled = true;

    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collectiontmp = Mage::getModel('catalog/product')->getCollection();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('manufacturer')
            ->addAttributeToSelect('packsize')
            ->addAttributeToSelect('store_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
            $collectiontmp->joinTable(
            'catalog/category_product',
            'product_id=entity_id',
            array('single_category_id' => 'category_id'),
            null,
            'left'
        );
           $collection->joinTable( 'cataloginventory/stock_item', 'product_id=entity_id', array("stock_status" => "is_in_stock") )->addAttributeToSelect('stock_status');
        }
     
       
            /** create category name 'dailyupdate' **/
        $category =  Mage::getModel('catalog/category')
                ->getCollection(true)
                ->addAttributeToSelect('*')                
                ->addAttributeToFilter('level','2')
                ->addIsActiveFilter();

        $categorysub =  $category->addFieldToFilter('name', 'dailyupdate')
        ->getFirstItem(); 
        
         $catId = $categorysub->getId();
        $collectiontmp->addFieldToFilter('single_category_id', array(
                             'eq' => $catId
                        ));
        /** Get store id from roll id**/
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
        $roleName =  $role_data['role_name'];

        $collectionRole = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('name', $roleName);

        $storeRole = $collectionRole->getFirstItem();

        if ($storeRole->getId()) {
            $storeId =  $storeRole->getId();
        }
        else
            $storeId= 0;

        if ($store->getId()) { 
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
             $collection->joinAttribute(
                'store_id',
                'catalog_product/store_id',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
          
        }
        else {
            $collection->joinAttribute('price','catalog_product/price', 'entity_id', null, 'left', $storeId);
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }


        $collectiontmp->addStoreFilter(  $storeId
                            );

        Mage::getSingleton('adminhtml/session')->setRoleStoreId($storeId);
            

        $collection->joinAttribute('special_price', 'catalog_product/special_price', 'entity_id', null, 'left', $storeId);
        $modelStockupdate = Mage::getModel('stockupdate/stockupdate')->load();

        $customPids = $modelStockupdate->gettingProductIds($storeId);
        
        $tmpProductIds = array();
        foreach ($collectiontmp as $_product){
            $tmpProductIds[] =  $_product->getId();   
        }

    $allProductIds = array_merge($tmpProductIds,$customPids);

    $collection->addIdFilter( $allProductIds);

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
       
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
            ));
        }


        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Manufacturer'),
                'width' => '60px',
                'index' => 'manufacturer',
                'type' => 'options',
                'options' => $this->_getAttributeOptions('manufacturer'),
        ));

$sites = Mage::getModel('core/website')->getCollection()->toOptionHash();


        $this->addColumn('packsize',
            array(
                'header'=> Mage::helper('catalog')->__('packsize'),
                'width' => '100px',
                'index' => 'packsize',
               'type' => 'options',
            'options' => $this->_getAttributeOptions('packsize'),
                
        ));
         /** Get store id from roll id**/
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
        $roleName =  $role_data['role_name'];

        $collectionRole = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('name', $roleName);

        $storeRole = $collectionRole->getFirstItem();

        if ($storeRole->getId()) {
            $storeId =  $storeRole->getId();
        }
        else
            $storeId= 0;

        if(Mage::app()->isSingleStoreMode()) {

            $this->addColumn('stock_status',

            array(

                'header'=> 'Instock/outstock',

                'width' => '60px',             //this is the column width

                'index' => 'stock_status',

                'type'  => 'options',

                'options' => array('1'=>'In stock','0'=>'Out of stock'),

        ));
        }
        
        else if($storeId){

            $this->addColumn('Instock/outstock',
            array(
                    'header'=> Mage::helper('catalog')->__('Instock/outstock'),
                    'width' => '50px',
                    'align' => 'right',
                    'index' => 'websites',
                    'filter' =>false,
                    'renderer' =>'stockupdate/adminhtml_widget_grid_column_renderer_inoutstock',
            ));
        }
        else {    
          if (!Mage::app()->isSingleStoreMode()) {
                    $this->addColumn('websites',
                        array(
                            'header'=> Mage::helper('catalog')->__('Websites'),
                            'width' => '100px',
                            'sortable'  => false,
                            'index'     => 'websites',
                            'type'      => 'options',
                            'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                    ));
                }
        }
     

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'renderer'         => 'stockupdate/adminhtml_widget_grid_column_renderer_inline',
        ));
               $this->addColumn('special_price',
            array(
                'header'=> Mage::helper('catalog')->__('Special Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'special_price',
                'renderer'         => 'stockupdate/adminhtml_widget_grid_column_renderer_inline',
        ));



        if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('instock', array(
             'label'=> Mage::helper('catalog')->__('Instock'),
             'url'  => $this->getUrl('*/*/massInstock')
                     ));
  $this->getMassactionBlock()->addItem('outstock', array(
             'label'=> Mage::helper('catalog')->__('Out of stock'),
             'url'  => $this->getUrl('*/*/massOutofstock')
                     ));


        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
 protected function _getAttributeOptions($attribute_code)
        {
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_code);
            $options = array();
            foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {
                $options[$option['value']] = $option['label'];
            }
            return $options;
        }
  
}
