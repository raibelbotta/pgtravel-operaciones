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
use Doctrine\Common\Collections\ArrayCollection;

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
        $types = array_combine(array_keys($this->container->getParameter('app.contract.models')), array_map(function($options) {
            return $options['display'];
        }, $this->container->getParameter('app.contract.models')));

        $session = $this->container->get('session');
        $filter = $session->get('contracts.filter', array());

        return $this->render('Contracts/index.html.twig', array(
            'types' => $types,
            'filter' => $filter
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
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Contract')
                ->createQueryBuilder('c')
                ->join('c.supplier', 's')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter');

        $session = $this->container->get('session');
        $session->set('contracts.filter', $filter);

        $andX = $qb->expr()->andX();

        if (is_array($search) && isset($search['value']) && $search['value']) {
            $andX->add($qb->expr()->like('c.name', ':q'));
            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
        }

        if (isset($filter['type']) && $filter['type']) {
            $andX->add($qb->expr()->eq('c.model', ':ctype'));
            $qb->setParameter('ctype', $filter['type']);
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

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

        $list = $pagination->getItems();
        $total = $pagination->getTotalItemCount();

        $translator = $this->container->get('translator');

        $template = $this->container->get('twig')->loadTemplate('Contracts/_row.html.twig');
        $types = array_combine(array_keys($this->container->getParameter('app.contract.models')), array_map(function($options) {
            return $options['display'];
        }, $this->container->getParameter('app.contract.models')));
        $data = array_map(function($record) use($types, $template) {
            return array(
                $template->renderBlock('checkbox', array('record' => $record)),
                $record->getName(),
                $template->renderBlock('type', array('record' => $record, 'types' => $types)),
                (string) $record->getSupplier(),
                $record->getSignedAt() ? $record->getSignedAt()->format('Y-m-d') : '',
                $record->getStartAt() ? $record->getStartAt()->format('Y-m-d') : '',
                $record->getEndAt() ? $record->getEndAt()->format('Y-m-d') : '',
                $template->renderBlock('actions', array('record' => $record))
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

        $originalTopServices = new ArrayCollection();
        foreach ($record->getTopServices() as $service) {
            $originalTopServices->add($service);
        }

        $originalAttachments = new ArrayCollection();
        foreach ($record->getAttachments() as $attachment) {
            $originalAttachments->add($attachment);
        }

        $originalFacilities = new ArrayCollection();
        $originalRooms = array();
        foreach ($record->getFacilities() as $facility) {
            $originalFacilities[] = $facility;
            $originalRooms[$facility->getId()] = new ArrayCollection();
            foreach ($facility->getRooms() as $room) {
                $originalRooms[$facility->getId()][] = $room;
            }
        }

        $originalCarCategories = new ArrayCollection();
        foreach ($record->getCarRentalCategories() as $category) {
            $originalCarCategories[] = $category;
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
            foreach ($originalCarCategories as $cat) {
                if (false === $record->getCarRentalCategories()->contains($cat)) {
                    $record->getTopServices()->removeElement($cat);
                    $em->remove($cat);
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
     * @Route("/{id}/hotel-prices", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Contract")
     * @Method({"get"})
     * @param Contract $record
     * @return Response
     */
    public function hotelPricesAction(Contract $record)
    {
        $cupos = $this->container->getParameter('app.hotel.cupos');
        $manager = $this->getDoctrine()->getManager();

        $repo = $manager->getRepository('AppBundle:ContractHotelPrice');
        $prices = array();
        foreach ($repo->findBy(array('contract' => $record->getId())) as $price) {
            $prices[$price->getRoom()->getId()][$price->getPlan()][$price->getSeason()->getId()][$price->getCupo()] = $price->getValue();
        }

        return $this->render('Contracts/hotel_prices.html.twig', array(
            'record'    => $record,
            'cupos'     => $cupos,
            'prices'    => $prices
        ));
    }

    /**
     * @Route("/set-hotel-price", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function setHotelPriceAction(Request $request)
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

    /**
     * @Route("/{id}/car-rental-prices", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Contract")
     * @Method({"get", "post"})
     * @param Contract $record
     * @return Response
     */
    public function carRentalPricesAction(Contract $record)
    {
        $manager = $this->getDoctrine()->getManager();

        $repo = $manager->getRepository('AppBundle:ContractCarRentalPrice');
        $prices = array();
        foreach ($repo->findBy(array('contract' => $record->getId())) as $price) {
            $prices[$price->getDayRange()->getId()][$price->getCategory()->getId()] = $price->getValue();
        }

        return $this->render('Contracts/car_rental_prices.html.twig', array(
            'record'    => $record,
            'prices'    => $prices
        ));
    }

    /**
     * @Route("/set-car-rental-price", options={"expose": true})
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function setCarRentalPriceAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $price = $manager->getRepository('AppBundle:ContractCarRentalPrice')->findOneBy(array(
            'contract' => $request->get('contract'),
            'dayRange' => $request->get('dayRange'),
            'category' => $request->get('category')
        ));

        if (!$price && $request->get('value')) {
            $price = new \AppBundle\Entity\ContractCarRentalPrice();
            $price
                    ->setContract($manager->find('AppBundle:Contract', $request->get('contract')))
                    ->setDayRange($manager->find('AppBundle:ContractCarRentalSeassonDayRange', $request->get('dayRange')))
                    ->setCategory($manager->find('AppBundle:ContractCarRentalCategory', $request->get('category')))
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
