<?php
class Vs_Reward_Block_Adminhtml_Program_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {  
        parent::__construct();
     
        $this->setId('vs_reward_program_form');
        $this->setTitle($this->__('Reward Program Information'));
    }  
     
    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {  
        $model = Mage::registry('vs_reward');
     
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('checkout')->__('Reward Program Information'),
            'class'     => 'fieldset-wide',
        ));
     
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }  
      $fieldset->addField('customer_id', 'text', array(
            'name'      => 'customer_id',
            'label'     => Mage::helper('checkout')->__('Loyalty Card ID'),
            'title'     => Mage::helper('checkout')->__('Loyalty Card ID'),
            'required'  => true,
        ));
		$fieldset->addField('contact_number', 'text', array(
            'name'      => 'contact_number',
            'label'     => Mage::helper('checkout')->__('Contact Number'),
            'title'     => Mage::helper('checkout')->__('Contact Number'),
            'required'  => true,
        ));
		$fieldset->addField('bill_amount', 'text', array(
            'name'      => 'bill_amount',
            'label'     => Mage::helper('checkout')->__('Bill Amount'),
            'title'     => Mage::helper('checkout')->__('Bill Amount'),
            'required'  => true,
        ));
		$fieldset->addField('online_login', 'text', array(
            'name'      => 'online_login',
            'label'     => Mage::helper('checkout')->__('Online Login'),
            'title'     => Mage::helper('checkout')->__('Online Login'),
            'required'  => true,
        ));
		$fieldset->addField('redemption_flag', 'text', array(
            'name'      => 'redemption_flag',
            'label'     => Mage::helper('checkout')->__('Redemption Flag'),
            'title'     => Mage::helper('checkout')->__('Redemption Flag'),
            'required'  => true,
        ));
     
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    }  
}