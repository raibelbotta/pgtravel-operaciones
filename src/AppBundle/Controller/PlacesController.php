<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\Type\PlaceFormType;
use AppBundle\Entity\Place;

/**
 * Description of PlaceController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/places")
 */
class PlacesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $session = $this->container->get('session');
        $filter = $session->get('places.filter', array(
            'q' => ''
        ));

        return $this->render('Places/index.html.twig', array(
            'filter' => $filter
        ));
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method({"post"})
     * @param Request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Place')
                ->createQueryBuilder('p')
                ->leftJoin('p.province', 'pr')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter', array());

        $andX = $qb->expr()->andX();

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $filter['q'] = $search['value'];
            $andX->add($qb->expr()->orX(
                    $qb->expr()->like('p.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value']))),
                    $qb->expr()->like('p.postalAddress', $qb->expr()->literal(sprintf('%%%s%%', $search['value']))),
                    $qb->expr()->andX(
                        $qb->expr()->isNotNull('p.province'),
                        $qb->expr()->like('pr.name', $qb->expr()->literal(sprintf('%%%s%%', $search['value'])))
                        )
                    ));
        } else {
            $filter['q'] = '';
        }

        $session = $this->container->get('session');
        $session->set('places.filter', $filter);

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'p.name';
                } elseif ($name == 'postalAddress') {
                    return 'p.postalAddress';
                } elseif ($name == 'province') {
                    return 'pr.name';
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

        $twig = $this->container->get('templating');
        $data = array_map(function($record) use($twig) {
            return array(
                $record->getName(),
                $record->getPostalAddress(),
                (string) $record->getProvince(),
                $twig->render('Places/_actions.html.twig', array(
                    'record' => $record
                ))
            );
        }, $list);

        return new JsonResponse(array(
            'data' => $data,
            'draw' => (integer) $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

     /**
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Place")
     * @param Place $record
     * @return Response
     */
     public function viewAction(Place $record)
     {
        return $this->render('Places/view.html.twig', array(
            'record' => $record
        ));
     }

    /**
     * @Route("/new")
     * @Method({"get", "post"})
     * @param Request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $record = new Place();
        $form = $this->createForm(PlaceFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($record);
            $manager->flush();

            $this->addFlash('notice', $this->container->get('translator')->trans('Record saved sucessfuly!'));

            return $this->redirect($this->generateUrl('app_places_index'));
        }

        return $this->render('Places/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Place")
     * @param Place $record
     * @param Request $request
     * @return Response
     */
    public function editAction(Place $record, Request $request)
    {
        $form = $this->createForm(PlaceFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $this->addFlash('notice', $this->container->get('translator')->trans('Record saved sucessfuly!'));

            return $this->redirect($this->generateUrl('app_places_index'));
        }

        return $this->render('Places/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Place")
     * @param Place $record
     * @return Response
     */
    public function deleteAction(Place $record)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($record);

        $manager->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }
}
