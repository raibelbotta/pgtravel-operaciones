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

/**
 * Description of NotificationsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/notifications")
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
        if (null === ($filter = $session->get('notifications.filter'))) {
            $filter = array('state' => 'no');
            $session->set('notifications.filter', $filter);
        }

        return $this->render('Notifications/index.html.twig', array(
            'filter' => $filter
        ));
    }

    /**
     * @Route("/get-data")
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
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter');

        $session = $request->getSession();
        $session->set('notifications.filter', $filter);

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('r.state', $qb->expr()->literal(Reservation::STATE_RESERVATION)),
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false)),
                $qb->expr()->gte('rs.startAt', $qb->expr()->literal(date('Y/m/d')))
                );

        if (isset($filter['state']) && $filter['state']) {
            $andX->add($qb->expr()->eq('rs.isNotified', $qb->expr()->literal($filter['state'] == 'yes')));
        }

        if (is_array($search) && isset($search['value']) && $search['value']) {

        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'service') {
                    return 'rs.name';
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
                $record->getSupplier()->getName(),
                $record->getName(),
                $record->getStartAt()->format('d/m/Y'),
                $record->getEndAt()->format('d/m/Y'),
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
     * @return JsonResponse
     */
    public function changeStateAction(ReservationService $record, Request $request)
    {
        if (!$record->getIsNotified()) {
            $record
                    ->setIsNotified(true)
                    ;
        } else {
            $record
                    ->setIsNotified(false)
                    ;
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'result' => 'success'
            ));
        } else {
            return $this->redirect($this->generateUrl('app_notifications_index'));
        }
    }
}
