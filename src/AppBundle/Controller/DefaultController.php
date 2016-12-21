<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $from = new \DateTime('first day of this month');
        $to = new \DateTime('last day of this month');

        $manager = $this->getDoctrine()->getManager();
        $query = $manager->createQuery('SELECT e FROM AppBundle:Reservation e WHERE e.state = :state AND e.isCancelled = :false')
                ->setParameters(array(
                    'state' => \AppBundle\Entity\Reservation::STATE_RESERVATION,
                    'false' => false
                ))
                ;
        
        return $this->render('Default/index.html.twig', array(
            'events' => array_map(function(\AppBundle\Entity\Reservation $record) {
                return array(
                    'title' => $record->getName(),
                    'start' => $record->getStartAt()->format('Y-m-d'),
                    'end' => $record->getEndAt()->format('Y-m-d')
                );
            }, $query->getResult())
        ));
    }
}
