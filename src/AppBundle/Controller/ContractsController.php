<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Contract;
use AppBundle\Form\Type\ContractFormType;

/**
 * Description of ContractsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/contracts")
 * @Security("has_role('ROLE_ADMIN')")
 */
class ContractsController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     */
    public function indexAction()
    {
        return $this->render('Contracts/index.html.twig');
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

        $qb = $em->getRepository('AppBundle:Contract')
                ->createQueryBuilder('c')
                ->join('c.supplier', 's')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');

        $andX = $qb->expr()->andX();

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->like('c.name', $qb->expr()->literal('%' . $search['value'] . '%')));
        }

        if ($andX->count() > 0) {
            $qb->where($andX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'c.name';
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

        $translator = $this->container->get('translator');

        $getModelName = function(Contract $record) {
            $models = array(
                'Hotel'         => 'hotel',
                'Transport'     => 'transport',
                'Car rental'    => 'car-rental',
                'Restaurant'    => 'restaurant',
                'Optionals'     => 'optionals',
                'Guide'         => 'guide',
                'Other'         => 'other'
            );

            return array_search($record->getModel(), $models);
        };

        $data = array_map(function($record) use($getModelName) {
            return array(
                '<input type="checkbox" class="flat">',
                $record->getName(),
                $getModelName($record),
                (string) $record->getSupplier(),
                $record->getSignedAt() ? $record->getSignedAt()->format('Y-m-d') : '',
                $record->getStartAt() ? $record->getStartAt()->format('Y-m-d') : '',
                $record->getEndAt() ? $record->getEndAt()->format('Y-m-d') : '',
                $this->renderView('Contracts/_actions.html.twig', array(
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
     * @Route("/new")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $record = new Contract();
        $record->setModel('other');
        $form = $this->createForm(ContractFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved successfuly!'));

            return $this->redirect($this->generateUrl('app_contracts_index'));
        }
        
        return $this->render('Contracts/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\Contract")
     * @param Request $request
     * @return Response
     */
    public function editAction(Contract $record, Request $request)
    {
        $form = $this->createForm(ContractFormType::class, $record);

        $originalTopServices = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($record->getTopServices() as $service) {
            $originalTopServices->add($service);
        }

        $originalAttachments = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($record->getAttachments() as $attachment) {
            $originalAttachments->add($attachment);
        }

        $originalFacilities = new \Doctrine\Common\Collections\ArrayCollection();
        $originalRooms = array();
        foreach ($record->getFacilities() as $facility) {
            $originalFacilities[] = $facility;
            $originalRooms[$facility->getId()] = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($facility->getRooms() as $room) {
                $originalRooms[$facility->getId()][] = $room;
            }
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($originalAttachments as $attachment) {
                if (false === $record->getAttachments()->contains($attachment)) {
                    $record->getAttachments()->removeElement($attachment);
                    $em->remove($attachment);
                }
            }
            foreach ($originalTopServices as $service) {
                if (false === $record->getTopServices()->contains($service)) {
                    $record->getTopServices()->removeElement($service);
                    $em->remove($service);
                }
            }

            foreach ($originalFacilities as $facility) {
                if (false === $record->getFacilities()->contains($facility)) {
                    $record->getFacilities()->removeElement($facility);
                    $em->remove($facility);
                } else {
                    foreach ($originalRooms[$facility->getId()] as $room) {
                        if (false === $facility->getRooms()->contains($room)) {
                            $facility->getRooms()->removeElement($room);
                            $em->remove($room);
                        }
                    }
                }
            }

            $em->flush();

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved successfuly!'));

            return $this->redirect($this->generateUrl('app_contracts_index'));
        }
        
        return $this->render('Contracts/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/prices", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Contract")
     * @Method({"get"})
     * @param Contract $record
     * @return Response
     */
    public function pricesAction(Contract $record)
    {
        $cupos = $this->container->getParameter('app.hotel.cupos');
        $manager = $this->getDoctrine()->getManager();

        $repo = $manager->getRepository('AppBundle:ContractHotelPrice');
        $prices = array();
        foreach ($repo->findBy(array('contract' => $record->getId())) as $price) {
            $prices[$price->getRoom()->getId()][$price->getPlan()][$price->getSeason()->getId()][$price->getCupo()] = $price->getValue();
        }

        return $this->render('Contracts/prices.html.twig', array(
            'record'    => $record,
            'cupos'     => $cupos,
            'prices'    => $prices
        ));
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"})
     * @Method({"post"})
     * @ParamConverter("record", class="AppBundle\Entity\Contract")
     * @param Contract $record
     * @return JsonResponse
     */
    public function deleteAction(Contract $record)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($record);
        $em->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }

    /**
     * @Route("/set-price")
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function setPriceAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $price = $manager->getRepository('AppBundle:ContractHotelPrice')->findOneBy(array(
            'contract' => $request->get('contract'),
            'room' => $request->get('room'),
            'plan' => $request->get('plan'),
            'season' => $request->get('season'),
            'cupo' => $request->get('cupo')
        ));

        if (!$price && $request->get('value')) {
            $price = new \AppBundle\Entity\ContractHotelPrice();
            $price
                    ->setContract($manager->find('AppBundle:Contract', $request->get('contract')))
                    ->setCupo($request->get('cupo'))
                    ->setPlan($request->get('plan'))
                    ->setRoom($manager->find('AppBundle:ContractFacilityRoom', $request->get('room')))
                    ->setSeason($manager->find('AppBundle:ContractFacilitySeason', $request->get('season')))
                    ->setValue($request->get('value'))
                    ;
            $manager->persist($price);
        } elseif ($price && !$request->get('value')) {
            $manager->remove($price);
        } elseif ($price) {
            $price->setValue($request->get('value'));
        }

        $manager->flush();

        return new JsonResponse(array(
            'inputId'   => $request->get('inputId'),
            'value'     => $request->get('value') ? sprintf('%0.2f', $price->getValue()) : ''
        ));
    }
}
