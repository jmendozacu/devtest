<?php
    $store_id = 1;
    $csv_filepath = "subscribers.csv";
    $csv_delimiter = ',';
    $csv_enclosure = '"';
    $magento_path = __DIR__;
    require "{$magento_path}/app/Mage.php";

    Mage::app()->setCurrentStore($store_id);
    echo "<pre>";
    $fp = fopen($csv_filepath, "r");

    if (!$fp) die("{$csv_filepath} not found\n");
    $count = 0;

    while (($row = fgetcsv($fp, 0, $csv_delimiter, $csv_enclosure)) !== false){
        if ($count != 0){

            $email = trim($row[1]);
            $type = trim($row[2]);
            $fname = trim($row[3]);
            $lname = trim($row[4]);
            $status = trim($row[5]);
            $website = trim($row[6]);
            $store = trim($row[7]);
            $store_view = trim($row[8]);

            if (strlen($email) == 0) continue;
            echo "$email";
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if ($subscriber->getId()){
                echo $email . " <b>already subscribed</b>\n";
                continue;
                }

            Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
            $subscriber_status = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

            if ($status == 1){
                  $subscriber_status->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                  $subscriber_status->save();
                }else if($status == 2){
                  $subscriber_status->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
                  $subscriber_status->save();
                }else if($status == 3){
                  $subscriber_status->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                  $subscriber_status->save();
                }else if($status == 4){
                  $subscriber_status->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
                  $subscriber_status->save();
                }
                echo $email . " <b>ok</b>\n";
            }

        $count++;

        }

echo "Import finished\n";