<?php

class Magebright_Productrestriction_Model_Importproduct extends Mage_Core_Model_Config_Data
{ 

    protected function _afterSave()
    {
        set_time_limit(60000); 
        ini_set('max_execution_time', 60000);
        ini_set('memory_limit','512M');

        if (empty($_FILES['groups']['tmp_name']['export_import_zipproduct']['fields']['upload_productzipcode']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['export_import_zipproduct']['fields']['upload_productzipcode']['value'];
        $this->_importedRows        = 0;

        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
		$hlp = Mage::helper('productrestriction');
        $resource = Mage::getSingleton('core/resource');
        $tablename= $resource->getTableName('zipcodeproduct');
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
              $pin_code = trim(preg_replace('/\s+/',' ', $actual_data[0]));
              $collection = $model->getCollection();
              $collection->addFieldToFilter('pin_code',$pin_code);
              $collection->getSelect()->limit(1);
              if($collection->getSize() > 0){
                  $isExist=1;
                }


                if($isExist==1){
                   $new_data = array();
                    $new_data['pin_code'] = $pin_code;//trim(preg_replace('/\s+/',' ', $actual_data[0]));
                    $new_data['product_id'] = str_replace('"','',trim(preg_replace('/\s+/',' ', $actual_data[1])));

                $query .= " (
                    '".$new_data['pin_code']."',
                                '".$new_data['product_id']."'),";
                }
                if($rowNumberCount<500):

                   if($rowNumber==$rowNumberCount+1):
          		    $final_query = "INSERT into ".$tablename." (pin_code,product_id) ".trim($query,',');
                    $writeConnection->query($final_query);
                    $query = " values ";
                    $pkj = 0;
                    endif;

                elseif($rowNumber>($rowNumberCount-500)):

                    if($rowNumber==$rowNumberCount+1):                        
                       $final_query = "INSERT into ".$tablename." (pin_code,product_id) ".trim($query,',');
                        $writeConnection->query($final_query); 
                        $query = " values ";
                        $pkj = 0; 
                    endif;
                else:
                    
                if($pkj==500):
                 $final_query = "INSERT into ".$tablename." (pin_code,product_id) ".trim($query,',');
                $writeConnection->query($final_query);
                //sleep(3);
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
		
		$collection = Mage::getModel('productrestriction/zipcodeproduct')->getCollection();
		foreach ($collection as $item) {
    		$item->delete();
		}
    }
}    