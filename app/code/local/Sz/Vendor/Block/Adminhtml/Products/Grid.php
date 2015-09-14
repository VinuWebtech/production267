<?php
class Sz_Vendor_Block_Adminhtml_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct(){
        parent::__construct();
        $this->setId('vendorGrid');
        $this->setUseAjax(true);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper('vendor')->__('No Products Found.');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('vendor/product')->getCollection();
        if($this->getRequest()->getParam('unapp')==1){
           $collection->addFieldToFilter('status', array('neq' => '1'));

        }
        $customerModel = Mage::getModel('customer/customer');
        $prefix = Mage::getConfig()->getTablePrefix();
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $pro_att_id = $eavAttribute->getIdByCode("catalog_product","name");
        $fnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "firstname")->getAttributeId();
        $lnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "lastname")->getAttributeId();
        $collection->getSelect()
                ->join(array("ce1" => $prefix."customer_entity_varchar"),"ce1.entity_id = main_table.userid",array("fname" => "value"))->where("ce1.attribute_id = ".$fnameid)
                ->join(array("ce2" => $prefix."customer_entity_varchar"),"ce2.entity_id = main_table.userid",array("lname" => "value"))->where("ce2.attribute_id = ".$lnameid)
                ->columns(new Zend_Db_Expr("CONCAT(`ce1`.`value`, ' ',`ce2`.`value`) AS fullname"));
        $collection->addFilterToMap("fullname","`ce1`.`value`");

        $pro_att_id = $eavAttribute->getIdByCode("catalog_product","name");
        $collection->getSelect()
        ->join(array("pn" => $prefix."catalog_product_entity_varchar"),"pn.entity_id = main_table.mageproductid",array("proname" => "value"))->where("pn.attribute_id = ".$pro_att_id. " AND pn.store_id = ".Mage::app()->getStore()->getStoreId());
        $collection->addFilterToMap("proname","pn.value");

        $price_att_id = $eavAttribute->getIdByCode("catalog_product","price");
        $collection->getSelect()
        ->join(array("pp" => $prefix."catalog_product_entity_decimal"),"pp.entity_id = main_table.mageproductid",array("price" => "value"))->where("pp.attribute_id = ".$price_att_id. " AND pn.store_id = ".Mage::app()->getStore()->getStoreId());
        $collection->addFilterToMap("price","pp.value");

        $collection->getSelect()->joinLeft($prefix."cataloginventory_stock_item","main_table.mageproductid = ".$prefix."cataloginventory_stock_item.product_id",array("qty"=>"qty"));
        $collection->getSelect()->joinLeft($prefix."catalog_product_entity","main_table.mageproductid = ".$prefix."catalog_product_entity.entity_id",array("created_at"=>"created_at"));

        $this->setCollection($collection);
        parent::_prepareCollection();        
        //Modify loaded collection
        foreach ($this->getCollection() as $item) {
            $item->deny = sprintf('<button type="button" class="wk_denyproduct" customer-id ="%s" product-id="%s"><span><span title="Deny">Deny</span></span></button>',$item->getuserid(),$item->getMageproductid());
            $item->prev = sprintf('<span data="%s" product-id="%s" customer-id="%s" title="Click to Review" class="prev btn">prev</span>',$this->getUrl('vendor/prev/index/id/' .$item->getMageproductid()),$item->getMageproductid(),$item->getuserid());
            $item->entity_id = (int)$item->getmageproductid();
            if(!(is_null($item->getmageproductid())) && $item->getmageproductid() != 0){
                 if($item->getstatus() == 1){
                    $item->status = sprintf('<a href="%s" title="View product">Approved</a>',
                                             $this->getUrl('adminhtml/catalog_product/edit/id/' . $item->getmageproductid())
                                            );
                }
                else{
                    $item->status = sprintf('<a href="%s" title="Click to Approve" onclick="return confirm(\'You sure?\')">Unapproved</a>',$this->getUrl('vendor/adminhtml_products/approve/id/' . $item->getmageproductid()));
                }
            $product = Mage::getModel('catalog/product')->load($item->getmageproductid());
            $stock_inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getmageproductid());
            $item->weight = $product->getWeight();
            $item->stock = $stock_inventory->getQty();
                
            $quantity = Mage::getModel('vendor/saleslist')->getSalesdetail($item->getmageproductid());
            $item->qty_sold = (int)$quantity['quantitysold'];
            $item->qty_soldconfirmed = (int)$quantity['quantitysoldconfirmed'];
            $symbol=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(); 
            $item->qty_soldpending = (int)$quantity['quantitysoldpending'];
            $item->amount_earned = $symbol.$quantity['amountearned'];
                foreach($quantity['clearedat'] as $clear){
                    if ( isset($clear) && $clear != '0000-00-00 00:00:00' ) {$item->cleared_at = $clear;}
                }
            }
        }
    }

    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('vendor')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
            'filter_index' => 'main_table.mageproductid'
        ));
       
        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('vendor')->__('Customer Name'),
            'index'     => 'fullname',
            'type'  => 'text',
        ));
      
        $this->addColumn('name', array(
            'header'    => Mage::helper('vendor')->__('Name'),
            'index'     => 'proname',
            'type'  => 'string',
        ));
         $this->addColumn('price', array(
            'header'    => Mage::helper('vendor')->__('Price'),
            'index'     => 'price',
            'currency_code' => $this->getcurrency(),
            'type'  => 'price',
        ));
        $this->addColumn('stock', array(
            'header'    => Mage::helper('vendor')->__('Stock'),
            'index'     => 'qty',
            'type'  => 'number',
            "filter"    => false,
            "sortable"  => false
        ));
        $this->addColumn('weight', array(
            'header'    => Mage::helper('vendor')->__('Weight'),
            'index'     => 'weight',
            'type'  => 'number',
            "filter"    => false,
            "sortable"  => false
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('vendor')->__('Status'),
            'index'     => 'status',
            "type"      => "text",
            "align"     => "center",
            "filter"    => false,
            // "sortable"  => false
        ));
        $this->addColumn('prev', array(
            'header'    => Mage::helper('vendor')->__('Preview'),
            'index'     => 'prev',
            'type'  => 'text',
            'filter'    => false,
            'sortable'  => false
        ));
        $this->addColumn('qty_soldconfirmed', array(
            'header'    => Mage::helper('vendor')->__('Qty. Confirmed'),
            'index'     => 'qty_soldconfirmed',
            'type'  => 'number',
            'filter'    => false,
            'sortable'  => false
        ));
        
        $this->addColumn('qty_soldpending', array(
            'header'    => Mage::helper('vendor')->__('Qty. Pending'),
            'index'     => 'qty_soldpending',
            'type'  => 'number',
            'filter'    => false,
            'sortable'  => false
        ));
        $this->addColumn('qty_sold', array(
            'header'    => Mage::helper('vendor')->__('Qty. Sold'),
            'index'     => 'qty_sold',
            'type'  => 'number',
            'filter'    => false,
            'sortable'  => false
        ));
        $this->addColumn('amount_earned', array(
            'header'    => Mage::helper('vendor')->__('Earned'),
            'index'     => 'amount_earned',
            'type'  => 'price',
            'filter'    => false,
            'sortable'  => false
        ));
        
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('vendor')->__('Created'),
            'index'     => 'created_at',
            'type'  => 'datetime',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()  {
        $this->setMassactionIdField('main_table.mageproductid');
        $this->getMassactionBlock()->setFormFieldName('vendorproduct');
        $this->getMassactionBlock()->addItem('delete', array(
           'label'    => Mage::helper('vendor')->__('Approve'),
           'url'      => $this->getUrl('vendor/adminhtml_products/massapprove')
        ));
        return $this;
    }

    public function getGridUrl(){
        return $this->getUrl("*/*/grid",array("_current"=>true));
    }

    public function getcurrency(){        
        return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }
}