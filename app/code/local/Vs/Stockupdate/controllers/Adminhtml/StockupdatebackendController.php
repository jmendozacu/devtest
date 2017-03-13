<?php
class Vs_Stockupdate_Adminhtml_StockupdatebackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Quick Updates"));
	   $this->_setActiveMenu('purchasedata/stockupdatebackend');
	   $this->renderLayout();
    }
}