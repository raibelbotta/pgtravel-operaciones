<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Entity\Reservation;
use AppBundle\Entity\ReservationService;
use AppBundle\Form\Type\PayReservationServiceFormType;

/**
 * Description of CXPagarController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/cxpagar")
 */
class CXPagarController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $session = $this->container->get('session');

        if (null === $filter = $session->get('cxpagar.filter')) {
            $filter = array(
                'state' => 'no'
            );
        }

        return $this->render('CXPagar/index.html.twig', array(
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
                ->add(
                        'select',
                        '(CASE WHEN r.client IS NOT NULL THEN c.fullName ELSE r.directClientFullName END) AS clientName',
                        true
                        )
                ->join('rs.reservation', 'r')
                ->join('rs.supplier', 's')
                ->leftJoin('r.client', 'c')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter');

        $request->getSession()->set('cxpagar.filter', $filter);

        $andX = $qb->expr()->andX(
                $qb->expr()->eq('r.state', $qb->expr()->literal(Reservation::STATE_RESERVATION)),
                $qb->expr()->eq('r.isCancelled', $qb->expr()->literal(false))
                );

        if (isset($filter['state']) && $filter['state']) {
            $andX->add($filter['state'] == 'yes' ? $qb->expr()->isNotNull('rs.paidAt') : $qb->expr()->isNull('rs.paidAt'));
        }

        if (is_array($search) && isset($search['value']) && $search['value']) {

        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) {
                if ('client' === $name) {
                    return 'clientName';
                } elseif ($name == 'service') {
                    return 'rs.name';
                } elseif ($name === 'startAt') {
                    return 'rs.startAt';
                } elseif ($name === 'endAt') {
                    return 'rs.endAt';
                } elseif ($name === 'supplier') {
                    return 's.name';
                } elseif ('date' == $name) {
                    return 'rs.paidAt';
                } elseif ('notes' === $name) {
                    return 'rs.payNotes';
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
                $record['clientName'],
                $record[0]->getReservation()->getName(),
                $record[0]->getName(),
                $record[0]->getStartAt()->format('d/m/Y'),
                $record[0]->getEndAt()->format('d/m/Y'),
                $record[0]->getSupplier()->getName(),
                null !== $record[0]->getPaidAt() ? $record[0]->getPaidAt()->format('d/m/Y') : '',
                $record[0]->getPayNotes(),
                $twig->render('CXPagar/_actions.html.twig', array('record' => $record[0]))
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
        $form = $this->createForm(PayReservationServiceFormType::class,
                $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $record->setPaidAt(new \DateTime());

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return new JsonResponse(array('result' => 'success'));
        }

        return $this->render('CXPagar/form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/view-state", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\ReservationService")
     * @param Request $request
     * @return JsonResponse
     */
    public function viewStateAction(ReservationService $record, Request $request)
    {
        return $this->render('CXPagar/view.html.twig', array(
            'record' => $record
        ));
    }

    /**
     * @Route("/{id}/download", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\ReservationServicePayAttachment")
     * @param \AppBundle\Entity\ReservationServicePayAttachment $record
     * @return Response
     */
    public function downloadAttachmentAction(\AppBundle\Entity\ReservationServicePayAttachment $record)
    {
        $filename = $this->container->getParameter('kernel.root_dir') .
                '/../web/uploads/pay_attachments/' . $record->getFilename();

        return new BinaryFileResponse($filename, 200, array(
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'application/octect-stream',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $record->getOriginalFilename()),
            'Expires' => 0,
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'Public',
            'Content-Length' => filesize($filename)
        ));
    }

    /**
     * @Route("/{id}/cancel-pay", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\ReservationService")
     */
    public function cancelPayAction(ReservationService $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $record
                ->setPaidAt(null)
                ->setPayNotes(null);
        foreach ($record->getPayAttachments() as $file) {
            $manager->remove($file);
        }

        $manager->flush();

        return $this->redirect($this->generateUrl('app_cxpagar_index'));
    }
}
