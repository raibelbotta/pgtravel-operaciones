<?php

namespace AppBundle\Lib\Reports;

include_once dirname(__FILE__) . '/../tcpdf/tcpdf.php';

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Report implements ReportInterface
{
    /**
     * @var \TCPDF
     */
    protected $pdf;
    
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        
        $this->options = $resolver->resolve($options);
        
        $this->pdf = new \TCPDF($this->options['orientation'], 'mm', $this->options['format']);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                    'orientation'   => 'P',
                    'format'        => 'A4'
                ));
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