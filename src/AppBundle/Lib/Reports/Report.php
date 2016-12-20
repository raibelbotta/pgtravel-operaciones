<?php

namespace AppBundle\Lib\Reports;

include_once dirname(__FILE__) . '/../tcpdf/tcpdf.php';

abstract class Report implements ReportInterface
{
    /**
     * @var \TCPDF
     */
    protected $pdf;

    public function __construct($orintation = 'P', $format = 'A4')
    {
        $this->pdf = new \TCPDF($orintation, 'mm', $format);
    }

    /**
     * @param array $columns
     * @return string
     */
    protected function getRowHeight(array $columns)
    {
        $this->pdf->startTransaction();
        $this->pdf->addPage();

        $maxH = 0;

        foreach ($columns as $content) {
            $this->pdf->MultiCell($content[0], 0, $content[1], 1, 'J', false, 0);

            if ($maxH < $this->pdf->getLastH()) {
                $maxH = $this->pdf->getLastH();
            }
        }

        $this->pdf->rollbackTransaction(true);

        return $maxH;
    }

    protected function getPdfContent()
    {
        ob_start();

        $this->pdf->Output();

        $content = ob_get_clean();

        return $content;
    }
}