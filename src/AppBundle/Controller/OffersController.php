<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Reservation;
use AppBundle\Form\Type\OfferFormType;
use AppBundle\Entity\ReservationAdministrativeCharge;

/**
 * Description of OffersController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/offers")
 */
class OffersController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $session = $this->container->get('session');

        return $this->render('Offers/index.html.twig', array(
            'filter' => $session->get('offers.filter', array(
                'fromDate' => date_create('now')->format('d/m/Y'),
                'cancelled' => 'no'
            ))
        ));
    }

    /**
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @Method({"get"})
     * @param Reservation $record
     * @return Response
     */
    public function viewAction(Reservation $record)
    {
        return $this->render('Offers/view.html.twig', array(
            'record' => $record,
            'models' => $this->container->getParameter('app.contract.models')
        ));
    }

    /**
     * @Route("/get-data", options={"i18n": false, "expose": true})
     * @Method({"post"})
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Reservation')
                ->createQueryBuilder('r')
                ->leftJoin('r.client', 'c')
                ;
        $qb->add('select', '(SELECT MIN(rs1.startAt) FROM AppBundle:ReservationService rs1 JOIN rs1.reservation rk1 WHERE rk1.id = r.id) AS startAt', true);
        $qb->add('select', '(CASE WHEN r.client IS NULL THEN r.directClientFullName ELSE c.fullName END) AS clientName', true);

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter', array());

        $session = $this->container->get('session');
        $session->set('offers.filter', $filter);

        $andX = $qb->expr()->andX();

        if (isset($filter['state']) && $filter['state']) {
            $andX->add($qb->expr()->eq('r.state', $qb->expr()->literal('offer' === $filter['state'] ? Reservation::STATE_OFFER : Reservation::STATE_RESERVATION)));
        }
        if (isset($filter['fromDate']) && $filter['fromDate'] && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $filter['fromDate'])) {
            $fromDate = \DateTime::createFromFormat('d/m/Y', $filter['fromDate']);
            $andX->add($qb->expr()->gte('(SELECT MIN(rs3.startAt) FROM AppBundle:ReservationService rs3 JOIN rs3.reservation rk3 WHERE rk3.id = r.id)', $qb->expr()->literal($fromDate->format('Y-m-d'))));
        }
        if (isset($filter['toDate']) && $filter['toDate'] && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $filter['toDate'])) {
            $toDate = \DateTime::createFromFormat('d/m/Y', $filter['toDate']);
            $andX->add($qb->expr()->lte('(SELECT MAX(rs4.endAt) FROM AppBundle:ReservationService rs4 JOIN rs4.reservation rk4 WHERE rk4.id = r.id)', $qb->expr()->literal($toDate->format('Y-m-d 23:59:59'))));
        }
        if (isset($filter['cancelled']) && $filter['cancelled']) {
            $andX->add($qb->expr()->eq('r.isCancelled', $qb->expr()->literal('yes' === $filter['cancelled'])));
        }

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->orX(
                    $qb->expr()->like('r.name', $qb->expr()->literal('%' . $search['value'] . '%')),
                    $qb->expr()->orX(
                            $qb->expr()->andX(
                                    $qb->expr()->isNotNull('r.client'),
                                    $qb->expr()->like('c.fullName', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                                    ),
                            $qb->expr()->andX(
                                    $qb->expr()->isNull('r.client'),
                                    $qb->expr()->like('r.directClientFullName', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                                    )
                            )
                        ));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'r.name';
                } elseif ($name == 'startAt') {
                    return 'startAt';
                } elseif ($name == 'client') {
                    return 'clientName';
                }
                return null;
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        if ($request->get('length')) {
            $paginator = $this->get('knp_paginator');
            $page = $request->get('start', 0) / $request->get('length') + 1;
            $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

            $list = $pagination->getItems();
            $total = $pagination->getTotalItemCount();
        } else {
            $list = $qb->getQuery()->getResult();
            $total = count($list);
        }

        $twig = $this->container->get('twig');
        $data = array_map(function($record) use ($twig) {
            return array(
                (string) $record[0]->getVersion(),
                sprintf('<i class="fa fa-circle" style="color: #%s"></i>', $record[0]->getIsCancelled() ? '333' : ($record[0]->getState() == Reservation::STATE_RESERVATION ? '1abb9c' : 'd9534f')),
                $record[0]->getName(),
                null !== $record[0]->getClient() ? (string) $record[0]->getClient() : $record[0]->getDirectClientFullName(),
                date_create($record['startAt'])->format('d/m/Y H:i'),
                $twig->render('Offers/_actions.html.twig', array('record' => $record[0]))
            );
        }, $list);

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/new")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $offer = new Reservation();
        $offer->setOperator($this->getUser());

        foreach ($this->container->getParameter('app.administrative_services') as $k) {
            $service = new ReservationAdministrativeCharge();
            $service
                    ->setName($k['name'])
                    ->setPax(0)
                    ->setMultiplier(1)
                    ->setPrice($k['price'])
                    ->setTotal(0)
                    ;
            $offer->addAdministrativeCharge($service);
        }

        $form = $this->createForm(OfferFormType::class, $offer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('jumpToOperation')->getData()) {
                $offer->setState(Reservation::STATE_RESERVATION);
            }
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($form->getData());
            $manager->flush();

            return $this->redirect($this->generateUrl($form->get('jumpToOperation')->getData() ? 'app_bookings_index' : 'app_offers_index'));
        }

        return $this->render('Offers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param Reservation $record
     * @param Request $request
     * @return Response
     */
    public function editAction(Reservation $record, Request $request)
    {
        $originalServices = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($record->getServices() as $service) {
            $originalServices[] = $service;
        }

        $form = $this->createForm(OfferFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            foreach ($originalServices as $service) {
                if (!$record->getServices()->contains($service)) {
                    $manager->remove($service);
                }
            }

            $record->setVersion($record->getVersion() + 1);

            $manager->flush();

            return $this->redirect($this->generateUrl('app_offers_index'));
        }

        return $this->render('Offers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/change-cancel-state", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return JsonResponse
     */
    public function changeCancelStateAction(Reservation $record)
    {
        $record->setIsCancelled(!$record->getIsCancelled());

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }
    
    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param Reservation $record
     * @return JsonResponse
     */
    public function deleteAction(Reservation $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($record);
        $manager->flush();

        return new JsonResponse(array('result' => 'success'));
    }

    /**
     * @Route("/get-nights", options={"expose": true})
     * @Method({"post"})
     * @return JsonResponse
     */
    public function getNightsAction(Request $request)
    {
        $from = \DateTime::createFromFormat('d/m/Y', preg_replace('/ \d{2}:\d{2}/', '', $request->get('from')));
        $response = array(
            'id' => $request->query->get('id')
        );
        
        if ($request->request->has('to')) {
            $to = \DateTime::createFromFormat('d/m/Y', preg_replace('/ \d{2}:\d{2}/', '', $request->get('to')));
            
            $diff = date_diff($to, $from, true);
            
            $response['nights'] = $diff->days;
        } elseif ($request->request->has('nights')) {
            $to = $from->add(new \DateInterval(sprintf('P%sD', $request->request->get('nights'))));
            
            $response['to'] = $to->format('d/m/Y H:i');
        }
        
        return new JsonResponse($response);
    }
    
    /**
     * @Route("/{id}/change-state", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return JsonResponse
     */
    public function changeStateAction(Reservation $record)
    {
        $record->setState($record->getState() === Reservation::STATE_RESERVATION ? Reservation::STATE_OFFER : Reservation::STATE_RESERVATION);

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }

    /**
     * @Route("/get-client-contacts", options={"expose": true})
     * @Method({"get"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientContactsAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $query = $manager->createQuery('SELECT cc FROM AppBundle:ClientContact cc WHERE cc.client = :client ORDER BY cc.fullName')
                ->setParameter('client', $request->get('client'))
                ;
        $elements = array_map(function(\AppBundle\Entity\ClientContact $record) {
            return array(
                'id' => $record->getId(),
                'text' => $record->getFullname()
            );
        }, $query->getResult());

        return new JsonResponse(array(
            'elements' => $elements
        ));
    }

    /**
     * @Route("/search-service", options={"expose": true})
     * @Method({"get"})
     * @return Response
     */
    public function searchServiceAction()
    {
        $cupos = $this->container->getParameter('app.hotel.cupos');
        $models = $this->container->getParameter('app.contract.models');

        return $this->render('Offers/search_service.html.twig', array(
            'cupos' => $cupos,
            'models' => $models
        ));
    }

    /**
     * @Route("/search-service/{model}", requirements={"model": "[a-z\-]+"})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function searchServiceByModelAction($model, Request $request)
    {
        $response = array(
            'result' => 'success'
        );
        $manager = $this->getDoctrine()->getManager();

        $from = $request->get('from') ? \DateTime::createFromFormat('d/m/Y H:i:s', $request->get('from') . ' 00:00:00') : null;
        $to = $request->get('to') ? \DateTime::createFromFormat('d/m/Y H:i:s', $request->get('to') . ' 00:00:00') : null;

        switch ($model) {
            case 'hotel':
                $qb = $manager->getRepository('AppBundle:ContractFacility')
                        ->createQueryBuilder('f')
                        ->select('f.id, f.name, s.id AS supplierId, s.name AS supplierName')
                        ->join('f.contract', 'c')
                        ->join('c.supplier', 's')
                        ->orderBy('f.name')
                        ;

                $andX = $qb->expr()->andX($qb->expr()->eq('c.model', $qb->expr()->literal('hotel')));

                if (!$from && !$to) {
                    $andX->add($qb->expr()->andX(
                            $qb->expr()->isNull('c.startAt'),
                            $qb->expr()->isNull('c.endAt')
                            ));
                } else {
                    if ($from) {
                        $andX->add($qb->expr()->lte('c.startAt', $qb->expr()->literal($from->format('Y-m-d'))));
                    }
                    if ($to) {
                        $andX->add($qb->expr()->gte('c.endAt', $qb->expr()->literal($to->format('Y-m-d'))));
                    }
                }

                $qb->where($andX);

                $hotels = array_map(function($record) {
                    return array(
                        'id'    => $record['id'],
                        'text'  => $record['name'],
                        'supplier' => array(
                            'id' => $record['supplierId'],
                            'name' => $record['supplierName']
                        )
                    );
                }, $qb->getQuery()->getResult());

                $response['hotels'] = $hotels;
                break;

            default:
                $response['result'] = 'error';
                $response['error_reason'] = 'Model no implemented yet';
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/get-hotel-prices")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function  getHotelPricesAction(Request $request)
    {
        $from = $request->get('from') ? \DateTime::createFromFormat('d/m/Y H:i:s', $request->get('from') . ' 00:00:00') : null;
        $to = $request->get('to') ? \DateTime::createFromFormat('d/m/Y H:i:s', $request->get('to') . ' 00:00:00') : null;

        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:ContractHotelPrice')
                ->createQueryBuilder('p')
                ->join('p.season', 's')
                ->join('p.contract', 'c')
                ->join('s.facility', 'f')
                ;
        $andX = $qb->expr()->andX();

        if ($request->get('hotel')) {
            $andX->add($qb->expr()->eq('f.id', $qb->expr()->literal($request->get('hotel'))));
        }
        if ($from) {
            $andX->add($qb->expr()->andX(
                    $qb->expr()->lte('c.startAt', $qb->expr()->literal($from->format('Y-m-d'))),
                    $qb->expr()->lte('s.fromDate', $qb->expr()->literal($from->format('Y-m-d')))
                    ));
        }
        if ($to) {
            $andX->add($qb->expr()->andX(
                    $qb->expr()->gte('c.endAt', $qb->expr()->literal($to->format('Y-m-d'))),
                    $qb->expr()->gte('s.toDate', $qb->expr()->literal($to->format('Y-m-d')))
                    ));
        }

        $andX->add($qb->expr()->eq('p.cupo', $qb->expr()->literal($request->get('pax'))));

        $qb->where($andX);

        return $this->render('Offers/prices_results.html.twig', array(
            'query' => $qb->getQuery(),
            'quantity' => $request->get('quantity'),
            'nights' => $to->diff($from, true)->days
        ));
    }

    /**
     * @Route("/get-service-prices")
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function getServicePricesAction(Request $request)
    {
        $from = $request->get('from') ? \DateTime::createFromFormat('d/m/Y H:i:s', $request->get('from') . ' 00:00:00') : null;
        $to = $request->get('to') ? \DateTime::createFromFormat('d/m/Y H:i:s', $request->get('to') . ' 00:00:00') : null;

        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:ContractTopService')
                ->createQueryBuilder('ts')
                ->join('ts.contract', 'c')
                ;
        $andX = $qb->expr()->andX($qb->expr()->eq('c.model', $qb->expr()->literal($request->get('model'))));

        if ($from) {
            $andX->add($qb->expr()->orX(
                    $qb->expr()->lte('ts.startAt', $qb->expr()->literal($from->format('Y-m-d'))),
                    $qb->expr()->isNull('ts.startAt')
                    ));
        }
        if ($to) {
            $andX->add($qb->expr()->orX(
                    $qb->expr()->gte('ts.endAt', $qb->expr()->literal($to->format('Y-m-d'))),
                    $qb->expr()->isNull('ts.endAt')
                    ));
        }

        $qb->where($andX);

        return $this->render('Offers/topservice_results.html.twig', array(
            'query' => $qb->getQuery(),
            'quantity' => $request->get('quantity', 1)
        ));
    }

    /**
     * @Route("/{id}/send-client-version", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param Reservation $record
     * @return Response
     */
    public function sendClientVersionAction(Reservation $record)
    {
        $report = $this->container->get('report_factory')->createReport(\AppBundle\Lib\Reports\Offer::class, array(
            'offer' => $record
        ));

        return new \AppBundle\Lib\Reports\ReportResponse($report);
    }

    /**
     * @todo Revisar si este método se utiliza, sino, borrar
     * @Route("/filter-top-services")
     * @Method({"post"})
     */
    public function filterTopServicesAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $builder = $manager->getRepository('AppBundle:ContractTopService')
                ->createQueryBuilder('s')
                ->orderBy('s.price', 'DESC');

        return new JsonResponse(array(
            'results' => array_map(function($record) {
                return array(
                    'id' => $record->getId(),
                    'text' => sprintf('%s - %0.2f', $record->getName(), $record->getPrice()),
                    'price' => sprintf('%0.2f', $record->getPrice()),
                    'supplier' => array(
                        'id' => $record->getContract()->getSupplier()->getId(),
                        'name' => $record->getContract()->getSupplier()->getName()
                    )
                );
            }, $builder->getQuery()->getResult())
        ));
    }

    /**
     * @Route("/{id}/download-itinerary-document", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return BinaryFileResponse
     */
    public function downloadItineraryDocumentAction(Reservation $record)
    {
        if (null === $record->getOfferSummaryFilename()) {
            throw $this->createNotFoundException('This record has no itinerary document');
        }

        $filename = $this->container->getParameter('kernel.root_dir') .
                '/../web/uploads/offers/' . $record->getOfferSummaryFilename();

        return new BinaryFileResponse($filename, 200, array(
            'Content-Description' => 'File transfer',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $record->getOfferSummaryOriginalFilename()),
            'Content-Type' => 'application/octect-stream',
            'Expires' => 0,
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Content-length' => filesize($filename)
        ));
    }

    /**
     * @Route("/{id}/print-preview")
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @Method({"get"})
     * @return Response
     */
    public function printPreviewAction(Reservation $record)
    {
        $report = new \AppBundle\Lib\Reports\BookingReview(array(
            'record' => $record,
            'manager' => $this->getDoctrine()->getManager(),
            'models' => $this->container->getParameter('app.contract.models'),
            'locale' => $this->container->get('request')->getLocale(),
            'translator' => $this->container->get('translator')
        ));

        return new StreamedResponse(function() use($report) {
            $content = $report->getContent();
            file_put_contents('php://output', $content);
        }, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s booking review.pdf"', $record->getName())
        ));
    }

    /**
     * @Route("/{id}/print-cash")
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @Method({"get"})
     * @return Response
     */
    public function printCash(Reservation $record)
    {
        $book = new \AppBundle\Lib\Excel\Cash(array(
            'record' => $record,
            'phpexcel' => $this->container->get('phpexcel'),
            'translator' => $this->container->get('translator'),
            'locale' => $this->container->get('request')->getLocale()
        ));

        $response = $book->getBookContent();
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('Cash %s v%s.xls', $record->getName(), $record->getVersion())
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/{id}/print-vouchers", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return Response
     */
    public function printVouchersAction(Reservation $record)
    {
        $pdf = new \AppBundle\Lib\Reports\Vouchers(array(
            'record' => $record,
            'translator' => $this->container->get('translator'),
            'locale' => $this->container->get('request')->getLocale(),
            'images_dir' => $this->container->getParameter('kernel.root_dir') . '/../web/images',
            'models' => $this->container->getParameter('app.contract.models')
        ));

        return new StreamedResponse(function() use($pdf) {
            file_put_contents('php://output', $pdf->getContent());
        }, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="Vouchers %s v%s.pdf"', $record->getName(), $record->getVersion())
        ));
    }
    
    /**
     * @Route("/get-places", options={"expose": true})
     * @Method({"get"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlacesAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:Place')
                ->createQueryBuilder('p')
                ->orderBy('p.name');

        if ($request->get('q')) {
            $qb->where($qb->expr()->like('p.name', $qb->expr()->literal(sprintf('%%%s%%', $request->get('q')))));
        }
        
        return new JsonResponse(array(
            'data' => array_map(function($record) {
                return array(
                    'id' => $record->getId(),
                    'text' => $record->getName(),
                    'postalAddress' => $record->getPostalAddress()
                );
            }, $qb->getQuery()->getResult())
        ));
    }
}
