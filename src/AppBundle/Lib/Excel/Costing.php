<?php

namespace AppBundle\Lib\Excel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Reservation;

/**
 * Description of Costing
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class Costing extends ExportableBook
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
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
                ->setRequired('record')
                ->setAllowedTypes('record', 'AppBundle\\Entity\\Reservation')
                ;
    }

    public function getBookContent()
    {
        $this->renderHeader();
        $this->renderSuppliersBlock();
        $this->renderAdministrativeBlock();

        $writer = $this->phpexcel->createWriter($this->book, 'Excel5');

        return $this->phpexcel->createStreamedResponse($writer);
    }

    private function renderHeader()
    {
        $this->book->setActiveSheetIndex(0);
        $sheet = $this->book->getActiveSheet();

        $sheet->setCellValue('A1', sprintf('COSTING %s', $this->options['record']->getName()));

        $sheet->mergeCells('A1:L4');
        $sheet->getStyle('A1:L4')->getFont()->setBold(true);
        $sheet->getStyle('A1:L4')->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ;

        $sheet->getStyle('A5:L6')->getFont()->setBold(true);
        $sheet->getStyle('A5:L6')->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ;
    }

    private function renderSuppliersBlock()
    {
        $sheet = $this->book->getActiveSheet();

        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', 'SUPPLIER');
        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('B5', 'HOTEL');
        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('C5', 'BOOKING NUMBER');
        $sheet->mergeCells('D5:D6');
        $sheet->setCellValue('D5', '# OF NIGHTS');
        $sheet->mergeCells('E5:E6');
        $sheet->setCellValue('E5', '# OF PAX');
        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'COST');
        $sheet->mergeCells('G5:G6');
        $sheet->setCellValue('G5', 'TOTAL');
        $sheet->mergeCells('H5:H6');
        $sheet->setCellValue('H5', 'CURRENCY');
        $sheet->mergeCells('I5:L6');
        $sheet->setCellValue('I5', 'NOTES');

        $firstRowIndex = 7;

        foreach ($this->record->getServices() as $k => $service) {
            $sheet->mergeCells(sprintf('I%s:L%s', $firstRowIndex + $k, $firstRowIndex + $k));
            $sheet->fromArray(array(
                null !== $service->getSupplier() ? $service->getSupplier()->getName() : null,
                $service->getName(),
                $service->getSupplierReference(),
                $service->getNights(),
                $service->getPax(),
                null,
                null,
                'CUC',
                $service->getInternalNotes()
            ), null, sprintf('A%s', $firstRowIndex + $k));

            $sheet->getStyle(sprintf('F%s:G%s', $firstRowIndex + $k, $firstRowIndex + $k))->getNumberFormat()->setFormatCode('0.00');
            $sheet->getCell(sprintf('F%s', $firstRowIndex + $k))->setValueExplicit(sprintf('%0.2f', $service->getTotalPrice()), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getCell(sprintf('G%s', $firstRowIndex + $k))->setValueExplicit(sprintf('%0.2f', $service->getTotalPrice()), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
        }

        $sheet->mergeCells(sprintf('D%s:F%s', $firstRowIndex + $this->record->getServices()->count() + 2, $firstRowIndex + $this->record->getServices()->count() + 2));
        $sheet->getStyle(sprintf('D%s:F%s', $firstRowIndex + $this->record->getServices()->count() + 2, $firstRowIndex + $this->record->getServices()->count() + 2))
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(sprintf('D%s:F%s', $firstRowIndex + $this->record->getServices()->count() + 2, $firstRowIndex + $this->record->getServices()->count() + 2))
                ->getFont()
                ->setBold(true)
                ;
        $sheet->getCell(sprintf('D%s', $firstRowIndex + $this->record->getServices()->count() + 2, $firstRowIndex + $this->record->getServices()->count() + 2))
                ->setValue('TOTAL SUPPLIERS')
                ;
        $sheet->getCell(sprintf('G%s', $firstRowIndex + $this->record->getServices()->count() + 2))
                ->setValueExplicit(sprintf('=SUM(G7:G%s)', $firstRowIndex + $this->record->getServices()->count()), \PHPExcel_Cell_DataType::TYPE_FORMULA)
                ;
        $sheet->getStyle(sprintf('G%s', $firstRowIndex + $this->record->getServices()->count() + 2))
                ->getNumberFormat()
                ->setFormatCode('0.00')
                ;
    }

    private function renderAdministrativeBlock()
    {
        $firstRow = 7 + $this->record->getServices()->count() + 4;
        $sheet = $this->book->getActiveSheet();

        foreach ($this->record->getAdministrativeCharges() as $k => $charge) {
            $sheet->fromArray(array(
                $charge->getName(),
                '-',
                $charge->getMultiplier(),
                $charge->getPax(),
                $charge->getPrice(),
                $charge->getTotal()
            ), null, sprintf('B%s', $firstRow + $k));
        }
        $sheet
                ->getStyle(sprintf('F%s:G%s', $firstRow, $firstRow + $this->record->getAdministrativeCharges()->count()))
                ->getNumberFormat()
                ->setFormatCode('0.00')
                ;

        $totalRow = $firstRow + $this->record->getAdministrativeCharges()->count() + 1;
        $sheet->fromArray(array(
            'TOTAL EXPENSES',
            null,
            null,
            sprintf('=SUM(G%s:G%s)', $firstRow, $firstRow + $this->record->getAdministrativeCharges()->count())
        ), null, sprintf('D%s', $totalRow));
        $sheet
                ->mergeCells(sprintf('D%s:F%s', $totalRow, $totalRow))
                ->getStyle(sprintf('D%s:G%s', $totalRow, $totalRow))
                ->getFont()
                ->setBold(true)
                ;
        $sheet->getStyle(sprintf('G%s', $totalRow))
                ->getNumberFormat()
                ->setFormatCode('0.00')
                ;
    }
}
