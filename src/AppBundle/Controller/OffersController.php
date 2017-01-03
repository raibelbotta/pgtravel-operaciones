<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Reservation;
use AppBundle\Form\Type\OfferFormType;

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
        return $this->render('Offers/index.html.twig');
    }

    /**
     * @Route("/get-data", options={"i18n": false})
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
        $qb->add('select', '(SELECT MIN(rs.startAt) FROM AppBundle:ReservationService rs JOIN rs.reservation rk WHERE rk.id = r.id ORDER BY rs.startAt ASC) AS startAt', true);
        $qb->add('select', '(CASE WHEN r.client IS NULL THEN r.directClientFullName ELSE c.fullName END) AS clientName', true);

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');

        $andX = $qb->expr()->andX($qb->expr()->eq('r.state', $qb->expr()->literal(Reservation::STATE_OFFER)));

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
        $data = array_map(function($record) use($twig) {
            return array(
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
            $service = new \AppBundle\Entity\ReservationAdministrativeCharge();
            $service
                    ->setName($k['name'])
                    ->setPax(0)
                    ->setNights(0)
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
        if (Reservation::STATE_OFFER !== $record->getState()) {
            throw $this->createNotFoundException();
        }

        $originalServices = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($record->getServices() as $service) {
            $originalServices[] = $service;
        }
        $originalAdministrativeCharges = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($record->getAdministrativeCharges() as $charge) {
            $originalAdministrativeCharges[] = $charge;
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
            foreach ($originalAdministrativeCharges as $charge) {
                if (!$record->getAdministrativeCharges()->contains($charge)) {
                    $manager->remove($charge);
                }
            }

            $manager->flush();

            return $this->redirect($this->generateUrl('app_offers_index'));
        }

        return $this->render('Offers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/cancel", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return JsonResponse
     */
    public function cancelAction(Reservation $record)
    {
        $record->setIsCancelled(true);

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
     * @Route("/{id}/promote", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return JsonResponse
     */
    public function promoteAction(Reservation $record)
    {
        $record->setState(Reservation::STATE_RESERVATION);

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }

    /**
     * @Route("/get-client-contacts")
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
     * @Route("/search-service")
     * @Method({"get"})
     * @return Response
     */
    public function searchServiceAction()
    {
        $cupos = $this->container->getParameter('app.hotel.cupos');
        $models = array(
            'hotel'         => 'Hotel',
            'transport'     => 'Transport',
            'car-rental'    => 'Car rental',
            'restaurant'    => 'Restaurant',
            'optionals'     => 'Optionals',
            'guide'         => 'Guide',
            'other'         => 'Other'
        );

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
}