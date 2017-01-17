<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Reservation;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\Type\ReservationFormType;

/**
 * Description of BookingsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/bookings")
 */
class BookingsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('Bookings/index.html.twig');
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

        $qb = $manager->getRepository('AppBundle:Reservation')
                ->createQueryBuilder('r')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter');

        $andX = $qb->expr()->andX($qb->expr()->eq('r.state', $qb->expr()->literal(Reservation::STATE_RESERVATION)));

        if (isset($filter['cancelled']) && $filter['cancelled']) {
            $andX->add($qb->expr()->eq('r.isCancelled', $qb->expr()->literal($filter['cancelled'] == 'yes')));
        }

        if (is_array($search) && isset($search['value']) && $search['value']) {

        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) {
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
                $record->getName(),
                $record->getStartAt()->format('d/m/Y'),
                $record->getEndAt()->format('d/m/Y'),
                $twig->render('Bookings/_actions.html.twig', array('record' => $record))
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
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param Reservation $record
     * @return Response
     */
    public function viewAction(Reservation $record)
    {
        return $this->render('Bookings/view.html.twig', array(
            'record' => $record
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param Reservation $record
     * @return Response
     */
    public function editAction(Reservation $record, Request $request)
    {
        $form = $this->createForm(ReservationFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $this->addFlash('notice', 'Record has been saved successfuly');

            return $this->redirect($this->generateUrl('app_bookings_index'));
        }

        return $this->render('Bookings/edit.html.twig', array(
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
}
