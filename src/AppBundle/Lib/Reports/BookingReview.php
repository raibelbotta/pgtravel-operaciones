<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;

/**
 * Description of BookingPreview
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class BookingReview extends Report
{
    /**
     * @var Reservation
     */
    private $record;

    /**
     * @var array
     */
    private $serviceModels;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->record = $this->options['record'];
        $this->serviceModels = array();
        foreach ($this->options['models'] as $element) {
            $this->serviceModels[$element['name']] = $element;
        }
        $this->translator = $this->options['translator'];

        unset($this->options['record'], $this->options['models'], $this->options['translator']);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
                ->setRequired(array('record', 'manager', 'models', 'locale', 'translator'))
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
                ->setAllowedTypes('manager', EntityManager::class)
                ->setAllowedTypes('models', 'array')
                ->setAllowedTypes('translator', TranslatorInterface::class)
                ->setAllowedTypes('locale', 'string')
                ->setDefault('locale', 'en')
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
        $this->pdf->Write(0, 'BOOKING REVIEW', '', false, 'C', true);
        $this->pdf->Write(0, sprintf('Name: %s', $this->record->getName()), '', false, 'C', true);

        $this->pdf->Ln(8);
    }

    private function renderBody()
    {
        $this->pdf->SetFontSize(10);
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->getSortedServices() as $service) {
            if (null !== $service->getDescription()) {
                $text = $service->getDescription();
            } elseif (Reservation::STATE_OFFER === $this->record->getState()) {
                if ($accessor->getValue($this->serviceModels[$service->getModel()], '[has_nights]')) {
                    $text = sprintf('%s nights in %s', $service->getNights() , $service->getName());
                } else {
                    $text = $service->getName();
                }
            } else {
                if ($service->getEndAt()) {
                    $text = sprintf('%s to %s. %s', $service->getStartAt()->format('d/m/Y H:i'), $service->getEndAt()->format('d/m/Y H:i'), $service->getName());
                } else {
                    $text = sprintf('%s %s', $service->getStartAt()->format('d/m/Y H:i'), $service->getName());
                }
            }

            $this->pdf->Write(0, $text, '', false, 'L', true);
            $this->pdf->Ln(2);
        }
    }

    private function getSortedServices()
    {
        $query = $this->options['manager']->createQuery('SELECT rs FROM AppBundle:ReservationService rs JOIN rs.reservation r WHERE r.id = :reservation ORDER BY rs.startAt')
                ->setParameter('reservation', $this->record->getId())
                ;

        return $query->getResult();
    }
}
