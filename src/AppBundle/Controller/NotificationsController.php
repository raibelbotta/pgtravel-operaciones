<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\ReservationService;
use AppBundle\Form\Type\ConfirmServiceFormType;
use AppBundle\Form\Type\ServiceConfirmFilterFormType;

/**
 * Description of NotificationsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/service-confirm")
 */
class NotificationsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $session = $this->container->get('session');
        $form = $this->createForm(ServiceConfirmFilterFormType::class, $session->get('notifications.filter', array(
            'state' => 'no'
        )));

        return $this->render('Notifications/index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $qb = $manager->getRepository('AppBundle:ReservationService')
                ->createQueryBuilder('rs')
                ->join('rs.reservation', 'r')
                ->leftJoin('r.client', 'c')
                ->leftJoin('r.operator', 'u')
                ->leftJoin('rs.supplier', 's')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter');

        $session = $request->getSession();
        $session->set('notifications.filter', $filter);

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('r.state', $qb->expr()->literal(Reservation::STATE_RESERVATION)),
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false))
                );

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->orX(
                    $qb->expr()->like('r.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value']))),
                    $qb->expr()->orX(
                            $qb->expr()->andX(
                                    $qb->expr()->isNull('r.client'),
                                    $qb->expr()->like('r.directClientFullName', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                                    )
                            ),
                            $qb->expr()->andX(
                                    $qb->expr()->isNotNull('r.client'),
                                    $qb->expr()->like('c.fullName', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                                    ),
                    $qb->expr()->andX(
                            $qb->expr()->isNotNull('r.operator'),
                            $qb->expr()->orX(
                                    $qb->expr()->like('u.firstName', $qb->expr()->literal(sprintf('%%%s%%', $search['value']))),
                                    $qb->expr()->like('u.lastName', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                                    )
                            ),
                    $qb->expr()->andX(
                            $qb->expr()->isNotNull('rs.supplier'),
                            $qb->expr()->like('s.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                            ),
                    $qb->expr()->like('rs.name', $qb->expr()->literal('%' . $search['value'] . '%')),
                    $qb->expr()->like('rs.supplierReference', $qb->expr()->literal('%' . $search['value'] . '%'))
                    ));
        }

        $qb->where($andX);

        $form = $this->createForm(ServiceConfirmFilterFormType::class);
        $form->submit($request->request->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
        $this->container->get('session')->set('notifications.filter', $form->getData());
        
        if ($orders) {
            $column = call_user_func(function($name) {
                if ('name' === $name) {
                    return 'r.name';
                } elseif ('operator' === $name) {
                    return 'u.firstName';
                } elseif ('client' === $name) {
                    return 'c.fullName';
                } elseif ('supplier' === $name) {
                    return 's.name';
                } elseif ('service' === $name) {
                    return 'rs.name';
                } elseif ('startAt' === $name) {
                    return 'rs.startAt';
                } elseif ('endAt' === $name) {
                    return 'rs.endAt';
                } elseif ('reference' === $name) {
                    return 'rs.supplierReference';
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
                $record->getReservation()->getName(),
                null !== $record->getReservation()->getOperator() ? (string) $record->getReservation()->getOperator() : '',
                null !== $record->getReservation()->getClient() ? (string) $record->getReservation()->getClient() : $record->getReservation()->getDirectClientFullName(),
                null !== $record->getSupplier() ? $record->getSupplier()->getName() : '',
                $record->getName(),
                $record->getStartAt()->format('d/m/Y'),
                $record->getEndAt()->format('d/m/Y'),
                $record->getSupplierReference(),
                $twig->render('Notifications/_actions.html.twig', array('record' => $record))
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
     * @Route("/{id}/change-state", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\ReservationService")
     * @param Request $request
     * @return Response
     */
    public function changeStateAction(ReservationService $record, Request $request)
    {
        $form = $this->createForm(ConfirmServiceFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $record->setIsNotified(true);

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return new JsonResponse(array(
                'result' => 'success'
            ));
        }

        return $this->render('Notifications/confirm.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
