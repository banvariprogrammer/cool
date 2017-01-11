<?php 
require 'app/Mage.php';
Mage::app();


if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);
Mage::getSingleton('core/session', array('name' => 'adminhtml'));





$directory =  Mage::getBaseDir('media') .'/Invoice'. DS ;
$results_array = array();
$file = array();
$dateIs = date('Y-m-d h:i:s');
if(is_dir($directory)) {
    if($handle = opendir($directory)){
		
        while($files = readdir($handle)) {
			if(strstr(substr($files,0,1),".")){
				
			} else {
				$file[] = $files;
			}
        }

		if(count($file)>0){
			$I=0;
			foreach($file as $filename)
			{
				$v = $directory.$filename;

				$adminuserId = 1;
				
				$admin_roleid=1;
				$model=Mage::getModel('import/invoice');
				$data1=array('status'=>1,'user_id'=>$adminuserId,'role_id'=>$admin_roleid,'file_path'=>"'".$v."'");
				$model->setData($data1);
				$model->setCreatedOn(now())->setUpdatedOn(now());

				$model->save();
				$curid=$model->getId();
				
				$fileis = @fopen($v,"r");
				while(!feof($fileis))
				{
					
				$line_of_text = fgets($fileis);	

				$type=substr($line_of_text,0,2);
				$data=array();
				

				if($type=='FD')
				{
					$page_num=substr($line_of_text,12,3);
					if($page_num==='000')
					{
						$data['invoice_id']=$model->getEntityId(); 
						$data['record_type']='FDH';
						$data['invoice_number']=substr($line_of_text,2,5);
						$data['customer_number']=substr($line_of_text,17,6);
						$data['order_number']=substr($line_of_text,23,10);
						$data['customer_name']=substr($line_of_text,33,30);
						
						$date=substr($line_of_text,153,8);
						$data['invoice_date']=substr($date,4,4).'-'.substr($date,2,2).'-'.substr($date,0,2);
						
					}
					
					else
					{
						if($page_num!=999)
						{
	
						$data['invoice_id']=$model->getEntityId();
						$data['record_type']='FDD';
						$data['invoice_number']=substr($line_of_text,2,5);
						$data['customer_number']=substr($line_of_text,17,6);
						$data['parent_id']=$fdhid;
						$data['supplier_number']=substr($line_of_text,35,5);
						$data['item_type']=substr($line_of_text,70,1);
						$data['item_number']=substr($line_of_text,73,6);
						$data['units_ordered']=substr($line_of_text,89,7);
						$data['units_supplied']=substr($line_of_text,96,7);
						$data['units_supplied_sign']=substr($line_of_text,103,1);
						
						
						$data['wholesale_price']=number_format(substr($line_of_text,104,7)/100,2);
						$data['extendeded_wholesale_price']=number_format(substr($line_of_text,111,9)/100,2);
						$data['retail_price']=number_format(substr($line_of_text,121,7)/100,2);
						$data['gst_rate']=substr($line_of_text,143,4);
						$data['gst_amount']=number_format(substr($line_of_text,147,7)/100,2);
						$data['gst_extended_amount']=number_format(substr($line_of_text,155,9)/100,2);
						$data['gst_extended_sign']=substr($line_of_text,164,1);
						$data['final_cost_amount']=number_format(substr($line_of_text,194,7)/100,2);
						$data['final_cost_extended_amount']=number_format(substr($line_of_text,202,9)/100,2);
						$data['final_cost_extended_amount_sign']=substr($line_of_text,211,1);
						
						}
					}
				}
				
				if(!empty($data))
				{
				
					$model1 = Mage::getSingleton("import/invoiceitem");
					$model1->setData($data);
					$model1->setCreatedOn(now());
		
					$model1->save();

					if($data['record_type']=='FDH')
					{
						$fdhid = $model1->getId();
					}
				}				
			}
			$destination = Mage::getBaseDir('media') .'/Inserted'. DS ;
			$destination = $destination.$filename;
			fclose($v);
			rename($v,$destination);	
			unlink($v); 
		}	
		echo "Data Inserted Successfully...";
	} else {
		echo "You have no files";
	}
	}
}
