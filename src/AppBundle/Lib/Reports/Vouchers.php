<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Description of Vouchers
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class Vouchers extends Report
{
    /**
     * @var Reservation
     */
    private $record;

    public function __construct(array $options = array())
    {
        parent::__construct(array_replace($options, array(
            'format' => 'LETTER'
        )));

        $this->record = $this->options['record'];
        unset($this->options['record']);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
                ->setRequired(array('record', 'images_dir', 'models', 'translator', 'locale'))
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
                ->setAllowedTypes('translator', 'Symfony\\Component\\Translation\\TranslatorInterface')
                ->setAllowedTypes('locale', 'string')
                ->setAllowedTypes('images_dir', 'string')
                ;
    }

    public function getContent()
    {
        $this->pdf->AddPage();

        $this->render();

        return $this->getPdfContent();
    }

    private function render()
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        
        $this->pdf->SetFont('', 'B', 18);
        $this->pdf->Write(0, sprintf('Vouchers for %s', $this->record->getName()), '', false, 'C');

        $logo = $this->options['images_dir'] . '/logo.jpg';
        $this->pdf->SetFont('', '', 12);
        $this->pdf->SetFillColor(148);
        
        foreach ($this->record->getServices() as $service) {
            $this->pdf->AddPage();
            
            $title = $accessor->getValue($this->options['models'][$service->getModel()], '[voucher_title]');
            
            $this->pdf->Image($logo, 20, 15, 50);
            
            $this->pdf->SetFont('', '', 10);
            $this->pdf->Cell(70);
            $this->pdf->Cell(0, 0, $title ? : 'VOUCHER', 0, 1, 'C');
            
            $this->pdf->Ln(5);
            
            $this->pdf->Cell(70);
            $this->pdf->Cell(0, 0, $this->options['translator']->trans('CLIENT DETAILS'), 0, 1, 'C', true);
            $this->pdf->Cell(70);
            $this->pdf->Cell(0, 0, $this->options['translator']->trans('Name: %name%', array('%name%' => $service->getClientName(0))), 0, 1, 'L');
            $this->pdf->Cell(70);
            $this->pdf->Cell(0, 0, $this->options['translator']->trans('Passport: %passport%', array('%passport%' => $service->getClientPassport())), 0, 1, 'L');
            
            $this->pdf->Ln(4);
            
            $this->pdf->Cell(70);
            $this->pdf->Cell(0, 0, $this->options['translator']->trans('PROVIDER'), 0, 1, 'C', true);
            
            if ($service->getModel() == 'transport') {
                $this->pdf->Cell(70);
                $this->pdf->Cell(0, 0, $this->options['translator']->trans('From: %place%', array('%place%' => $service->getOrigin())), 'B', 1, 'L');
                $this->pdf->Cell(70);
                $this->pdf->Cell(0, 0, $this->options['translator']->trans('To: %place%', array('%place%' => $service->getDestination())), 'B', 1, 'L');
                $this->pdf->Cell(70);
                $this->pdf->Cell(40, 0, $this->options['translator']->trans('Direction:'), 'B', 0, 'L');
                $this->pdf->Cell(60, 0, $this->options['translator']->trans('Date: %date%', array('%date%' => $service->getStartAt()->format('d/m/Y'))), 'B', 0, 'L');
                $this->pdf->Cell(0, 0, $this->options['translator']->trans('Time: %time%', array('%time%' => $service->getStartAt()->format('H:i'))), 'B', 1, 'L');
                $this->pdf->Cell(70);
                $this->pdf->Cell(0, 0, $this->options['translator']->trans('Type: %type%', array('%type%' => '')), 'B', 1, 'L');
                $this->pdf->Cell(70);
                $this->pdf->Cell(0, 0, $this->options['translator']->trans('PAX: %pax%', array('%pax%' => $service->getPax())), 'B', 1, 'L');
                
                $this->pdf->Ln(4);
                
                $this->pdf->Cell(70, 0, 'VOUCHER DETAILS', 0, 1, 'C', true);
                $this->pdf->Cell(20, 0, 'No.:', 1, 0, 'L');
                $this->pdf->Cell(50, 0, '', 1, 1, 'L');
                $this->pdf->Cell(20, 0, 'Date:', 1, 0, 'L');
                $this->pdf->Cell(50, 0, '', 1, 1, 'L');
                
                $this->pdf->Ln(2);
                $this->pdf->Cell(0, 0, 'Notes', 0, 1, 'C');
                $this->pdf->Cell(0, 14, '', array('LTRB' => array('width' => 1.12)), 1, 'C');
                
            } elseif ($service->getModel() == 'hotel') {
                
            }
        }
    }
}