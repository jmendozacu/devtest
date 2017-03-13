<?php
class Vs_Reward_Adminhtml_ProgramController
    extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {  
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
            ->renderLayout();
    }  
     
    public function newAction()
    {  
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }  
     
    public function editAction()
    {  
        $this->_initAction();
     
        // Get id if available
        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('vs_reward/program');
     
        if ($id) {
            // Load record
            $model->load($id);
     
            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This program no longer exists.'));
                $this->_redirect('*/*/');
     
                return;
            }  
        }  
     
        $this->_title($model->getId() ? $model->getName() : $this->__('New Reward Program'));
     
        $data = Mage::getSingleton('adminhtml/session')->getProgramData(true);
        if (!empty($data)) {
            $model->setData($data);
        }  
     
        Mage::register('vs_reward', $model);
     
        $this->_initAction()
            ->_addBreadcrumb($id ? $this->__('Edit Reward Program') : $this->__('New Reward Program'), $id ? $this->__('Edit Reward Program') : $this->__('New Reward Program'))
            ->_addContent($this->getLayout()->createBlock('vs_reward/adminhtml_program_edit')->setData('action', $this->getUrl('*/*/save')))
            ->renderLayout();
    }
     
    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
			
            $model = Mage::getSingleton('vs_reward/program');
            $model->setData($postData);

            try {
                $model->save();
 
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The program has been saved.'));
                $this->_redirect('*/*/');
 
                return;
            }  
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this program.'));
            }
 
            Mage::getSingleton('adminhtml/session')->setProgramData($postData);
            $this->_redirectReferer();
        }
    }
     
    public function messageAction()
    {
        $data = Mage::getModel('vs_reward/program')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }
     
    /**
     * Initialize action
     *
     * Here, we set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {    	
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('sales/vs_reward_program')
            ->_title($this->__('Sales'))->_title($this->__('Reward Program'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Program'), $this->__('Reward Program'));
         
        return $this;
    }
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/vs_reward_program');
    }
}