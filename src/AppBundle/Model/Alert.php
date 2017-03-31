<?php

namespace AppBundle\Model;

/**
 * Description of Alert
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class Alert
{
    /**
     * @var AlertableInterface
     */
    private $record;
    
    public function __construct(AlertableInterface $record)
    {
       $this->record = $record; 
    }
}
