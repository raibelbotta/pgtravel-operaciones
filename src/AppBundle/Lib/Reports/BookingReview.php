<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of BookingPreview
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class BookingReview extends Report
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver
                ->setRequired(array('record', 'manager'))
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
                ->setAllowedTypes('manager', 'Doctrine\\ORM\\EntityManager')
                ;
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
        $this->pdf->Write(0, 'BOOKING PREVIEW', '', false, 'C', true);
        $this->pdf->Write(0, sprintf('Name: %s', $this->options['record']->getName()), '', false, 'C', true);
        
        $this->pdf->Ln(8);
    }
    
    private function renderBody()
    {
        $this->pdf->SetFontSize(10);

        foreach ($this->getSortedServices() as $service) {
            $text = sprintf('%s to %s. %s', $service->getStartAt()->format('d/m/Y'), $service->getEndAt()->format('d/m/Y'), $service->getName());
            $this->pdf->Write(0, $text, '', false, 'L', true);
            $this->pdf->Ln(2);
        }
    }
    
    private function getSortedServices()
    {
        $query = $this->options['manager']->createQuery('SELECT rs FROM AppBundle:ReservationService rs JOIN rs.reservation r WHERE r.id = :reservation ORDER BY rs.startAt')
                ->setParameter('reservation', $this->options['record']->getId())
                ;
        
        return $query->getResult();
    }
}
