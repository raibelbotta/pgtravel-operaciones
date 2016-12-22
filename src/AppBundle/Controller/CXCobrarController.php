<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Reservation;
use AppBundle\Form\Type\PayReservationFormType;

/**
 * Description of CXCobrarController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/cxcobrar")
 */
class CXCobrarController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('CXCobrar/index.html.twig');
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

        $qb = $manager->getRepository('AppBundle:Reservation')
                ->createQueryBuilder('r')
                ->add(
                        'select',
                        '(CASE WHEN r.client IS NULL THEN r.directClientFullName'
                        . ' ELSE c.fullName END) AS clientName',
                        true
                        )
                ->add(
                        'select',
                        '(SELECT MIN(rs1.startAt) FROM AppBundle:ReservationService rs1 '
                        . 'JOIN rs1.reservation rk1 WHERE rk1.id = r.id) AS startAt',
                        true
                        )
                ->add(
                        'select',
                        '(SELECT MAX(rs2.endAt) FROM AppBundle:ReservationService rs2 '
                        . 'JOIN rs2.reservation rk2 WHERE rk2.id = r.id) AS endAt',
                        true
                        )
                ->leftJoin('r.client', 'c')
                ;

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order');
        $filter = $request->get('filter');

        $andX = $qb->expr()->andX($qb->expr()->eq('r.state',
                $qb->expr()->literal(Reservation::STATE_RESERVATION)));

        if (isset($filter['state']) && $filter['state']) {
            $andX->add($filter['state'] == 'yes' ? 
                    $qb->expr()->isNotNull('r.paidAt') :
                    $qb->expr()->isNull('r.paidAt'));
        }

        if (is_array($search) && isset($search['value']) && ($value = $search['value'])) {
            $andX->add($qb->expr()->orX(
                    $qb->expr()->orX(
                            $qb->expr()->andX(
                                    $qb->expr()->isNull('r.client'),
                                    $qb->expr()->like('r.directClientFullName',
                                            $qb->expr()->literal('%' . $value . '%'))
                                    ),
                            $qb->expr()->andX(
                                    $qb->expr()->isNotNull('r.client'),
                                    $qb->expr()->like('c.fullName',
                                            $qb->expr()->literal('%' . $value . '%'))
                                    )
                            ),
                    $qb->expr()->like('r.name', $qb->expr()->literal('%' . $value . '%')),
                    $qb->expr()->like('r.payNotes', $qb->expr()->literal('%' . $value . '%'))
                    ));
        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) {
                if ('client' === $name) {
                    return 'clientName';
                } elseif ($name == 'name') {
                    return 'r.name';
                } elseif ('startAt' === $name) {
                    return 'startAt';
                } elseif ('endAt' === $name) {
                    return 'endAt';
                } elseif ('price' === $name) {
                    return 'r.clientCharge';
                } elseif ('date' == $name) {
                    return 'r.paidAt';
                } elseif ('notes' === $name) {
                    return 'r.payNotes';
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
                null !== $record[0]->getClient() ? (string) $record[0]->getClient() : $record[0]->getDirectClientFullName(),
                $record[0]->getName(),
                date_create($record['startAt'])->format('Y/m/d H:i'),
                date_create($record['endAt'])->format('d/m/Y H:i'),
                sprintf('<div class="text-right">%0.2f</div>', $record[0]->getClientCharge()),
                null !== $record[0]->getPaidAt() ? $record[0]->getPaidAt()->format('d/m/Y') : '',
                $record[0]->getPayNotes(),
                $twig->render('CXCobrar/_actions.html.twig', array('record' => $record[0]))
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
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @param Request $request
     * @return JsonResponse
     */
    public function changeStateAction(Reservation $record, Request $request)
    {
        $form = $this->createForm(PayReservationFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $record->setPaidAt(new \DateTime('now'));

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return new JsonResponse(array(
                'result' => 'success'
            ));
        }

        return $this->render('CXCobrar/form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/view-state", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     * @return Response
     */
    public function viewStateAction(Reservation $record)
    {
        return $this->render('CXCobrar/view.html.twig', array(
            'record' => $record
        ));
    }

    /**
     * @Route("/{id}/download", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\ReservationPayAttachment")
     * @param \AppBundle\Entity\ReservationPayAttachment $record
     * @return Response
     */
    public function downloadAttachmentAction(\AppBundle\Entity\ReservationPayAttachment $record)
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
     * @ParamConverter("record", class="AppBundle\Entity\Reservation")
     */
    public function cancelPayAction(Reservation $record)
    {
        $manager = $this->getDoctrine()->getManager();
        $record
                ->setPaidAt(null)
                ->setPayNotes(null);
        foreach ($record->getPayAttachments() as $file) {
            $manager->remove($file);
        }

        $manager->flush();

        return $this->redirect($this->generateUrl('app_cxcobrar_index'));
    }
}
