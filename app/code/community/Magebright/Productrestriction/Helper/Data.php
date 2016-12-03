<?php
class Magebright_Productrestriction_Helper_Data extends Mage_Core_Helper_Abstract
{
     public function getProductrestrictionData($productrestriction_id)
	{
		$valArray = $productrestriction_id;
		$collection = Mage::getModel('productrestriction/productrestriction')->getCollection()
		->addFieldToFilter('productrestriction_id',$productrestriction_id);

		return $collection;
	}
    public function checkProductrestrictionData($zipcode,$productId)
    {
		$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
		$collection->addFieldToFilter('pin_code',$zipcode);
		$collection->getSelect()->limit(1);
		$zipcodedata=$collection->getData();

		$invalidproduct=0;
		$response = array();

	    // print_r($zipcodedata);
		
		if(count($productId)>0 && $collection->getSize()>0)
		{
		 	/*$productArray=explode(',',$zipcodedata[0]['product_id']);

			foreach ($productId as $prodval){
			 if(!in_array($prodval,$productArray)){
			 	$invalidproduct.=$prodval.',';
			 }
			} */
            $invalidproduct= $this->checkProductZipcode($zipcode,$productId);
		}
		
		if($invalidproduct=='')
		{
			if($collection->getSize()>0)
			{
				foreach($collection as $zipdata)
				{
					$DeliveryDays = $zipdata->getDeliveryDays();
					$city = $zipdata->getCity();
					$cashod= $zipdata->getCod();
				}
				$response['valid'] = 1;
				$response['Delivery_Days'] = $DeliveryDays;
				$response['city'] = $city;
				$response['cod-valid'] = $cashod;
				if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
				$response['cod'] = $cmsg;
			}
			else
			{
				$flag=0;
				/*Check for * validation */
				$key="*";
				$starcollection = Mage::getModel('productrestriction/productrestriction')->getCollection();
				//$collection->addFieldToFilter('pin_code',$zipcode);
				$starcollection->addFieldToFilter('pin_code',array('like'=>'%'.$key.'%'));

				if($starcollection->getSize()>0)
				{
					foreach($starcollection as $zipdata)
					{
		   				$COD = $zipdata->getPinCode();
		 				$starpos=stripos($COD,"*");
	   					$admincode=substr($COD,0,$starpos);
	  					$frontcode=substr($zipcode,0,$starpos);
						if($admincode  == $frontcode)
						{
				  			$invalidproduct= $this->checkProductZipcode($COD,$productId);
							if($invalidproduct=='')
							{
								$DeliveryDays = $zipdata->getDeliveryDays();
								$FINALCOD = $zipdata->getCity();
								$cashod=$zipdata->getCod();
								$response['valid'] = 1;
								$response['Delivery_Days'] = $DeliveryDays;
								$response['city'] = $FINALCOD;
								$response['cod-valid'] = $cashod;
								if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
								$response['cod'] = $cmsg;
								$flag=1;
								break;
							}
						}
					}
				}
				/*Check for – validation */
				if($flag==0)
				{
					$key='-';
					$descollection = Mage::getModel('productrestriction/productrestriction')->getCollection();
					//$collection->addFieldToFilter('pin_code',$zipcode);
					$descollection->addFieldToFilter('pin_code',array('like'=>'%'.$key.'%'));
					if($descollection->getSize()>0)
					{
						foreach($descollection as $szipdata)
						{
							$zipcodestr = $szipdata->getPinCode();
							$desadmincode = explode("-", $zipcodestr);
							$desstart=$desadmincode[0];
							$desend=$desadmincode[1];
							if($zipcode >= $desstart &&  $zipcode <= $desend)
							{
				  				$invalidproduct= $this->checkProductZipcode($zipcodestr,$productId);
			  					if($invalidproduct=='')
			  					{
									$des_DeliveryDays = $szipdata->getDeliveryDays();
									$DES_FINALCOD = $szipdata->getCity();
									$cashod=$szipdata->getCod();
									$response['valid'] = 1;
									$response['Delivery_Days'] = $des_DeliveryDays;
									$response['city'] = $DES_FINALCOD;
									$response['cod-valid'] = $cashod;
									if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
									$response['cod'] = $cmsg;
									$flag=1;
									break;
					  			}
							}
						}
					}
				}
				if($flag==0)
				{
					$response['valid'] = 0;
					$response['cod-valid'] = 0;
			        $response['invalid-product']=$invalidproduct;
				}
			}
		}
		else
		{
			$response['valid'] = 0;
			$response['cod-valid'] = 0;
			$response['invalid-product']=$invalidproduct;
		}
		return $response;
    }

    public function checkoutProductrestrictionData($zipcode,$productId)
    {
    	$checkoutresult = array();
    	foreach ($productId as $prodval)
    	{
    		$checkoutresult[] = Mage::helper('productrestriction')->checkSingleProductrestrictionData($zipcode,$prodval);
    	}
    	return $checkoutresult;
    }

    public function checkSingleProductrestrictionData($zipcode,$productId)
    {
		if (ctype_digit($zipcode)) 
    	{
    		$allzipcodeid = "*"; 
			//here * represents that product ids having this as pincode are shipped to all other pincodes that exist in the system

			// Load the collection of the entered pincode
			$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
			$collection->addFieldToFilter('pin_code',$zipcode);
			$collection->getSelect()->limit(1);

			$invalidproduct=0;
			$response = array();

			//check if product id exists with * pincode in zipcodeproduct table collection
			$check = Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',$allzipcodeid)->addFieldToSelect('product_id');
			$checkproductids = $check->getData();
			foreach ($checkproductids as $checkproductid) 
			{
				if ($checkproductid['product_id'] == $productId) 
				{
					// Now Product is shipped to all pincodes in the system as its id is in * pincode. To check if the entered pincode exists in the system(serviceable pincode), check the loaded collection size of entered pincode
					if($collection->getSize()>0)
					{
						//set the response data for the found pincode
						foreach($collection as $zipdata)
						{
							$DeliveryDays = $zipdata->getDeliveryDays();
							$city = $zipdata->getCity();
							$cashod= $zipdata->getCod();
						}
						$response['valid'] = 1;
						$response['Delivery_Days'] = $DeliveryDays;
						$response['city'] = $city;
						$response['cod-valid'] = $cashod;
						if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
						$response['cod'] = $cmsg;
					}
					else
					{
						//if collection is not loaded for entered pincode, check with range condition i.e; entered pincode is part of a range and not individual entry in the system
						$flag=0;
						$key='-';
						$descollection = Mage::getModel('productrestriction/productrestriction')->getCollection();
						$descollection->addFieldToFilter('pin_code',array('like'=>'%'.$key.'%'));
						if($descollection->getSize()>0)
						{
							foreach($descollection as $szipdata)
							{
								$zipcodestr = $szipdata->getPinCode();
								$desadmincode = explode("-", $zipcodestr);
								$desstart=$desadmincode[0];
								$desend=$desadmincode[1];
								if($zipcode >= $desstart &&  $zipcode <= $desend)
								{
									//set the response data from the found pincode range data
									$des_DeliveryDays = $szipdata->getDeliveryDays();
									$DES_FINALCOD = $szipdata->getCity();
									$cashod=$szipdata->getCod();
									$response['valid'] = 1;
									$response['Delivery_Days'] = $des_DeliveryDays;
									$response['city'] = $DES_FINALCOD;
									$response['cod-valid'] = $cashod;
									if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
									$response['cod'] = $cmsg;
									$flag=1;
									break;
								}
							}
						}
						if($flag==0)
						{	
							//if entered pincode is not found in pincodes as range also then return not valid result
							$invalidproduct = $productId;
							$response['valid'] = 0;
							$response['cod-valid'] = 0;
					        $response['invalid-product']=$invalidproduct;
						}
					}
					//skip further checks and return result
					goto end;
				}
			}
			
			//if no match is found with * pincode, check with rest of individual or range serviceable pincodes
			if($collection->getSize()>0)
			{
	            $invalidproduct= $this->checkProductZipcode($zipcode,$productId);

	            if($invalidproduct=='')
				{
					// No invalid product is returned, Product is serviceable to the entered pincode, return pincode data
					foreach($collection as $zipdata)
					{
						$DeliveryDays = $zipdata->getDeliveryDays();
						$city = $zipdata->getCity();
						$cashod= $zipdata->getCod();
					}
					$response['valid'] = 1;
					$response['Delivery_Days'] = $DeliveryDays;
					$response['city'] = $city;
					$response['cod-valid'] = $cashod;
					if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
					$response['cod'] = $cmsg;
				}
				else
				{
					// invalid product is returned, return not valid result
					$response['valid'] = 0;
					$response['cod-valid'] = 0;
					$response['invalid-product']=$invalidproduct;
				}
			}
			else
			{
				//if collection is not loaded for entered pincode, check with range condition i.e; entered pincode is part of a range and not individual entry in the system
				$flag=0;
				$key='-';
				$descollection = Mage::getModel('productrestriction/productrestriction')->getCollection();
				$descollection->addFieldToFilter('pin_code',array('like'=>'%'.$key.'%'));
				if($descollection->getSize()>0)
				{
					foreach($descollection as $szipdata)
					{
						$zipcodestr = $szipdata->getPinCode();
						$desadmincode = explode("-", $zipcodestr);
						$desstart=$desadmincode[0];
						$desend=$desadmincode[1];
						if($zipcode >= $desstart &&  $zipcode <= $desend)
						{
							//check if product id exists with range pincode collection
			  				$invalidproduct= $this->checkProductZipcode($zipcodestr,$productId);
		  					if($invalidproduct=='')
		  					{
		  						// No invalid product is returned, Product is serviceable to the range pincode, return range pincode data for the entered pincode
								$des_DeliveryDays = $szipdata->getDeliveryDays();
								$DES_FINALCOD = $szipdata->getCity();
								$cashod=$szipdata->getCod();
								$response['valid'] = 1;
								$response['Delivery_Days'] = $des_DeliveryDays;
								$response['city'] = $DES_FINALCOD;
								$response['cod-valid'] = $cashod;
								if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
								$response['cod'] = $cmsg;
								$flag=1;
								break;
				  			}
						}
					}
				}
				if($flag==0)
				{	
					//if entered pincode is not found in pincodes as range also then return not valid result
					$invalidproduct = $productId;
					$response['valid'] = 0;
					$response['cod-valid'] = 0;
			        $response['invalid-product']=$invalidproduct;
				}
			}
		}
		else
		{
			$invalidproduct = $productId;
			$response['valid'] = 0;
			$response['cod-valid'] = 0;
	        $response['invalid-product']=$invalidproduct;
		}
		end:
		return $response;
    }
    
    public function checkCOD($zipcode)
    {
    	$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
		$collection->addFieldToFilter('pin_code',$zipcode)->addFieldToFilter('cod',1);
		$collection->getSelect()->limit(1);
		$collection->getSize();
		if($collection->getSize()>0) { return 1; }
		else { return 0; }
    }

    public function checkProductZipcode($zipcode,$productId)
    {
       	$zipcode_product = Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',$zipcode)->addFieldToSelect('product_id');
       	$checkedproductsids = $zipcode_product->getData();

		$isProduct=0;
    	$validproduct=array();
		$invalidproduct='';
		if($zipcode_product->getSize()>0)
		{
    		foreach ($checkedproductsids as $validpro)
    		{
			 	$validproduct[]=$validpro['product_id'];
			}
		}

    	if(!in_array($productId,$validproduct))
		{
		 	$isProduct++;
		 	$invalidproduct = $productId;
		}

      	return  $invalidproduct;
    }

    public function checkZipcodeForStore($zipcode)
    {

    	$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
		$collection->addFieldToFilter('pin_code',$zipcode);
		$collection->getSelect()->limit(1);
		$storeId = Mage::app()->getStore()->getStoreId();
		$data=$collection->getData();
		$default_store=explode(',',$data[0]['store_id']);
		
		if(in_array($storeId,$default_store))
		{
			return  1; 
		}
		else
		{ 
			return  0;
	    }
    }

    public function deleteProductZipcode($zipcode)
    {
		$collection_del= Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',array('eq'=>$zipcode));
		$collection_del->walk('delete');
    }
}