<?php

class XJ_Importexportcategory_Adminhtml_ImportexportcategoryController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('importexportcategory/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	/*public function fileuploadeAction() 
	{
		$params = $this->getRequest()->getParams();
		if($params['isAjax'] == 1)
		{
			$response = array();
			$response['status'] = 'SUCCESS';
		        $response['message'] = $this->__('Unable to find Product ID');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
		    return;
		}
	}*/
		public function fileuploadeAction() 
	{
		if ($data = $this->getRequest()->getPost()) 
		{
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '')
			{
				if($_FILES['filename']['type'] == "text/csv")
				{
					try {	
					$path = Mage::getBaseDir() . DS ."var/import/category/" ;
					if (is_dir($path) || file_exists($path))
					{
				
					}
					else
					{
						mkdir($path, 0777, true);
					}                               
					 $uploader = new Varien_File_Uploader('filename');
					$uploader->setAllowedExtensions(array('csv'));
		                        $uploader->setAllowRenameFiles(false);
				    	$uploader->setFilesDispersion(false);
		                        $uploader->save($path, 'categories.csv');
			
		                    } catch (Exception $e) {
		                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		                        Mage::getSingleton('adminhtml/session')->setFormData($data);
		                      
		                         return;
		                    }  
			    }
			    else
			     {
				Mage::getSingleton('adminhtml/session')->addError("Invaild file type");  
				} 
		        }
			else
			{
				Mage::getSingleton('adminhtml/session')->addError("file name is empty");  
			}
		}
		$this->_redirect('*/*/');
	}
	public function categoryAction() {
		$type = $this->getRequest()->getParam('type');
		$this->_title(Mage::helper('importexportcategory')->__('Export Category Profiles'));
		$this->getResponse()->setBody($this->getLayout()->createBlock('importexportcategory/adminhtml_category_export_'.$type)->toHtml());
		$this->getResponse()->sendResponse();
	}
	public function categoryimportAction() {
					try 
					{	
						$path = Mage::getBaseDir() . DS ."var/import/category/" ;
						if (is_dir($path) || file_exists($path))
						{
				
						}
						else
						{
							mkdir($path, 0777, true);
						}                               
						 $uploader = new Varien_File_Uploader('filename');
						$uploader->setAllowedExtensions(array('csv'));
				                $uploader->setAllowRenameFiles(false);
					    	$uploader->setFilesDispersion(false);
				                $uploader->save($path, 'categories.csv');
		                    	} 
					catch (Exception $e) {
		                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		                        Mage::getSingleton('adminhtml/session')->setFormData($data);
		                  
		                         return;
		                    }  
		$this->_title(Mage::helper('importexportcategory')->__('Import Category Profiles'));
		$this->getResponse()->setBody($this->getLayout()->createBlock('importexportcategory/adminhtml_category_import_Categories')->toHtml());
		$this->getResponse()->sendResponse();
	}
}
