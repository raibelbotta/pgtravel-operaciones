<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Client;
use AppBundle\Form\Type\ClientFormType;

/**
 * Description of ClientsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/clients")
 */
class ClientsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('Clients/index.html.twig');
    }

    /**
     * @Route("/get-data", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Client')
                ->createQueryBuilder('c')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');

        $andX = $qb->expr()->andX();

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->like('c.fullName', $qb->expr()->literal('%' . $search['value'] . '%')));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'fullname') {
                    return 'c.fullName';
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
                $record->getFullName(),
                $twig->render('Clients/_actions.html.twig', array(
                    'record' => $record
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
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Client")
     * @return Response
     */
    public function viewAction(Client $record)
    {
        return $this->render('Clients/view.html.twig', array(
            'record' => $record
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
        $client = new Client();
        $form = $this->createForm(ClientFormType::class, $client);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($client);
            $manager->flush();

            return $this->redirect($this->generateUrl('app_clients_index'));
        }

        return $this->render('Clients/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("client", class="AppBundle\Entity\Client")
     * @param Client $client
     * @param Request $request
     * @return Response
     */
    public function editAction(Client $client, Request $request)
    {
        $form = $this->createForm(ClientFormType::class, $client);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return $this->redirect($this->generateUrl('app_clients_index'));
        }

        return $this->render('Clients/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Client")
     * @param Client $record
     * @return JsonResponse
     */
    public function deleteAction(Client $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($record);
        $manager->flush();

        return new JsonResponse(array('result' => 'success'));
    }
}
