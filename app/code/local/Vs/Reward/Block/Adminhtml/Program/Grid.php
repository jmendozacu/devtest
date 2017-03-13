<?php
class Vs_Reward_Block_Adminhtml_Program_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('vs_reward_program_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'vs_reward/program_collection';
    }
     
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass());
		//print_r($collection);
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'id'
            )
        );
  		$this->addColumn('customer_id',
            array(
                'header'=> $this->__('Loyalty Card ID'),
                'index' => 'customer_id'
            )
        );
		 $this->addColumn('contact_number',
            array(
                'header'=> $this->__('Contact Number'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'contact_number'
            )
        );
		 $this->addColumn('bill_amount',
            array(
                'header'=> $this->__('Bill Amount'),
                'index' => 'bill_amount'
            )
        );
         $this->addColumn('online_login',
            array(
                'header'=> $this->__('Online Login'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'online_login'
            )
        );
		   $this->addColumn('redemption_flag',
            array(
                'header'=> $this->__('Redemption Flag'),
                'index' => 'redemption_flag'
            )
        ); 
		 
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}