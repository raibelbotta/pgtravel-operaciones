<?php

namespace AppBundle\Lib\Excel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\ReservationService;

/**
 * Description of Cash
 * 
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class Cash extends ExportableBook
{    
    /**
     * @var \PHPExcel
     */
    private $book;
    
    /**
     * @var Reservation
     */
    private $record;
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        
        $this->book = $this->phpexcel->createPHPExcelObject();
        $this->record = $this->options['record'];
        unset($this->options['record']);
    }
    
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver
                ->setRequired(array('record', 'translator', 'locale'))
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
                ->setAllowedTypes('translator', 'Symfony\\Component\\Translation\\TranslatorInterface')
                ->setAllowedTypes('locale', 'string')
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
        $translator = $this->options['translator'];
        $locale = $this->options['locale'];

        $sheet->fromArray(array_map(function(ReservationService $service) use ($translator, $locale) {
            return array(
                null,
                $service->getName(),
                null !== $service->getSupplier() ? $service->getSupplier()->getName() : '',
                $translator->trans('%from% to %to%', array(
                    '%from%' => $service->getStartAt()->format('d/m/Y'),
                    '%to%' => $service->getEndAt()->format('d/m/Y')
                ), null, $locale),
                sprintf('%0.2f', $service->getCost())
            );
        }, $this->record->getServices()->toArray()), null, 'A1');
    }
}
