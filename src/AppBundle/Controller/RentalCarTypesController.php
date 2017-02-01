<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtrabundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtrabundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtrabundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\RentCarType;
use AppBundle\Form\Type\RentCarTypeFormType;

/**
 * Description of RentalCarTypeController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/rent-car-types")
 */
class RentalCarTypesController extends Controller
{
    /**
     * @Route("/")
     * @Method("get")
     * @return Response
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $records = $manager->createQuery('SELECT c FROM AppBundle:RentCarType c ORDER BY c.name');

        return $this->render('RentCarTypes/index.html.twig', array(
            'records' => $records
        ));
    }

    /**
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\RentCarType")
     * @param RentCarType $record
     * @return Response
     */
    public function viewAction(RentCarType $record)
    {
        return $this->render('RentCarTypes/view.html.twig', array(
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
        $record = new RentCarType();
        $form = $this->createForm(RentCarTypeFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($record);

            $manager->flush();

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved sucessfuly!'));

            return $this->redirect($this->generateUrl('app_rentalcartypes_index'));
        }

        return $this->render('RentCarTypes/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit")
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\RentCarType")
     * @param Request $request
     * @return Response
     */
    public function editAction(RentCarType $record, Request $request)
    {
        $form = $this->createForm(RentCarTypeFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($record);

            $manager->flush();

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved sucessfuly!'));

            return $this->redirect($this->generateUrl('app_rentalcartypes_index'));
        }

        return $this->render('RentCarTypes/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/delete")
     * @ParamConverter("record", class="AppBundle\Entity\RentCarType")
     * @param RentCarTpe $record
     * @return Response
     */
    public function deleteAction(RentCarType $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($record);
        $manager->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }
}
