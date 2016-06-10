<?php
require_once(BP . DS . 'lib' . DS . 'tcpdf' . DS . 'config'. DS . 'tcpdf_config_mage.php');
require_once(BP . DS . 'lib' . DS . 'tcpdf' . DS . 'tcpdf.php');

class ITwebexperts_Request4quote_Model_Quotepdf extends Mage_Core_Model_Abstract
{
    const PDFCUSTOMISER_PDF_TYPE = 'rfq';

    public function getFilePath(){
        return Mage::getBaseDir('media') . DS . 'pdfs' . DS;
    }



    /**
    * Render a PDF file and show in browser or save to disk
    * If save to disk return file location
    */

    public function renderRfqRequest($quote, $saveToDisk = false){
        $storeid = $quote->getStoreId();
        if ($storeid) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initial = $appEmulation->startEnvironmentEmulation(
                $storeid, Mage_Core_Model_App_Area::AREA_FRONTEND, true
            );
        }
        $pdf = new TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true,30);
        $pdf->setHeaderMargin(20);
        $pdf->setFooterMargin(20);
        $pdf->setImageScale(1.5);
        $emailtext = Mage::helper('request4quote/email')->sendRequestProposalNotification($quote, false, true);

        $pdf->writeHTML(Mage::helper('cms')->getBlockTemplateProcessor()->filter($emailtext), false);
        $pdf->endPage();
        if ($storeid) {
            $appEmulation->stopEnvironmentEmulation($initial);
        }
        $rfqfilename = 'rfq_' . $quote->getId() . '.pdf';
        if(!$saveToDisk) {
            return $pdf->Output($rfqfilename);
        } else if ($saveToDisk){
            $filePath = $this->getFilePath() . $rfqfilename;
            $pdf->Output($filePath, 'F');
            return $filePath;
        }
        exit;
    }
}
