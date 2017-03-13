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
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    /**
     * Draw item line
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        $prod = Mage::getModel('catalog/product')->load($item->getProductId());        
        //$manufacturer = $prod->getManufacturer(); 
        $manufacturer = $prod->getAttributeText('manufacturer');
       
		$var_srno = Mage::registry('sr_no');
		if($var_srno!=0)
		{
			$new_sr = $var_srno + 1;
			$sr_no = $new_sr;
			Mage::unregister('sr_no');
		}
		else{
			$new_sr = 1;
		}		
		//if()		
		Mage::register('sr_no', $new_sr);


        // draw S.No
        $lines[0] = array(array(
            'text' => $new_sr,
            'feed' => 35,
        ));
        // draw Brand
        $lines[0][] = array(
            'text' => strtoupper($manufacturer),
            'feed' => 70,
        );

        // draw Product name
        $lines[0][] = array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), 50, true, true),
            'feed' => 150,
        );

        // draw SKU
        /* $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 17),
            'feed'  => 290,
            'align' => 'right'
        ); */

        // custom options
        
        $packsize = "";
        $originaldata = $item->getOrigData();
        $_productSku = $originaldata['sku'];
        $_productId = Mage::getModel('catalog/product')->getIdBySku($_productSku);
        $product = Mage::getModel('catalog/product')->load($_productId);
        $packsize1 = $product->getResource()->getAttribute('packsize')->getFrontend()->getValue($product);
        $packsize2 =$product->getResource()->getAttribute('liquids')->getFrontend()->getValue($product);
        $packsize1 = ($packsize1!="No")?$packsize1:"";
        $packsize2 = ($packsize2!="No")?$packsize2:"";
        $packsize = $packsize1.$packsize2;
        if($packsize)
        {
        
        	//echo $packsize;
        	//	$packsize = "";
        }
        
        $packandqty = $packsize. " x ". $item->getQty() * 1;
        
        $lines[0][] = array(
        		'text' => $packandqty,//Mage::helper('core/string')->str_split($packsize, 30, true, true),
        		'feed' => 380
        );
        
        // draw QTY
        /* $lines[0][] = array(
            'text'  => $item->getQty() * 1,
            'feed'  => 430,
            'align' => 'right'
        ); */


        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 465;
        $feedSubtotal = 515;
        foreach ($prices as $priceData){
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => $feedPrice,
                    'align' => 'left'
                );
                // draw Subtotal label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => $feedSubtotal,
                    'align' => 'left'
                );
                $i++;
            }
            
            $price = str_replace("₹","Rs. ",$priceData['price']);
            // draw Price
            $lines[$i][] = array(
                'text'  => $price,
                'feed'  => $feedPrice,
                'font'  => 'bold',
                'align' => 'left'
            );
            // draw Subtotal
            $lines[$i][] = array(
                'text'  => str_replace("₹","Rs. ",$priceData['subtotal']),
                'feed'  => $feedSubtotal,
                'font'  => 'bold',
                'align' => 'left'
            );
            $i++;
        }

        // draw Tax
        /* $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 495,
            'font'  => 'bold',
            'align' => 'right'
        ); */

        
        /*
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label

                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $_printValue = $option['print_value'];
                    } else {
                        $_printValue = strip_tags($option['value']);
                    }
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[0][] = array(
                            'text' => Mage::helper('core/string')->str_split($packsize, 30, true, true),
                            'feed' => 380
                        );
                    }
                }
            }
        }
       */
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
