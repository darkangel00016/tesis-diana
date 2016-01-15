<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 04/01/16
 * Time: 05:22 PM
 */
class CorePdf extends TCPDF
{
    //Page header
    public function Header() {}

    // Page footer
    public function Footer() {
        $config = CoreRoute::getConfig();
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages() . " " . date($config["formato_fecha_larga"]), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}