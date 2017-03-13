<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Order_Pdf_Abstract extends Varien_Object
{
    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Item renderers with render type key
     *
     * model    => the model name
     * renderer => the renderer model
     *
     * @var array
     */
    protected $_renderers = array();

    /**
     * Predefined constants
     */
    const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID       = 'sales_pdf/invoice/put_order_id';
    const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID      = 'sales_pdf/shipment/put_order_id';
    const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID    = 'sales_pdf/creditmemo/put_order_id';

    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    protected $_pdf;

    /**
     * Default total model
     *
     * @var string
     */
    protected $_defaultTotalModel = 'sales/order_pdf_total_default';

    /**
     * Retrieve PDF
     *
     * @return Zend_Pdf
     */
    abstract public function getPdf();

    /**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param  string $string
     * @param  Zend_Pdf_Resource_Font $font
     * @param  float $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = '"libiconv"' == ICONV_IMPL ?
            iconv('UTF-8', 'UTF-16BE//IGNORE', $string) :
            @iconv('UTF-8', 'UTF-16BE', $string);

        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;

    }

    /**
     * Calculate coordinates to draw something in a column aligned to the right
     *
     * @param  string $string
     * @param  int $x
     * @param  int $columnWidth
     * @param  Zend_Pdf_Resource_Font $font
     * @param  int $fontSize
     * @param  int $padding
     * @return int
     */
    public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }

    /**
     * Calculate coordinates to draw something in a column aligned to the center
     *
     * @param  string $string
     * @param  int $x
     * @param  int $columnWidth
     * @param  Zend_Pdf_Resource_Font $font
     * @param  int $fontSize
     * @return int
     */
    public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }

    /**
     * Insert logo to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image       = Zend_Pdf_Image::imageWithPath($image);
                $top         = 830; //top border of the page
                $widthLimit  = 270; //half of the page width
                $heightLimit = 270; //assuming the image is not a "skyscraper"
                $width       = $image->getPixelWidth();
                $height      = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width  = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width  = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width  = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
    }

    /**
     * Insert address to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 8);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 815;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $page->drawText(trim(strip_tags($_value)),
                        $this->getAlignRight($_value, 130, 440, $font, 10),
                        $top,
                        'UTF-8');
                    $top -= 10;
                }
            }
        }
        $this->y = ($this->y > $top) ? $top : $this->y;
    }

    /**
     * Format address
     *
     * @param  string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
           
            foreach (Mage::helper('core/string')->str_split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        
        }
        return $return;
    }

    /**
     * Calculate address height
     *
     * @param  array $address
     * @return int Height
     */
    protected function _calcAddressHeight($address)
    {
        $y = 0;
        foreach ($address as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }

    /**
     * Insert order to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $obj
     * @param bool $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 35);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates(array(25, $top, 570, $top - 55));
        $this->_setFontRegular($page, 8);
        //Mage::registry('sr_no');
        Mage::unregister('sr_no');
        //Mage::register('sr_no', 0);
        if ($putOrderId) {
            $page->drawText(
                Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 12), 'UTF-8'
            );
        }
        $page->drawText(
            Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate(
                $order->getCreatedAtStoreDate(), 'medium', false
            ),
            35,
            ($top -= 15),
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        //$page->drawRectangle(25, $top, 275, ($top - 25));
        //$page->drawRectangle(25, $top, 275, ($top - 25));

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
            $shippingMethod  = $order->getShippingDescription();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        
        //$page->drawText(Mage::helper('sales')->__('Sold to:'), 35, ($top - 15), 'UTF-8');
        
        if (!$order->getIsVirtual()) {
            //$page->drawText(Mage::helper('sales')->__('Ship to:'), 285, ($top - 15), 'UTF-8');
        } else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top - 15), 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

       /*  $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));*/
        $this->_setFontRegular($page, 8); 
        $this->y = $top - 10;
        $addressesStartY = $this->y;

        /* foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        } */

        $addressesEndY = $this->y;
        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            $i = 1;
            $j = 0;
            $count = count($shippingAddress);
            $addressconc = "";
            $landflag =1000;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                     
                    }                    
                    if($i == 1){
                    	$page->drawText("Name: ".$_value, 35, $this->y, 'UTF-8');
                    	$this->y -= 15;
                    }else if(strpos($_value,'T:')!== false){
                    	$phone = ltrim($_value,"T:");
                        $j++;

                    }else if(strpos($_value,'Landmark:')!= false){
                         $land = ltrim($_value,"<br/>");
                          $landflag = $i;
                         $j++;
                    }
                    else if( $landflag<$i) {
                      $land .= ltrim($_value,"<br/>");
                      

                    }else{
	                    foreach ($text as $part) {
                           
                           $addressconc .= " ".strip_tags(ltrim($part));
	                      
	                    }
                    }  
                                    
                    if($i == $count -1){                         
                        $addressSplit = str_split($addressconc,125);                   
                     
                       $k =0;
                       foreach ($addressSplit as $key => $value) {
                            if($k==0) {
                                 $page->drawText("Address:".$value, 35, $this->y, 'UTF-8');
                                 $this->y -= 15;   
                            }
                            else {
                                $page->drawText($value, 35, $this->y, 'UTF-8');
                                $this->y -= 15;
                            }
                            $k++;
                       }     
                    }
                }
                $i++;
            }
            if($phone) {
                $page->drawText("Phone: ".$phone, 35, $this->y, 'UTF-8');
                $this->y -= 15;
            }
            if($land) {
                 $landSplit = str_split($land,125);      
                     foreach ($landSplit as $key => $value) {                            
                         $page->drawText($value, 35, $this->y, 'UTF-8');
                         $this->y -= 15;   
                      
                   }  
             }
            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            //$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            //$page->setLineWidth(0.5);
            //$page->drawRectangle(25, $this->y, 275, $this->y-25);
            //$page->drawRectangle(275, $this->y, 570, $this->y-25);

            //$this->y -= 15;
            //$this->_setFontBold($page, 12);
            //$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            //$page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
            //$page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y , 'UTF-8');

            //$this->y -=10;
            //$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

            //$this->_setFontRegular($page, 10);
            //$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments   = $this->y - 5;
        }
        else {
            $yPayments   = $addressesStartY - 5;
            $paymentLeft = 285;
        }

        foreach ($payment as $value){
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $page->drawText("Mode of Payment: ".strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                }
            }
        }   

        $ddate = Mage::getResourceModel('ddate/ddate')->getDdateByOrder($order->getRealOrderId());
	    if($ddate){
              $deliverydate = date('Y-m-j', strtotime($ddate['ddate'])).' '; 
              if(isset($ddate['dtimetext'])){ 
                  $deliverydate .= $ddate['dtimetext']; 
              }
              $page->drawText("Delivery Date: ".strip_tags($deliverydate), 300, $yPayments, 'UTF-8');
              $yPayments -= 15;
        }
        
        $total = $order->getGrandTotal();
        $page->drawText("Total: ".str_replace("₹","Rs. ",$order->formatPriceTxt($total)), 35, $this->y - 20, 'UTF-8');
        //$this->y -= 30;
         $orderid = $order->getRealOrderId(); 
 
            $foodcouponModel = Mage::getModel('foodcoupon/foodcoupon');
                   $amount = $foodcouponModel->getFoodcouponAmount($orderid);
            if($amount>0) {

                $msg ="Eligible Food Coupon Amount: Rs. $amount";
            $page->drawText(
                $msg, 300,$this->y - 20, 'UTF-8');
           
            }
        
        $connection = Mage::getSingleton('core/resource')
        ->getConnection('core_read');
        
        $select = $connection->select()
        ->from('vs_reward_instruction', array('*')) // select * from tablename or use array('id','name') selected values
        ->where('order_id=?',$order->getRealOrderId()) ;             // where id =1
        
        $rowArray =$connection->fetchRow($select);
        
        if(isset($rowArray['reward_instruction']) && ($rowArray['reward_instruction'] != "" && $rowArray['reward_instruction'] != NULL)){
        	
        	$page->drawText("Loyalty Program:", 35, $this->y - 35, 'UTF-8');
        	
        	if( $rowArray['reward_instruction'] == "Create a new Loyalty Card & Add this transaction to above contact number." || $rowArray['reward_instruction'] == "Add this transaction to above contact number.")
        	{ 	
        		
	        	$page->drawText("Loyalty Program ID: New Customer", 35, $this->y - 50, 'UTF-8');
	        	$page->drawText("Contact Number: ".$rowArray['mobile_no'], 235, $this->y - 50, 'UTF-8');
	        	$page->drawText("Instructions:", 35, $this->y - 65, 'UTF-8');
	        	$page->drawText(strip_tags($rowArray['reward_instruction']), 35, $this->y - 80, 'UTF-8');
	        	
        	}else{
        		
        		$selectloyaltycard = $connection->select()
        		->from('vs_reward_program', array('*')) // select * from tablename or use array('id','name') selected values
        		->where('contact_number=?',$rowArray['mobile_no']) ;             // where id =1
        		
        		$rowloyaltycard =$connection->fetchRow($selectloyaltycard);
        		
        		$page->drawText("Loyalty Program ID: ".$rowloyaltycard['customer_id'], 35, $this->y - 50, 'UTF-8');
        		$page->drawText("Contact Number: ".$rowArray['mobile_no'], 235, $this->y - 50, 'UTF-8');
        		$page->drawText("Instructions:", 35, $this->y - 65, 'UTF-8');
        		$page->drawText(strip_tags($rowArray['reward_instruction']), 35, $this->y - 80, 'UTF-8');
        		
        	}
        	
        }
        
        $this->y -= 95;
        
       /*  if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25,  ($top - 25), 25,  $yPayments);
            $page->drawLine(570, ($top - 25), 570, $yPayments);
            $page->drawLine(25,  $yPayments,  570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin    = 15;
            $methodStartY = $this->y;
            $this->y     -= 15;

            foreach (Mage::helper('core/string')->str_split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " "
                . $order->formatPriceTxt($order->getShippingAmount()) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = array();
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode != 'custom') {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    } else {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, 292, $yShipments , 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments , 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25,  $methodStartY, 25,  $currentY); //left
            $page->drawLine(25,  $currentY,     570, $currentY); //bottom
            $page->drawLine(570, $currentY,     570, $methodStartY); //right

            $this->y = $currentY;
            $this->y -= 15;
        } */
    }

    /**
     * Insert title and number for concrete document type
     *
     * @param  Zend_Pdf_Page $page
     * @param  string $text
     * @return void
     */
    public function insertDocumentNumber(Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 8);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 35, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * Sort totals list
     *
     * @param  array $a
     * @param  array $b
     * @return int
     */
    protected function _sortTotalsList($a, $b) {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return ($a['sort_order'] > $b['sort_order']) ? 1 : -1;
    }

    /**
     * Return total list
     *
     * @param  Mage_Sales_Model_Abstract $source
     * @return array
     */
    protected function _getTotalsList($source)
    {
        $totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();
        usort($totals, array($this, '_sortTotalsList'));
        $totalModels = array();
        foreach ($totals as $index => $totalInfo) {
            if (!empty($totalInfo['model'])) {
                $totalModel = Mage::getModel($totalInfo['model']);
                if ($totalModel instanceof Mage_Sales_Model_Order_Pdf_Total_Default) {
                    $totalInfo['model'] = $totalModel;
                } else {
                    Mage::throwException(
                        Mage::helper('sales')->__('PDF total model should extend Mage_Sales_Model_Order_Pdf_Total_Default')
                    );
                }
            } else {
                $totalModel = Mage::getModel($this->_defaultTotalModel);
            }
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    /**
     * Insert totals to pdf page
     *
     * @param  Zend_Pdf_Page $page
     * @param  Mage_Sales_Model_Abstract $source
     * @return Zend_Pdf_Page
     */
    protected function insertTotals($page, $source){
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $lineBlock = array(
            'lines'  => array(),
            'height' => 15
        );
        foreach ($totals as $total) {
            $total->setOrder($order)
                ->setSource($source);

            if ($total->canDisplay()) {
                //$total->_setFontRegular($page, "10"); 
                $total->setFontSize(8);
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    $lineBlock['lines'][] = array(
                        array(
                            'text'      => $totalData['label'],
                            'feed'      => 475,
                            'align'     => 'right',
                            'font_size' => $totalData['font_size'],
                            'font'      => 'bold'
                        ),
                        array(
                            'text'      => str_replace("₹","Rs. ",$totalData['amount']),
                            'feed'      => 565,
                            'align'     => 'right',
                            'font_size' => $totalData['font_size'],
                            'font'      => 'bold'
                        ),
                    );
                }
            }
        }

        $this->y -= 20;
        $page = $this->drawLineBlocks($page, array($lineBlock));
        
        
        
        $page->drawLine(25, $this->y-30, 35, $this->y-30);
        $page->drawText(Mage::helper('sales')->__('cut here'), 40, $this->y-30, 'UTF-8');
        $page->drawLine(70, $this->y-30, $page->getWidth()-75, $this->y-30);
        $page->drawText(Mage::helper('sales')->__('cut here'), $page->getWidth()-70, $this->y-30, 'UTF-8');
        $page->drawLine($page->getWidth()-40, $this->y-30, $page->getWidth()-25, $this->y-30);
        
        $page->drawText(Mage::helper('sales')->__('Order #:'), 25, $this->y-50, 'UTF-8');
        $page->drawText($order->getRealOrderId(), 55, $this->y-50, 'UTF-8');
        
        
        $page->drawText(Mage::helper('sales')->__('Returned Items:'), 25, $this->y-60, 'UTF-8');
        $page->drawLine(25, $this->y-65, $page->getWidth()-25, $this->y-65);
        $page->drawText(Mage::helper('sales')->__('S.No'), 25, $this->y-77, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Product Name'), 250, $this->y-77, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Price'), 500, $this->y-77, 'UTF-8');
        $page->drawLine(25, $this->y-85, $page->getWidth()-25, $this->y-85);
        $page->drawLine(25, $this->y-160, $page->getWidth()-25, $this->y-160);
        $page->drawText(Mage::helper('sales')->__("Customer's Signaure"), 480, $this->y-185, 'UTF-8');
        
        return $page;
    }

    /**
     * Parse item description
     *
     * @param  Varien_Object $item
     * @return array
     */
    protected function _parseItemDescription($item)
    {
        $matches = array();
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return array($description);
    }

    /**
     * Before getPdf processing
     */
    protected function _beforeGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
    }

    /**
     * After getPdf processing
     */
    protected function _afterGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    }

    /**
     * Format option value process
     *
     * @param  array|string $value
     * @param  Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _formatOptionValue($value, $order)
    {
        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return  $resultValue;
        } else {
            return $value;
        }
    }

    /**
     * Initialize renderer process
     *
     * @param string $type
     */
    protected function _initRenderer($type)
    {
        $node = Mage::getConfig()->getNode('global/pdf/' . $type);
        foreach ($node->children() as $renderer) {
            $this->_renderers[$renderer->getName()] = array(
                'model'     => (string)$renderer,
                'renderer'  => null
            );
        }
    }

    /**
     * Retrieve renderer model
     *
     * @param  string $type
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    protected function _getRenderer($type)
    {
        if (!isset($this->_renderers[$type])) {
            $type = 'default';
        }

        if (!isset($this->_renderers[$type])) {
            Mage::throwException(Mage::helper('sales')->__('Invalid renderer model'));
        }

        if (is_null($this->_renderers[$type]['renderer'])) {
            $this->_renderers[$type]['renderer'] = Mage::getSingleton($this->_renderers[$type]['model']);
        }

        return $this->_renderers[$type]['renderer'];
    }

    /**
     * Public method of protected @see _getRenderer()
     *
     * Retrieve renderer model
     *
     * @param  string $type
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function getRenderer($type)
    {
        return $this->_getRenderer($type);
    }

    /**
     * Render item
     *
     * @param Varien_Object $item
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Pdf_Items_Abstract $renderer
     *
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    public function renderItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order, $renderer)
    {
        $renderer->setOrder($order)
            ->setItem($item)
            ->setPdf($this)
            ->setPage($page)
            ->setRenderedModel($this)
            ->draw();

        return $this;
    }

    /**
     * Draw Item process
     *
     * @param  Varien_Object $item
     * @param  Zend_Pdf_Page $page
     * @param  Mage_Sales_Model_Order $order
     * @return Zend_Pdf_Page
     */
    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
    {
        $orderItem = $item->getOrderItem();
        $type = $orderItem->getProductType();
        $renderer = $this->_getRenderer($type);

        $this->renderItem($item, $page, $order, $renderer);

        $transportObject = new Varien_Object(array('renderer_type_list' => array()));
        Mage::dispatchEvent('pdf_item_draw_after', array(
            'transport_object' => $transportObject,
            'entity_item'      => $item
        ));

        foreach ($transportObject->getRendererTypeList() as $type) {
            $renderer = $this->_getRenderer($type);
            if ($renderer) {
                $this->renderItem($orderItem, $page, $order, $renderer);
            }
        }

        return $renderer->getPage();
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font =  Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);//Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);//Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);//Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set PDF object
     *
     * @param  Zend_Pdf $pdf
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        return $page;
    }

    /**
     * Draw lines
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param  Zend_Pdf_Page $page
     * @param  array $draw
     * @param  array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines  = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = 7;//empty($column['font_size']) ? 10 : $column['font_size'];
                    //echo $column['font_file'];
                    if (!empty($column['font_file'])) {                        
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);                        
                        $page->setFont($font, $fontSize);
                    } else {
                        //echo $column['font'];
                        //$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        //$page->setFont($font, $fontSize);
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        //echo $fontStyle."test"; exit;
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                        //$font = $this->_setFontRegular($page, $fontSize); 
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = 20;//!empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < 15) {
                            $page = $this->newPage($pageSettings);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                }
                                else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        if($part != "Grand Total:"){
                            //$page->drawRectangle(25, $this->y-$top-8, $page->getWidth()-25, $this->y-$top+11, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                            $page->drawText($part, $feed, $this->y-$top, 'UTF-8');
                            //$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                            //$page->setLineWidth(0.5);                        
                            //$page->drawLine($feed - 20, $this->y-$top+12, $feed - 20, $this->y-$top-8);
                            $page->drawLine(25, $this->y-$top-8, $page->getWidth()-25, $this->y-$top-8);
                            $top += $lineSpacing;
                        }
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }
}
