<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Description of ReportFactory
 *
 * @author Raibel Botta <raibelbottagmail.com>
 */
class ReportFactory implements ContainerAwareInterface
{
    /**
     * @var ContainerAwareInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function createReport($className, $options = array())
    {
        $report = new $className($options);
        $report->setContainer($this->container);

        return $report;
    }
}
