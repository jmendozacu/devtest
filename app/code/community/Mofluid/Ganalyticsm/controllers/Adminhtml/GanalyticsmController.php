<?php

	class Mofluid_Ganalyticsm_Adminhtml_GanalyticsmController extends Mage_Adminhtml_Controller_Action
	{


	    /**
	     * View form action
	     */
	    public function indexAction()
	    {
		$this->_registryObject();
		$this->loadLayout();
		$this->_setActiveMenu('mofluid/form');
		$this->_addBreadcrumb(Mage::helper('mofluid_ganalyticsm')->__('Form'), Mage::helper('mofluid_ganalyticsm')->__('Form'));
		$this->getLayout()->getBlock('head')
		     ->setCanLoadExtJs(true)
		     ->setCanLoadTinyMce(true)
		     ->addItem('js','tiny_mce/tiny_mce.js')
		     ->addItem('js','mage/adminhtml/wysiwyg/tiny_mce/setup.js')
		     ->addJs('mage/adminhtml/browser.js')
		     ->addJs('prototype/window.js')
		     ->addJs('lib/flex.js')
		     ->addJs('mage/adminhtml/flexuploader.js')
		     ->addItem('js_css','prototype/windows/themes/default.css')
		     ->addItem('js_css','prototype/windows/themes/magento.css');
                 $this->renderLayout();
	    }

	    /**
	     * Grid Action
	     * Display list of products related to current category
	     *
	     * @return void
	     */
	    public function gridAction()
	    {
		$this->_registryObject();
		$this->getResponse()->setBody(
		    $this->getLayout()->createBlock('mofluid_ganalyticsm/adminhtml_form_edit_tab_product')
			->toHtml()
		);
	    }
	    
	   
    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return void
     */
    public function saveAction()
    {
        try 
        {
            $mofluid_google_analytics_post_array = $this->getRequest()->getParam('general'); 
            $model = Mage::getModel('mofluid_ganalyticsm/ganalyticsm');	
            if($model != null) {
                $mofluid_google_analytics_data = array(); 
                $mofluid_google_analytics_data['mofluid_ga_status'] = $mofluid_google_analytics_post_array['mofluid_ganalyticsm_status'];
                $mofluid_google_analytics_data['mofluid_ga_accountid'] = $mofluid_google_analytics_post_array['mofluid_ganalyticsm_account_id'];
                $mofluid_google_analytics_data['mofluid_ga_extras'] = '';
 
                $model->setData($mofluid_google_analytics_data)->setId(23);
        		$model->setCreatedTime(now())->setUpdateTime(now());
        		$model->save();
		    }
		    else {
		        echo "No Model Found"; die;
		    }
		}    
		catch(Exception $ex) {
		    echo $ex->getMessage();
	   }
	   Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mofluid_ganalyticsm')->__('Settings has been saved successfully'));
	   Mage::getSingleton('adminhtml/session')->setFormData(true);
       $this->_redirect('*/*/');
    }
   
    /**
     * registry form object
     */
    protected function _registryObject()
    {
         //Mage::register('mofluid_paymentcod', Mage::getModel('mofluid_paymentcod/form'));
    }
   
}
