<?php

class Magebright_Productrestriction_Model_Import extends Mage_Core_Model_Config_Data
{ 

    protected function _afterSave()
    {
        set_time_limit(60000); 
        ini_set('max_execution_time', 60000);
        ini_set('memory_limit','512M');
       
        
        if (empty($_FILES['groups']['tmp_name']['export_import']['fields']['upload_productrestriction']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['export_import']['fields']['upload_productrestriction']['value'];

        $this->_importedRows        = 0;

        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
		$hlp = Mage::helper('productrestriction');
        $zipcodearray=array();
        $resource = Mage::getSingleton('core/resource');  
        $tablename= $resource->getTableName('productrestriction');
        $model = Mage::getModel('productrestriction/productrestriction');
        $writeConnection = $resource->getConnection('core_write');
             
        try { 
            $rowNumber  = 0;
            $rowNumberCount  = 0;
            $pkj  = 0;
            $query = " values ";
            $importData = array();
            $this->deletePrevious();

            
           while (false !== ($csvLinecount = $io->streamRead())) { 
              $rowNumberCount ++;                
           }    
           $rowNumberCount = $rowNumberCount-1;     
            
           $io->streamClose();
           
           $io->streamOpen($info['basename'], 'r');    
           
            while (false !== ($csvLine = $io->streamRead())) {
           
                $rowNumber ++;
                
                $pkj++;
                if($rowNumber!=1):
                $csvLinedata = explode(',',$csvLine);
                $actual_data = $csvLinedata;
                $isExist=0;
               $zipcode = trim(preg_replace('/\s+/',' ', $actual_data[0]));

              if(in_array($zipcode,$zipcodearray)){
                  $isExist=1;
                } else{
                   $zipcodearray[]=$zipcode;
                }
              if($isExist==0){

                $new_data = array();
                $new_data['pin_code'] =$zipcode;// trim(preg_replace('/\s+/',' ', $actual_data[0]));
                $new_data['city'] = str_replace('"','',trim(preg_replace('/\s+/',' ', $actual_data[1])));
               
                $new_data['delivery_days'] = str_replace('"','',trim(preg_replace('/\s+/',' ', $actual_data[2])));
                $new_data['cod'] = trim(preg_replace('/\s+/',' ', $actual_data[3]));
                $query .= " (
                    '".$new_data['pin_code']."',
                                '".$new_data['city']."',
                                          '".$new_data['delivery_days']."',
                                          	'".$new_data['cod']."'),";
                }
                if($rowNumberCount<500):

                    if($rowNumber==$rowNumberCount+1):
          			    $final_query = "INSERT into ".$tablename." (pin_code,city,delivery_days,cod) ".trim($query,',');
                        $writeConnection->query($final_query);
                        $query = " values ";
                        $pkj = 0;
                    endif;

                elseif($rowNumber>($rowNumberCount-500)):
                    if($rowNumber==$rowNumberCount+1):
                        $final_query = "INSERT into ".$tablename." (pin_code,city,delivery_days,cod) ".trim($query,',');
                        $writeConnection->query($final_query); 
                        $query = " values ";
                        $pkj = 0; 
                    endif;

                else:
                    
                if($pkj==500):
                    $final_query = "INSERT into ".$tablename." (pin_code,city,delivery_days,cod) ".trim($query,',');
                    $writeConnection->query($final_query);
                    $query = " values ";
                    $pkj = 0;
                endif;
                endif;
                endif;

            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Zipcode imported successfully'));

        } catch (Mage_Core_Exception $e) {
            //$adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            //$adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException($hlp->__('An error occurred while importing '.$tablename.'.'));
        }
    }
     public function deletePrevious()
    {
		
		$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
		foreach ($collection as $item) {
    		$item->delete();
		}
		
    }
}    