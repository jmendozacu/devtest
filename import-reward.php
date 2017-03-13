<p><strong>Please note that import is starting from second row of CSV. <br/>Please have .CSV with header Customer Id, Contact Number, Bill Amount(INR).</strong></p>
<form action="" enctype="multipart/form-data" method="post">
<input type="file" name="importreward" />
<input type="submit" name="submit" value="Submit" />
</form>
<?php
if(isset($_POST['submit'])){
	if($_FILES["importreward"]["name"] != ""){
		if($_FILES["importreward"]["type"] == "text/csv" || $_FILES["importreward"]["type"] == "application/vnd.ms-excel"){		
		
			$filename = $_FILES["importreward"]["name"];
			move_uploaded_file($_FILES["importreward"]["tmp_name"], $filename);
			
			set_time_limit(0);
			ini_set('memory_limit', '1024M');
			include_once "app/Mage.php";
			include_once "downloader/Maged/Controller.php";
			
			Mage::init();
			
			$app = Mage::app('default');
			$row = 0;
			
			$read= Mage::getSingleton('core/resource')->getConnection('core_read');
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			
			if (($handle = fopen($filename, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					
					$row++;
			
					if($row == 1) continue;
					
					$value = $read->query("SELECT count(*) as numrows from vs_reward_program WHERE contact_number = '{$data[1]}'");
					$row = $value->fetch();
					
					if($row['numrows'] == 0){
						
						$sql = "INSERT INTO vs_reward_program(customer_id, contact_number, bill_amount) VALUES ('{$data[0]}','{$data[1]}','{$data[2]}')";
						$write->query($sql);		

						echo "Customer ID {$data[0]} has been inserted with contact number as {$data[1]} and bill amount as {$data[2]}<br/>";
						
					}else{

						if($row['numrows'] == 1){
							
							$valuec = $read->query("SELECT customer_id from vs_reward_program WHERE contact_number = '{$data[1]}'");
							$rowc = $valuec->fetch();
							
							if($rowc['customer_id'] == 0){
								
								$upsql = "UPDATE vs_reward_program SET customer_id = '{$data[0]}',
										bill_amount = '{$data[2]}', redemption_flag = '0' WHERE contact_number = '{$data[1]}'";			
								$write->query($upsql);
								
								echo "Contact number {$data[1]} has been assigned to customer id {$data[0]} and bill amount as {$data[2]} <br/>";
								
							}else{
								
								$upsqlbill = "UPDATE vs_reward_program SET bill_amount = '{$data[2]}', redemption_flag = '0'
								WHERE contact_number = '{$data[1]}'";
								$write->query($upsqlbill);
								echo "Contact number {$data[1]} has been updated with bill amount as {$data[2]}<br/>";
																
							}						
							
						
						}elseif($row['numrows'] > 1){
							
							echo "The contact number - {$data[1]} is associated with more than one customer. Please check and associate it.<br/>";
							
						}
						
					}
					
				}
			}
			
			unlink($filename);
					
		}
	
	}

}

?>