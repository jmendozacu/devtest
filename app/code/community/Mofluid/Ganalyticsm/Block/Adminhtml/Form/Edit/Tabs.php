<?php
class Mofluid_Ganalyticsm_Block_Adminhtml_Form_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('edit_home_tabs');
        $this->setDestElementId('edit_form');
        $title = "Mofluid - Google Analytics";
        $this->setTitle(Mage::helper('mofluid_ganalyticsm')->__($title));
    }

    /**
     * add tabs before output
     *
     * @return Mofluid_Paymentcod_Block_Adminhtml_Form_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
         $this->addTab('configuration', array(
            'label'     => Mage::helper('mofluid_ganalyticsm')->__('Google Analytics'),
            'title'     => Mage::helper('mofluid_ganalyticsm')->__('Google Analytics'),
            'content'   => $this->getLayout()->createBlock('mofluid_ganalyticsm/adminhtml_form_edit_tab_configuration')->toHtml(),
        ));
         return parent::_beforeToHtml();
    }

}
