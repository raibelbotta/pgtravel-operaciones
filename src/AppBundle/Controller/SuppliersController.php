<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\Type\SupplierFormType;
use AppBundle\Entity\Supplier;

/**
 * Description of SuppliersController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/suppliers")
 * @Security("has_role('ROLE_ADMIN')")
 */
class SuppliersController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('Suppliers/index.html.twig');
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

        $qb = $em->getRepository('AppBundle:Supplier')
                ->createQueryBuilder('s')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');

        $andX = $qb->expr()->andX();

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->like('s.name', $qb->expr()->literal('%' . $search['value'] . '%')));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 's.name';
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
        $phoneNumberUtil = $this->container->get('libphonenumber.phone_number_util');
        $data = array_map(function($record) use($twig, $phoneNumberUtil) {
            return array(
                $record->getName(),
                $record->getFixedPhone() ? $phoneNumberUtil->format($record->getFixedPhone(), \libphonenumber\PhoneNumberFormat::INTERNATIONAL) : '',
                $twig->render('Suppliers/_actions.html.twig', array(
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
     * @ParamConverter("record", class="AppBundle\Entity\Supplier")
     * @param Supplier $record
     * @return Response
     */
    public function viewAction(Supplier $record)
    {
        return $this->render('Suppliers/view.html.twig', array(
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
        $form = $this->createForm(SupplierFormType::class, new Supplier());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved sucessfuly!'));

            return $this->redirect($this->generateUrl('app_suppliers_index'));
        }

        return $this->render('Suppliers/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Supplier")
     * @param Supplier $record
     * @param Request $request
     * @return Response
     */
    public function editAction(Supplier $record, Request $request)
    {
        $form = $this->createForm(SupplierFormType::class, $record);

        $originalEmployees = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($record->getEmployees() as $employee) {
            $originalEmployees[] = $employee;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($originalEmployees as $employee) {
                if (false === $record->getEmployees()->contains($employee)) {
                    $em->remove($employee);
                }
            }

            $em->flush();

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved sucessfuly!'));

            return $this->redirect($this->generateUrl('app_suppliers_index'));
        }

        return $this->render('Suppliers/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Supplier")
     * @param Supplier $record
     * @return JsonResponse
     */
    public function deleteAction(Supplier $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return new JsonResponse(array(
            'result' => 'success'
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
