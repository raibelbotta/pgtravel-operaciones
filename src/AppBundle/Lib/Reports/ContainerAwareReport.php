<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of ContainerAwareReport
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
abstract class ContainerAwareReport extends Report implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    protected $options;

    public function __construct($options = array())
    {
        $orintation = isset($options['orientation']) ? $options['orientation'] : 'P';
        $format = isset($options['format']) ? $options['format'] : 'A4';

        parent::__construct($orintation, $format);

        $this->options = $options;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }
}
