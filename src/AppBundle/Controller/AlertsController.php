<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of AlertsController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class AlertsController extends Controller
{
    public function homeSectionAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $thenDaysAhead = new \DateTime('+10 days');
        $query = $manager->createQuery('SELECT r, (SELECT MIN(s.startAt) FROM AppBundle:ReservationService s JOIN s.reservation sr WHERE sr.id = r.id) AS startAt FROM AppBundle:Reservation r WHERE r.cone = :false HAVING startAt <= :thenDays ORDER BY startAt')
                ->setParameters(array(
                    'false' => false,
                    'thenDays' => $thenDaysAhead->format('Y-m-d')
                ))
                ;
        
        return $this->render('Alerts/_home_section.html.twig', array(
            'records' => $query->getResult()
        ));
    }
}
