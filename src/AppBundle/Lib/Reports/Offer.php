<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Entity\Reservation;

/**
 * Description of Offer
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class Offer extends ContainerAwareReport
{
    /**
     * @var Reservation
     */
    private $offer;

    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->offer = $options['offer'];
    }

    public function getContent()
    {
        $this->pdf->AddPage();

        $this->renderHeader();
        $this->renderBody();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFontSize(14);
        $this->pdf->Cell(0, 0, 'Offer', 0, 1, 'C');
    }

    private function renderBody()
    {
        $this->pdf->SetFontSize(10);

        $this->pdf->Cell($w, $h, $txt, $border, $ln, $align);

        return $this;
    }
}
