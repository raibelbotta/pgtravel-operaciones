<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('Default/index.html.twig');
    }

    /**
     * @Route("/get-calendar-events")
     * @Method({"get"})
     * @param Request $request
     * @return JsonParam
     */
    public function getEventsAction(Request $request)
    {
        $from = \DateTime::createFromFormat('U', $request->get('start'));
        $to = \DateTime::createFromFormat('U', $request->get('end'));

        $manager = $this->getDoctrine()->getManager();
        $qb = $manager->getRepository('AppBundle:Reservation')
                ->createQueryBuilder('e')
                ->add('select', '(SELECT MIN(rs1.startAt) FROM AppBundle:ReservationService rs1'
                        . ' JOIN rs1.reservation r1 WHERE r1.id = e.id) AS startAt', true)
                ->add('select', '(SELECT MAX(rs2.endAt) FROM AppBundle:ReservationService rs2'
                        . ' JOIN rs2.reservation r2 WHERE r2.id = e.id) AS endAt', true)
                ;
        $andX = $qb->expr()->andX(
                $qb->expr()->eq('e.state', $qb->expr()->literal(\AppBundle\Entity\Reservation::STATE_RESERVATION)),
                $qb->expr()->eq('e.isCancelled', $qb->expr()->literal(false))
                );

        $having = $qb->expr()->orX(
                $qb->expr()->andX(
                        $qb->expr()->lt('startAt', ':from'),
                        $qb->expr()->gt('endAt', ':to')
                        ),
                $qb->expr()->andX(
                        $qb->expr()->gte('startAt', ':from'),
                        $qb->expr()->lte('startAt', ':to')
                        ),
                $qb->expr()->andX(
                        $qb->expr()->gte('endAt', ':from'),
                        $qb->expr()->lte('endAt', ':to')
                        )
                );

        $query = $qb
                ->where($andX)
                ->having($having)
                ->getQuery()
                ->setParameters(array(
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d 23:59:59')
                ));

        $backColors = array('#ff7d71', '#7dff71', '#7d71ff', 'red', 'blue', 'olive');

        return new JsonResponse(array(
            'events' => array_map(function(array $record) use ($backColors) {

                return array(
                    'title' => $record[0]->getName(),
                    'start' => date_create($record['startAt'])->format('Y-m-d'),
                    'end' => date_create($record['endAt'])->format('Y-m-d'),
                    'backgroundColor' => $backColors[rand(0, count($backColors) - 1)]
                );
            }, $query->getResult())
        ));
    }
}
