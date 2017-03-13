<?php

class MW_Ddate_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getDtime($storeid=null) {
		if(empty($storeid)) $storeid=Mage::app()->getStore()->getId();
        $dtimes = Mage::getModel('ddate/dtime')->getCollection();
        $dtimes->getSelect()
                ->join('mwdtime_store', 'mwdtime_store.dtime_id = main_table.dtime_id ', array('main_table.dtime_id')
                )
                ->where('mwdtime_store.store_id in (?)', array('0',$storeid ))
                ->where('main_table.status = 1');
        return $dtimes;
    }

    public function getSpecialDay() {
		$regis=Mage::registry('mw_ddate_specialday');//fix error:Mage registry key "mw_ddate_specialday" already exists
        if(!empty($regis)) {
            $list = (Mage::getStoreConfig('ddate/info/special_days')) ? Mage::getStoreConfig('ddate/info/special_days') : "";
            if ($list) {
                $list = trim($list);
                $list = explode(';', $list);
				if(is_array($list)){
					foreach ($list as $key => $date) {
						if ($date) {
							$date = $this->validateDate($date);
							$list[$date] = 1;
						}
						unset($list[$key]);
					}
				}else{
					$date = $this->validateDate($date);
					$list[$date] = 1;
				}
                return $list;
            }else{
                $list = array();
            }
            Mage::register('mw_ddate_specialday', $list);
        }
        
        return Mage::registry('mw_ddate_specialday');
    }

    public function getSaturday() {
        return (Mage::getStoreConfig('ddate/info/deliver_saturdays')) ? Mage::getStoreConfig('ddate/info/deliver_saturdays') : "0";
    }

    public function getSunday() {
        return (Mage::getStoreConfig('ddate/info/deliver_sundays')) ? Mage::getStoreConfig('ddate/info/deliver_sundays') : "0";
    }

    public function getSpecialDayByList() {
        return (Mage::getStoreConfig('ddate/info/special_days')) ? Mage::getStoreConfig('ddate/info/special_days') : "";
    }

    public function getDayoff() {
        return (Mage::getStoreConfig('ddate/info/dayoff')) ? Mage::getStoreConfig('ddate/info/dayoff') : "0";
    }

    public function getMaxBooking() {
        return (Mage::getStoreConfig("ddate/info/maximum_bookings")) ? Mage::getStoreConfig("ddate/info/maximum_bookings") : 10000000;
    }
	
	const MYNAME = "MW_Ddate";		
	function disableConfig()
	{					
			Mage::getModel('core/config')->saveConfig("advanced/modules_disable_output/".self::MYNAME,1);	
			Mage::getConfig()->reinit();
	}
	
    public function validateDate($date){
        preg_match("/^[0-9]{1,2}-[0-9]{1,2}$/", $date, $result, PREG_OFFSET_CAPTURE);
        if(count($result)){
            $currentTime = time();
            $date = date('Y',$currentTime).'-'.$date;
        }
        return $date;
    }	
	/*
	$id  shipping address id
	return array/false (ddate,dtime,dcomment)
	*/
	  public function find_delivery_info($id)
    {
		$adress_ddate=array();
		$ddate=Mage::getSingleton('customer/session')->getDdateinfo();
		//Zend_debug::dump($ddate);		
		if(!empty($ddate)){
		$adress_ddate['ddate']=$ddate['datemultiaddress'.$id];
		$adress_ddate['dtime']=$ddate['dtimemultiaddress'.$id];
		$adress_ddate['mwcomment']=$ddate['ddate_commentmultiaddress'.$id];
		return $adress_ddate;}
		else{
			Mage::log('find_delivery_info function $ddate is empty');
			return false;
		}
		
	}
	/* 
	*$ddate string/datetime 
	*$id  slot's id
	*/
	public function ordered_counting($date = null,$id = null){
		$ordered=0;
       if(empty($date) || empty($id) ) return false;
		$collection= Mage::getSingleton('ddate/ddate')->getCollection()
                ->addFieldToFilter('ddate', array('eq' => $date))
                ->addFieldToFilter('dtime', array('eq' => $id));
		foreach ($collection as $cl){
			$ordered=$ordered + $cl->getOrdered();
		}
		if($ordered > 0) return $ordered;
        return false;
    }

	public function get_config_format(){
	$c_f=Mage::getStoreConfig("ddate/info/formatdate");
	if(empty($c_f)) return 'Ymd';
	else return $c_f;
	}
	/* 
	*
	*return php date format string base on configuration
	*/
	public function php_date_format($sign="-"){		
		$c_f=$this->get_config_format();
		if ($c_f=="mdY") return 'm'.$sign.'d'.$sign.'Y';
		if ($c_f=="dmY") return 'd'.$sign.'m'.$sign.'Y';
		if ($c_f=="Ymd") return 'Y'.$sign.'m'.$sign.'d';
		return "Y-m-d";		
	}
	
	/* 
	*
	*return php date/month format string base on configuration
	*/
	public function month_date_format($sign="/"){		
		$c_f=$this->get_config_format();
		if ($c_f=="mdY") return 'm'.$sign.'j';
		if ($c_f=="dmY") return 'j'.$sign.'m';
		if ($c_f=="Ymd") return 'm'.$sign.'j';
		return "m/j";		
	}
	
	/* 
	*
	*return php date format string base on configuration
	*/
	public function php_date_format_M($sign="-"){		
		$c_f=$this->get_config_format();
		if ($c_f=="mdY") return 'MM'.$sign.'d'.$sign.'Y';
		if ($c_f=="dmY") return 'd'.$sign.'MM'.$sign.'Y';
		if ($c_f=="Ymd") return 'Y'.$sign.'MM'.$sign.'d';
		return "Y-MM-d";		
	}
	
	public function calendar_date_format($sign="-"){		
		$c_f=$this->get_config_format();
		if ($c_f=="mdY") return '%m'.$sign.'%d'.$sign.'%Y';
		if ($c_f=="dmY") return '%d'.$sign.'%m'.$sign.'%Y';
		if ($c_f=="Ymd") return '%Y'.$sign.'%m'.$sign.'%d';
		return "Y-m-d";		
	}
	/**
	Convert date from Ddate'config into yyyy-mm-dd
	$date string
	return date format "yyy-mm-dd" 
	*/
	public function convert_date_format($date=null,$sign="-"){	
		if(empty($date)) return '';
		$c_f=$this->get_config_format();
		$date_ar=explode($sign,$date);
		if ($c_f=="mdY") return $date_ar[2].$sign.$date_ar[0].$sign.$date_ar[1];
		if ($c_f=="dmY") return $date_ar[2].$sign.$date_ar[1].$sign.$date_ar[0];
		if ($c_f=="Ymd") return $date_ar[0].$sign.$date_ar[1].$sign.$date_ar[2];
		return '';		
	}
	/**
	Convert date from yyyy-mm-dd into Ddate config date format
	$date string
	return date format in Ddate config 
	*/
	public function convert_date_format_config($date=null,$sign="-"){	
		if(empty($date)) return '';
		$c_f=$this->get_config_format();
		$date_ar=explode($sign,$date);
		if ($c_f=="mdY") return $date_ar[1].$sign.$date_ar[2].$sign.$date_ar[0];
		if ($c_f=="dmY") return $date_ar[2].$sign.$date_ar[1].$sign.$date_ar[0];
		if ($c_f=="Ymd") return $date_ar[0].$sign.$date_ar[1].$sign.$date_ar[2];
		return '';		
	}
	
	/**
	$date string
	return date format "yyy-mm-dd" 
	*/
	public function format_ddate($date=null){	
		if(empty($date)) return '';
		return date($this->php_date_format(), strtotime($date));		
	}
	

}