<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Lib\Reports\ReportResponse;
use AppBundle\Lib\Reports;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Description of ReportsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/reports")
 */
class ReportsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $dates = array(
            'today' => new \DateTime('now'),
            'first_day' => date_create('first day of this month'),
            'last_day' => date_create('last day of this month')
        );

        return $this->render('Reports/index.html.twig', array(
            'dates' => $dates
        ));
    }

    /**
     * @Route("/cxcobrar")
     * @Method({"post"})
     * @return Response
     */
    public function cxcobrarAction(Request $request)
    {
        if ($request->get('format') == 'pdf') {
            $report = $this->container->get('report_factory')->createReport(Reports\CXCobrar::class, array(
                'from' => date_create($request->get('from')),
                'to' => date_create($request->get('to')),
                'state' => $request->get('state')
            ));
        } elseif ($request->get('format') == 'xls') {
            // ask the service for a Excel5
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("liuggio")
                ->setLastModifiedBy("Giulio De Donato")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Hello')
                ->setCellValue('B2', 'world!');
            $phpExcelObject->getActiveSheet()->setTitle('Simple');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'stream-file.xls'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }

        return new ReportResponse($report);
    }

    /**
     * @Route("/offer/{id}/{format}", requirements={"id": "\d+", "format": "pdf|xls"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param \AppBundle\Entity\Reservation $record
     * @param string $format
     * @return Response
     */
    public function offerAction(\AppBundle\Entity\Reservation $record, $format)
    {
        if ('pdf' == $format) {
            $report = new Reports\Costing(array(
                'record' => $record
            ));

            return new StreamedResponse(function() use($report) {
                $content = $report->getContent();
                file_put_contents('php://output', $content);
            }, 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="Costing %s.pdf"', $record->getName())
            ));
        } else {
            $book = new \AppBundle\Lib\Excel\Costing(array(
                'phpexcel' => $this->container->get('phpexcel'),
                'record' => $record
            ));

            $response = $book->getBookContent();
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                sprintf('Costing %s v%s.xls', $record->getName(), $record->getVersion())
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }
    }
}
