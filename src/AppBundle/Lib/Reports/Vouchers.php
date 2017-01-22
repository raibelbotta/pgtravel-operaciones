<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;

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
            'format' => array(612, 396)
        )));

        $this->record = $this->options['record'];
        unset($this->options['record']);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
                ->setRequired('record')
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
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
        
    }
}