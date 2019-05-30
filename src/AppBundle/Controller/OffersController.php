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
use AppBundle\Form\Type\BookingListFilterFormType;
use AppBundle\Form\Type\ServiceFilter as ServiceFilters;

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

        $form = $this->createForm(BookingListFilterFormType::class, $session->get('offers.filter', array(
            'startAt' => array(
                'left_date' => new \DateTime('now')
            ),
            'isCancelled' => 'no'
        )));

        return $this->render('Offers/index.html.twig', array(
            'form' => $form->createView()
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

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX = $qb->expr()->andX();
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

            $qb->where($andX);
        }

        $form = $this->createForm(BookingListFilterFormType::class);
        $form->submit($request->request->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
        $this->get('session')->set('offers.filter', $form->getData());

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

            return $this->redirect($this->generateUrl('app_offers_index'));
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
        $models = $this->container->getParameter('app.contract.models');
        $manager = $this->getDoctrine()->getManager();

        return $this->render('Offers/search_service.html.twig', array(
            'cupos' => $this->container->getParameter('app.hotel.cupos'),
            'models' => $models,
            'provinces' => $manager->createQuery('SELECT p FROM AppBundle:Province AS p ORDER BY p.name'),
            'filters' => array(
                'hotel' => $this->createForm(ServiceFilters\HotelFilterFormType::class)->createView(),
                'private_house' => $this->createForm(ServiceFilters\PrivateHouseFilterFormType::class)->createView()
            )
        ));
    }

    /**
     * @Route("/get-hotel-prices", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function getHotelPricesAction(Request $request)
    {
        $columns = $request->get('columns');
        $order = $request->get('order', array());
        $search = $request->get('search', array());

        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:ContractHotelPrice')
                ->createQueryBuilder('p')
                ->join('p.season', 's')
                ->join('p.contract', 'c')
                ->join('s.facility', 'f')
                ->join('p.room', 'r')
                ;

        $form = $this->createForm(ServiceFilters\HotelFilterFormType::class);
        $form->submit($request->request->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);

        $andX = $qb->expr()->andX();

        if ($search['value']) {
            $andX->add($qb->expr()->like('f.name', ':q'));
            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
        }

        if ($andX->count() > 0) {
            $qb->andWhere($andX);
        }

        if ($order) {
            $column = call_user_func(function($name) {
                if ($name == 'hotel') {
                    return 'f.name';
                } elseif ($name == 'room') {
                    return 'r.name';
                } elseif ($name == 'season') {
                    return 's.startAt';
                } elseif ($name == 'price') {
                    return 'p.value';
                } elseif ($name == 'total') {
                    return 'p.value';
                }
                return null;
            }, $columns[$order[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($order[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));
        $total = $pagination->getTotalItemCount();

        $submittedData = $form->getData();
        $nights = $submittedData['dates']['left_date'] && $submittedData['dates']['right_date'] ? $submittedData['dates']['right_date']->diff($submittedData['dates']['left_date'], true)->days : 0;
        $template = $this->container->get('twig')->loadTemplate('Offers/_hotel_prices_row.html.twig');

        $data = array_map(function(\AppBundle\Entity\ContractHotelPrice $price)  use ($template, $submittedData, $nights) {
            $qty = $submittedData['quantity'] ? $submittedData['quantity'] : 0;
            return array(
                $template->renderBlock('facility', array('price' => $price)),
                (string) $price->getRoom(),
                (string) $price->getSeason(),
                $price->getPlan(),
                $price->getCupo(),
                $template->renderBlock('value', array('price' => $price)),
                $template->renderBlock('total', array('value' => $price->getValue() * $qty * $nights)),
                $template->renderBlock('actions', array(
                    'price' => $price,
                    'quantity' => $qty,
                    'nights' => $nights
                ))
            );
        }, $pagination->getItems());

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/get-private-house-prices", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function getPrivateHousePricesAction(Request $request)
    {
        $columns = $request->get('columns');
        $order = $request->get('order', array());
        $search = $request->get('search', array());

        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:ContractPrivateHousePrice')
                ->createQueryBuilder('p')
                ->join('p.facility', 'f')
                ->join('f.contract', 'c')
                ->join('c.supplier', 's')
                ;
        $andX = $qb->expr()->andX($qb->expr()->eq('c.model', $qb->expr()->literal('private-house')));
        if ($search['value']) {
            $andX->add($qb->expr()->orX(
                $qb->expr()->like('c.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value']))),
                $qb->expr()->like('s.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                ));
        }
        $qb->where($andX);

        $form = $this->createForm(ServiceFilters\PrivateHouseFilterFormType::class);
        $form->submit($request->request->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);

        if ($order) {
            $column = call_user_func(function($name) {
                if ('supplier' === $name) {
                    return 's.name';
                } elseif ('room' === $name) {
                    return 'f.name';
                } elseif ('plan' === $name) {
                    return 'f.mealPlan';
                } elseif ('price' === $name) {
                    return 'p.value';
                }
                return null;
            }, $columns[$order[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($order[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));
        $total = $pagination->getTotalItemCount();

        $template = $this->container->get('twig')
                ->load('Offers/_private_house_prices_row.html.twig');
        $submittedData = $form->getData();
        $data = array_map(function(\AppBundle\Entity\ContractPrivateHousePrice $record) use($template, $submittedData) {
            return array(
                $template->renderBlock('name', array('record' => $record)),
                $record->getFacility()->getName(),
                $record->getFacility()->getMealPlan(),
                $template->renderBlock('price', array('record' => $record)),
                $template->renderBlock('total_price', array(
                    'record' => $record,
                    'quantity' => $submittedData['quantity'],
                    'nights' => $submittedData['nights']
                )),
                $template->renderBlock('actions', array(
                    'record' => $record,
                    'quantity' => $submittedData['quantity'],
                    'nights' => $submittedData['nights']
                ))
            );
        }, $pagination->getItems());

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));

        return $this->render('Offers/private_house_prices_results.html.twig', array(
            'query' => $qb->getQuery(),
            'quantity' => $request->get('quantity', 0),
            'nights' => $from && $to ? $to->diff($from, true)->days : 0
        ));
    }

    /**
     * @Route("/get-car-rental-prices", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarRentalPricesAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:ContractCarRentalPrice')
                ->createQueryBuilder('p')
                ->join('p.contract', 'c')
                ->join('p.dayRange', 'r')
                ->join('r.seasson', 's')
                ->join('s.dates', 'd')

                ;
        $andX = $qb->expr()->andX($qb->expr()->eq('c.model', ':model'));
        $qb->setParameter('model', 'car-rental');

        $filter = $request->get('filter', array());

        if (isset($filter['from']) && $filter['from']) {
            $from = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $filter['from']);
            $andX->add($qb->expr()->andX(
                    $qb->expr()->lte('d.startAt', ':from'),
                    $qb->expr()->gte('d.endAt', ':from')
                    ));
            $qb->setParameter('from', $from->format('Y-m-d 00:00:00'));
        }
        if (isset($from) && isset($filter['to']) && $filter['to']) {
            $to = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $filter['to']);
            $days = $to->diffInDays($from);
            $plusHour = \Carbon\Carbon::instance($from)->addDays($days)->diffInHours($to);
            if ($plusHour > 0) {
                $days++;
            }
            $andX->add($qb->expr()->andX(
                    $qb->expr()->lte('r.beginDay', ':days'),
                    $qb->expr()->gte('r.endDay', ':days')
                    ));
            $qb->setParameter('days', $days);
        }

        $qb->where($andX);

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

        $list = $pagination->getItems();
        $total = $pagination->getTotalItemCount();

        $template = $this->container->get('twig')->loadTemplate('Offers/_rental_car_prices_row.html.twig');
        $days = isset($days) ? $days : null;

        $data = array_map(function(\AppBundle\Entity\ContractCarRentalPrice $price)  use ($template, $days) {
            return array(
                $price->getContract()->getSupplier()->getName(),
                $price->getCategory()->getName(),
                $template->renderBlock('cost', array('record' => $price)),
                $template->renderBlock('price', array('record' => $price, 'days' => $days)),
                $template->renderBlock('actions', array('record' => $price))
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
     * @Route("/get-transport-prices", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function getTransportPricesAction(Request $request)
    {
        $columns = $request->get('columns');
        $filter = $request->get('filter', array());
        $order = $request->get('order', array());
        $search = $request->get('search', array());

        $from = isset($filter['from']) && $filter['from'] ? \DateTime::createFromFormat('d/m/Y H:i:s', $filter['from'] . ' 00:00:00') : null;
        $to = isset($filter['to']) && $filter['to'] ? \DateTime::createFromFormat('d/m/Y H:i:s', $filter['to'] . ' 00:00:00') : null;

        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:ContractTopService')
                ->createQueryBuilder('s')
                ->join('s.contract', 'c')
                ;

        $andX = $qb->expr()->andX($qb->expr()->eq('c.model', ':model'));
        $qb->setParameter('model', 'transport');

        if (isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->like('s.name', ':q'));
            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($order) {
            $column = call_user_func(function($name) {
                if ($name == 'service') {
                    return 's.name';
                }
                return null;
            }, $columns[$order[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($order[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

        $list = $pagination->getItems();
        $total = $pagination->getTotalItemCount();

        $twig = $this->container->get('twig');
        $data = array_map(function(\AppBundle\Entity\ContractTopService $service)  use ($filter, $from, $to, $twig) {
            if ($from && $to) {
                $days = $to->diff($from)->days + 1;
                if (isset($filter['addhalfday']) && $filter['addhalfday']) {
                    $days += 0.5;
                }
            } else {
                $days = 0;
            }

            $qty = isset($filter['quantity']) && $filter['quantity'] ? $filter['quantity'] : 0;
            return array(
                $service->getName(),
                (string) $service->getContract()->getSupplier(),
                sprintf('%0.2f', $service->getPrice()),
                sprintf('%0.2f', $service->getPrice() * $qty * $days),
                $twig->render('Offers/_transport_prices.html.twig', array(
                    'record' => $service,
                    'quantity' => $qty,
                    'days' => $days
                ))
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
     * @Route("/get-service-prices", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return Response
     */
    public function getGeneralServicePricesAction(Request $request)
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
                    $qb->expr()->andX(
                            $qb->expr()->isNotNull('ts.startAt'),
                            $qb->expr()->lte('ts.startAt', $qb->expr()->literal($from->format('Y-m-d')))
                            ),
                    $qb->expr()->isNull('ts.startAt')
                    ));
        }
        if ($to) {
            $andX->add($qb->expr()->orX(
                    $qb->expr()->andX(
                            $qb->expr()->isNotNull('ts.endAt'),
                            $qb->expr()->gte('ts.endAt', $qb->expr()->literal($to->format('Y-m-d')))
                            ),
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
     * @Route("/{id}/print-booking-review/{format}", requirements={"id": "\d+", "format": "pdf|xls"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @Method({"get"})
     * @return Response
     */
    public function printBookingReviewAction(Reservation $record, $format)
    {
        if ('pdf' === $format) {
            $report = new \AppBundle\Lib\Reports\BookingReview(array(
                'record' => $record,
                'manager' => $this->getDoctrine()->getManager(),
                'models' => $this->container->getParameter('app.contract.models'),
                'locale' => $this->container->get('request')->getLocale(),
                'translator' => $this->container->get('translator')
            ));

            $response = new StreamedResponse(function() use($report) {
                $content = $report->getContent();
                file_put_contents('php://output', $content);
            }, 200, array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s booking review.pdf"', $record->getName())
            ));
        } elseif ('xls' === $format) {
            $book = new \AppBundle\Lib\Excel\BookingReview(array(
                'record' => $record,
                'phpexcel' => $this->container->get('phpexcel'),
                'manager' => $this->getDoctrine()->getManager(),
                'models' => $this->container->getParameter('app.contract.models'),
                'locale' => $this->container->get('request')->getLocale(),
                'translator' => $this->container->get('translator')
            ));

            $response = $book->getBookContent();
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                sprintf('Cash %s v%s.xls', $this->sanitizeName($record->getName()), $record->getVersion())
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);
        }

        return $response;
    }

    private function sanitizeName($originalName)
    {
        $resultName = preg_replace('/[^A-Za-z0-9\s]/', '', $originalName);

        if (empty($resultName)) {
            $resultName = uniqid();
        }

        return $resultName;
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
        return $this->render('Offers/vouchers.html.twig', array(
            'record' => $record
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
