<?php
    $store_id = 1;
    $magento_path = __DIR__;
    require "{$magento_path}/app/Mage.php";

    Mage::app()->setCurrentStore($store_id);

//this builds a collection that's analagous to 
//select * from products where image = 'no_selection'
$products = Mage::getModel('catalog/product')
->getCollection()
->addAttributeToSelect('*')
->addAttributeToFilter('image', 'no_selection')
->addAttributeToFilter('type_id', 'simple');

foreach($products as $product)
{
    echo  $product->getSku() . "\n<br />\n";
    //var_dump($product->getData()); //uncomment to see all product attributes
                                     //remove ->addAttributeToFilter('image', 'no_selection');
                                     //from above to see all images and get an idea of
                                     //the things you may query for
}       

echo "**********Listing finished*******\n";