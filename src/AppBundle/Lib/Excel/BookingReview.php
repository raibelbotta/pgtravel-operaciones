<?php

namespace AppBundle\Lib\Excel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\ReservationService;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Description of Cash
 * 
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class BookingReview extends ExportableBook
{    
    /**
     * @var \PHPExcel
     */
    private $book;
    
    /**
     * @var Reservation
     */
    private $record;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var array
     */
    private $serviceModels;

    public function __construct(array $options = array())
    {
        parent::__construct($options);
        
        $this->book = $this->phpexcel->createPHPExcelObject();
        $this->record = $this->options['record'];
        $this->manager = $this->options['manager'];
        
        $this->serviceModels = array();
        foreach ($this->options['models'] as $element) {
            $this->serviceModels[$element['name']] = $element;
        }
        $this->translator = $this->options['translator'];
        
        unset($this->options['record'], $this->options['manager'], $this->options['models']);
    }
    
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver
                ->setRequired(array('record', 'translator', 'locale', 'manager', 'models'))
                ->setAllowedTypes('record', Reservation::class)
                ->setAllowedTypes('translator', TranslatorInterface::class)
                ->setAllowedTypes('locale', 'string')
                ->setAllowedTypes('manager', EntityManager::class)
                ->setAllowedTypes('models', 'array')
                ->setDefault('locale', 'en')
                ;
    }
    
    public function getBookContent()
    {
        $this->render();
        
        $writer = $this->phpexcel->createWriter($this->book, 'Excel5');
        
        return $this->phpexcel->createStreamedResponse($writer);
    }
    
    private function render()
    {
        $sheet = $this->book->getActiveSheet();
        $accessor = PropertyAccess::createPropertyAccessor();
        $record = $this->record;

        $sheet->fromArray(array_map(function(ReservationService $service) use ($record, $accessor) {
            if (null !== $service->getDescription()) {
                $text = $service->getDescription();
            } elseif (Reservation::STATE_OFFER === $record->getState()) {
                if ($accessor->getValue($this->serviceModels[$service->getModel()], '[has_nights]')) {
                    $text = sprintf('%s nights in %s', $service->getNights() , $service->getName());
                } else {
                    $text = $service->getName();
                }
            } else {
                $text = sprintf('%s to %s. %s', $service->getStartAt()->format('d/m/Y H:i'), $service->getEndAt()->format('d/m/Y H:i'), $service->getName());
            }

            return array($text);
        }, $this->getSortedServices()), null, 'A1');
    }

    /**
     * @return array
     */
    private function getSortedServices()
    {
        $query = $this->manager->createQuery('SELECT rs FROM AppBundle:ReservationService rs JOIN rs.reservation r WHERE r.id = :reservation ORDER BY rs.startAt')
                ->setParameter('reservation', $this->record->getId())
                ;

        return $query->getResult();
    }
}
