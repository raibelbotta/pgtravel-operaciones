<?php

namespace AppBundle\Lib\Reports;

use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ReportResponse
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReportResponse extends Response
{
    public function __construct(Report $report)
    {
        parent::__construct($report->getContent(), self::HTTP_OK, array(
            'Content-Type' => 'application/pdf'
        ));
    }
}
