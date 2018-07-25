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
use AppBundle\Form\Type\ContractFilterFormType;

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
        $session = $this->container->get('session');
        $form = $this->createForm(ContractFilterFormType::class, $session->get('contracts.filter', array()));

        return $this->render('Contracts/index.html.twig', array(
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
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Contract')
                ->createQueryBuilder('c')
                ;

        $columns = $request->get('columns');
        $orders = $request->get('order');

        $form = $this->createForm(ContractFilterFormType::class);
        $form->submit($request->request->get($form->getName()));
        $this->container->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
        $this->container->get('session')->set('contracts.filter', $form->getData());

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
        $total = $pagination->getTotalItemCount();

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
        }, $pagination->getItems());

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

        $originalCollections = array();
        $this->createCollections($record, $originalCollections, array(
            'topServices',
            'privateHouseSeassons',
            'privateHouseFacilities'
        ));

        $originalAttachments = new ArrayCollection();
        foreach ($record->getAttachments() as $attachment) {
            $originalAttachments->add($attachment);
        }

        $originalFacilities = new ArrayCollection();
        $originalRooms = array();
        $originalSeasons = array();
        foreach ($record->getFacilities() as $facility) {
            $originalFacilities[] = $facility;

            $originalRooms[$facility->getId()] = new ArrayCollection();
            foreach ($facility->getRooms() as $room) {
                $originalRooms[$facility->getId()][] = $room;
            }

            $originalSeasons[$facility->getId()] = new ArrayCollection();
            foreach ($facility->getSeasons() as $season) {
                $originalSeasons[$facility->getId()][] = $season;
            }
        }

        $originalCarCategories = new ArrayCollection();
        foreach ($record->getCarRentalCategories() as $category) {
            $originalCarCategories[] = $category;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $this->updateCollections($record, $originalCollections, array(
                'topServices',
                'privateHouseSeassons',
                'privateHouseFacilities'
            ));

            foreach ($originalAttachments as $attachment) {
                if (false === $record->getAttachments()->contains($attachment)) {
                    $record->getAttachments()->removeElement($attachment);
                    $em->remove($attachment);
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

                    foreach ($originalSeasons[$facility->getId()] as $season) {
                        if (false === $facility->getSeasons()->contains($season)) {
                            $facility->getSeasons()->removeElement($season);
                            $em->remove($season);
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

    private function createCollections($record, array &$container, $names)
    {
        foreach ($names as $name) {
            if (!isset($container[$name])) {
                $container[$name] = new ArrayCollection();
            }

            $getter = sprintf('get%s', ucfirst($name));

            foreach (call_user_func(array($record, $getter)) as $element) {
                $container[$name]->add($element);
            }
        }
    }

    private function updateCollections($record, array &$container, $names)
    {
        $manager = $this->getDoctrine()->getManager();

        foreach ($names as $name) {
            /** @var ArrayCollection $collection */
            $collection = $container[$name];
            $getter = sprintf('get%s', ucfirst($name));

            foreach ($collection as $item) {
                if (false === call_user_func(array($record, $getter))->contains($item)) {
                    $manager->remove($item);
                }
            }
        }
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

    /**
     * @Route("/{id}/private-house-prices", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Contract")
     * @Method({"get"})
     * @param Contract $record
     * @return Response
     */
    public function privateHousePricesAction(Contract $record)
    {
        $manager = $this->getDoctrine()->getManager();

        $query = $manager->createQuery('SELECT p FROM AppBundle:ContractPrivateHousePrice AS p JOIN p.facility AS f JOIN f.contract AS c WHERE c.id = :contract')
                ->setParameter('contract', $record->getId());
        $prices = array();
        foreach ($query->getResult() as $price) {
            $prices[$price->getFacility()->getId()][$price->getSeasson()->getId()] = $price->getValue();
        }

        return $this->render('Contracts/private_house_prices.html.twig', array(
            'contract'  => $record,
            'prices'    => $prices
        ));
    }

    /**
     * @Route("/set-private-house-price", options={"expose": true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function setPrivateHousePriceAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $record = $manager->getRepository('AppBundle:ContractPrivateHousePrice')->savePriceBySeasonAndFacility(
            $manager->find('AppBundle:ContractPrivateHouseSeason', $request->get('season')),
            $manager->find('AppBundle:ContractPrivateHouseFacility', $request->get('facility')),
            $request->get('value')
        );

        $manager->flush();

        return new JsonResponse(array(
            'value'     => $request->get('value') ? sprintf('%0.2f', $request->get('value')) : ''
        ));
    }

    /**
     * @Route(
     *     "set-private-house-note/{seasonId}/{facilityId}",
     *     requirements={
     *          "seasonId": "\d+", "facilityId": "\d+"
     *     }
     * )
     * @Method({"GET", "POST"})
     */
    public function setPrivateHouseNotesAction($seasonId, $facilityId, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $season = $manager->find('AppBundle:ContractPrivateHouseSeason', $seasonId);
        $facility = $manager->find('AppBundle:ContractPrivateHouseFacility', $facilityId);
        $record = $manager->getRepository('AppBundle:ContractPrivateHousePrice')
            ->findOneBySeasonAndFacility($season, $facility);

        $form = $this->createFormBuilder($record)
            ->add('note')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->getRepository('AppBundle:ContractPrivateHousePrice')
                ->saveNotesBySeasonAndFacility(
                    $season,
                    $facility,
                    $form->get('note')->getData()
                );

            $manager->flush();

            return new Response('<script type="text/javascript">$(\'.modal\').modal(\'hide\')</script>');
        }

        return $this->render('Contracts/set_contract_private_house_price_note.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
