<?php
/**
 * @author Adjustware
 */ 
class MW_Ddate_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

	 protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if($field=='status') $field='main_table.status';
			if($field=='dtime') $field='mwdtime.dtime_id';
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {				
                    $this->getCollection()->addFieldToFilter($field , $cond);
                }
            }
        }
	
        return $this;
    }
	
    protected function _prepareCollection()
    {
        $ddate = Mage::getModel('ddate/dtime')->getCollection();
        //$collection = Mage::getModel('sales/order')->getCollection();
        $collection = Mage::getResourceModel($this->_getCollectionClass());
         $collection->getSelect()
        	->joinLeft(
	        	array('ddate_store'=>$ddate->getTable('ddate_store')),
	        	'ddate_store.increment_id = main_table.increment_id',
	        	array('ddate_store.ddate_id')
        	)
        	->joinLeft(
	        	array('mwddate'=>$ddate->getTable('ddate')),
	        	'mwddate.ddate_id = ddate_store.ddate_id',
	        	array('mwddate.ddate','mwddate.dtimetext')
        	)->joinLeft(
                        array('mwdtime' => $ddate->getTable('dtime')),
                        'mwdtime.dtime_id = mwddate.dtime',
                        array('mwdtime.dtime','mwdtime.dtime_id')
                        )
        ; 		

        $this->setCollection($collection);
        return parent::_prepareCollection();

    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        $this->addColumn('ddate', array(
            'header' => Mage::helper('ddate')->__('Delivery Date'),
            'index'  => 'ddate',
            'type'   => 'date',
            'width'  => '100px',
			'format'	  => Mage::helper('ddate')->php_date_format_M("-"),
			'filter_condition_callback' => array($this, '_myDdateFilter'),
        ));
		
     
      
      $this->addColumn('dtimetext', array(
          'header'    => Mage::helper('ddate')->__('Delivery Time'),
          'align'     => 'center',
          'width'     => '100px',
          'index'     => 'dtimetext',
      	  'type'	  => 'text',      	  
      )); 
	  
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
   protected function _myDdateFilter($collection, $column)
	{
			if (!$value = $column->getFilter()->getValue()) {
				return $this;
			}	
				$from=Mage::getSingleton('core/date')->gmtDate('Y-m-d',$value['orig_from']);
				$to= Mage::getSingleton('core/date')->gmtDate('Y-m-d',$value['orig_to']);			
			 $this->getCollection()->getSelect()->where("mwddate.ddate >= '".$from."' AND ddate <= '".$to."'"); 
			//echo  $this->getCollection()->getSelect();
			return $this;
	}
	public function getRowClass($row)  { 

        $earlistTimeslot = $this->nextSlot();
        $status = $row->getStatus();
        $orderDate = $row->getDdate();
        $orderTime =  $row->getDtimetext();
        $orderSlot = $orderDate ." ".$orderTime;

        $currentTime = Mage::getSingleton('core/date')->timestamp();
        $firstTime = explode('TO',$orderTime);
        $orderStartTime = $orderDate." ". $firstTime[0];
        
        if($status =="pending") {

           $exprs = explode('-' ,$row->getDtimetext());
           
           if(substr_count($exprs[0],'Express')>0)
                $class = "exp";
            else {
                if($earlistTimeslot == $orderSlot ) {
                    $class = "upcoming";
                }
				else if(strtotime($orderStartTime) < $currentTime) { 
                    $class = "delay";
                }
				else {
					$class = "nextcoming";
				}

            }
        }
        return $class;

    }
    public function nextSlot() {

        $block = Mage::app()->getLayout()->createBlock('ddate/onepage_ddate');
        $slots =  $block->getSlots();
        $currentTime = Mage::getSingleton('core/date')->timestamp();
        $weeks = Mage::getStoreConfig("ddate/info/weeks") != '' ? Mage::getStoreConfig("ddate/info/weeks") : 4;
        $deliveryslot = array();//[];
        for ($w = 0; $w < $weeks; $w++){            
                foreach($slots as $slot) {
                    for ($i = 2 * $w; $i < 2 * $w + 2; $i++) {                              
                        $strDate = date('Y-m-d', strtotime('+' . $i . ' day', $currentTime));
                        //echo $strDate;
                        if($block->isEnabled($slot->getId(), $strDate)){
                            $deliveryslot[] = date('Y-m-d', strtotime('+' . $i . ' day', $currentTime))." ".$slot->getDtime();                              
                        }else{
                            $deliveryslot[] = "unavailable";
                        }
                    }
                }
        }
        //$deliveryorder = array(0, 2, 4, 6, 1, 3, 5, 7, 8, 10, 12, 14, 9, 11, 13, 15);
        $neworder = array();//[];
        $x = 0;
        $count = count($slots);
        $m =0;
        $n=$count+1;
           for($k=0;$k<$count*2;$k++) {
                $order = $k;
                if($order%2==0) {
                    $deliveryorder[$m] = $order;
                    
                    $deliveryorder[$m+ $count*2+1] = $k+ $count*2;
                    $m++;
                }
                else {
                    $deliveryorder[$n] = $k;
                     $deliveryorder[$n+ $count*3] = $k+ $count*2;
                    $n++;
                   
                }
            
            }
            ksort($deliveryorder);
     
        foreach($deliveryorder as $ds){
            $neworder[$x] = $deliveryslot[$ds];
            $x++;
        }

        foreach($neworder as $key=>$value){
            if($value != "unavailable"){
                $earlisttimeslot = $value;
                break;
            }
        }
        return $earlisttimeslot;
    }

}