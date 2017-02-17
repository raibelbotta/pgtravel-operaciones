<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;

/**
 * Description of Costing
 *
 * @author raibel
 */
class Costing extends Report
{
    /**
     * @var Reservation
     */
    private $offer;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->offer = $options['record'];
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
                ->setDefault('orientation', 'L')
                ->setRequired('record')
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
                ;
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
        $this->pdf->SetFont('', 'B');

        $this->pdf->Cell(0, 15, sprintf('Costing %s', $this->offer->getName()),
                1, 1, 'C');
    }

    private function renderBody()
    {
        $this->pdf->SetFontSize(10);
        $this->pdf->SetFont('', 'B');

        $h = $this->getRowHeight(array(
            array(45, 'SUPPLIER'),
            array(60, 'SERVICE'),
            array(20, 'BOOKING NUMBER'),
            array(20, '# OF NIGHTS'),
            array(20, '# OF PAX'),
            array(20, 'COST'),
            array(25, 'CURRENCY'),
            array(0, 'NOTES')
        ));
        $this->pdf->MultiCell(45, $h, 'SUPPLIER', 1, 'C', false, 0);
        $this->pdf->MultiCell(60, $h, 'SERVICE', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, 'BOOKING NUMBER', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, '# OF NIGHTS', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, '# OF PAX', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, 'COST', 1, 'C', false, 0);
        $this->pdf->MultiCell(25, $h, 'CURRENCY', 1, 'C', false, 0);
        $this->pdf->MultiCell(0, $h, 'NOTES', 1, 'C', false, 1);

        $this->pdf->SetFontSize(9);
        $this->pdf->SetFont('', '');

        $totalSuppliers = 0;

        foreach ($this->offer->getServices() as $service) {
            $h = $this->getRowHeight(array(
                array(45, $service->getSupplier() ? $service->getSupplier()->getName() : ''),
                array(60, $service->getName()),
                array(20, $service->getSupplierReference()),
                array(20, $service->getNights()),
                array(20, $service->getPax()),
                array(20, sprintf('%0.2f', $service->getTotalPrice())),
                array(25, 'CUC'),
                array(0, $service->getInternalNotes())
            ));

            $this->pdf->MultiCell(45, $h, $service->getSupplier() ? $service->getSupplier()->getName() : '', 1, 'L', false, 0);
            $this->pdf->MultiCell(60, $h, $service->getName(), 1, 'L', false, 0);
            $this->pdf->MultiCell(20, $h, $service->getSupplierReference(), 1, 'L', false, 0);
            $this->pdf->MultiCell(20, $h, $service->getNights(), 1, 'C', false, 0);
            $this->pdf->MultiCell(20, $h, $service->getPax(), 1, 'C', false, 0);
            $this->pdf->MultiCell(20, $h, sprintf('%0.2f', $service->getTotalPrice()), 1, 'R', false, 0);
            $this->pdf->MultiCell(25, $h, 'CUC', 1, 'C', false, 0);
            $this->pdf->MultiCell(0, $h, $service->getInternalNotes(), 1, 'J', false, 1);

            $totalSuppliers += $service->getTotalPrice();
        }

        $this->pdf->Cell(165, 0, '', 0, 0);
        $this->pdf->Cell(20, 0, sprintf('%0.2f', $totalSuppliers), 1, 1, 'R');

        $this->pdf->Ln(6);

        $this->pdf->SetFont('', 'B');

        $h = $this->getRowHeight(array(
            array(100, ''),
            array(20, '# OF NIGHTS'),
            array(20, '# OF PAX'),
            array(20, 'COST'),
            array(20, 'TOTAL'),
            array(25, 'CURRENCY'),
            array(0, 'NOTES')
        ));
        $this->pdf->MultiCell(105, $h, 'ITEM', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, '# OF NIGHTS', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, '# OF PAX', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, 'COST', 1, 'C', false, 0);
        $this->pdf->MultiCell(20, $h, 'TOTAL', 1, 'C', false, 0);
        $this->pdf->MultiCell(0, $h, '', 0, 'L', false, 1);

        $this->pdf->SetFontSize(9);
        $this->pdf->SetFont('', '');

        $sumCharges = 0;
        foreach ($this->offer->getAdministrativeCharges() as $charge) {
            $this->pdf->Cell(105, 0, $charge->getName(), 1, 0);
            $this->pdf->Cell(20, 0, $charge->getNights(), 1, 0);
            $this->pdf->Cell(20, 0, $charge->getPax(), 1, 0);
            $this->pdf->Cell(20, 0, $charge->getPrice(), 1, 0, 'R');
            $this->pdf->Cell(20, 0, $charge->getTotal(), 1, 1, 'R');

            $sumCharges += $charge->getTotal();
        }

        $this->pdf->Cell(165, 0, '', 0, 0);
        $this->pdf->Cell(20, 0, sprintf('%0.2f', $sumCharges), 1, 1, 'R');

        $this->pdf->Ln(5);

        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(165, 0, preg_match('/^\d+\%$/', $this->offer->getPercentApplied()) ? 'Percent applied' : 'Plus added', 1, 0, 'L');
        $this->pdf->Cell(20, 0, $this->offer->getPercentApplied(), 1, 1, 'R');

        $this->pdf->Ln(5);

        $this->pdf->Cell(165, 0, 'Total client charge', 1, 0, 'L');
        $this->pdf->Cell(20, 0, sprintf('%0.2f', $this->offer->getClientCharge()), 1, 1, 'R');
    }

    public function renderFooter()
    {
        $this->pdf->Ln(8);
    }
}
