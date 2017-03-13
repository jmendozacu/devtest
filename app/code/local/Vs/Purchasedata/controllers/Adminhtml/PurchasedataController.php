<?php
class Vs_Purchasedata_Adminhtml_PurchasedataController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Store Updates"));
	    $this->_setActiveMenu('purchasedata/purchasedata');
	   $this->renderLayout();
    }
    
	protected function _isAllowed()
	    {
	        return Mage::getSingleton('admin/session')->isAllowed('purchasedata/purchasedataback');
	    }
    public function saveAction()
    {
       if ($data = $this->getRequest()->getPost()) 
		{
			$admin_user_session = Mage::getSingleton('admin/session');
	        $adminuserId = $admin_user_session->getUser()->getUserId();
	        $role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
	        $roleName =  $role_data['role_name'];
			$date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		
			foreach ($_FILES as $key => $fvalue) {

			if(isset($fvalue['name']) && $fvalue['name'] != '') {

				 $filename = $fvalue['name'];

				if($fvalue['type'] == "text/csv" || $fvalue['type'] == "application/vnd.ms-excel" || $fvalue['type'] =='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {

					if($fvalue['size'] <10000000) {	       				        			        

						try {	
							if($roleName=='Administrators') {
								$roleName = "Default Store";
							}
							$path = Mage::getBaseDir() . DS ."var/datauploads/".$roleName."/" ;
								if (is_dir($path) || file_exists($path)) {

								}
								else {
									
									mkdir($path, 0777, true);						
								}        

							 $uploader = new Varien_File_Uploader($key);
						//$uploader->setAllowedExtensions(array('csv'));
			                        $uploader->setAllowRenameFiles(true);
					    			$uploader->setFilesDispersion(false);
			                        $uploader->save($path, $date.'-'.$filename);
			                        Mage::getSingleton('adminhtml/session')->addSuccess('Succesfully  uploaded file::'.$filename);
				
			                    } catch (Exception $e) {
			                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			                        Mage::getSingleton('adminhtml/session')->setFormData($data);
			                      
			          		}		               

			        }
			        else {
							Mage::getSingleton('adminhtml/session')->addError("Invaild file size file::".$filename); 
			        }   
			    }
			    else   {
					Mage::getSingleton('adminhtml/session')->addError("Invaild file type file::".$filename);  
				} 
		    }
		   
		}
			/*else {
				Mage::getSingleton('adminhtml/session')->addError("file name is empty");  
			}*/
			
		}
		$this->_redirect('*/*/');
    }
}