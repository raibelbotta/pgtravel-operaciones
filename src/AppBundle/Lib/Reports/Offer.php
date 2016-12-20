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
        $this->renderFooter();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFontSize(14);

        $clientName = null !== $this->offer->getClient() ? (string) $this->offer->getClient() : $this->offer->getDirectClientFullName();

        $this->pdf->Write(0, sprintf('Offer for %s from PGTRAVEL', $clientName), false, false, 'C', 1);

        $this->pdf->Ln(4);

        return $this;
    }

    private function renderBody()
    {
        $this->pdf->SetFontSize(10);

        foreach ($this->offer->getServices() as $service) {
            $serviceName = $service->getDescription() ?: $service->getName();

            $h = $this->getRowHeight(array(
                array(150, $serviceName),
                array(0, $service->getStartAt()->format('d/m/Y'))
            ));

            $this->pdf->MultiCell(150, $h, $serviceName, 1, 'J', false, 0);
            $this->pdf->MultiCell(0, $h, $service->getStartAt()->format('d/m/Y'), 1, 'L', false, 1);
        }

        $this->pdf->Ln(4);

        return $this;
    }

    private function renderFooter()
    {
        $this->pdf->SetFontSize(13);
        $this->pdf->Cell(160, 0, '', 0, 0);
        $this->pdf->Cell(0, 0, $this->offer->getClientCharge(), 1, 1, 'R');
    }
}
