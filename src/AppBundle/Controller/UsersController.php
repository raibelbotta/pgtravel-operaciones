<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\UserFormType;
use AppBundle\Entity\User;

/**
 * Description of UsersController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/users")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class UsersController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT u FROM AppBundle:User u');

        return $this->render('Users/index.html.twig', array(
            'query' => $query
        ));
    }

    /**
     * @Route("/{id}/view", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @param User $record
     * @return Response
     */
    public function viewAction(User $record)
    {
        return $this->render('Users/view.html.twig', array(
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
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved sucessfuly!'));
            return $this->redirect($this->generateUrl('app_users_index'));
        }

        return $this->render('Users/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"})
     * @Method({"get", "post"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @param User $record
     * @param Request $request
     * @return Response
     */
    public function editAction(User $record, Request $request)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $form = $this->createForm(UserFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($record);

            $translator = $this->container->get('translator');
            $this->addFlash('notice', $translator->trans('Record saved sucessfuly!'));
            return $this->redirect($this->generateUrl('app_users_index'));
        }

        return $this->render('Users/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"})
     * @Method({"get"})
     * @ParamConverter("record", class="AppBundle\Entity\User")
     * @param User $record
     * @return Response
     */
    public function deleteAction(User $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        $translator = $this->container->get('translator');
        $this->addFlash('notice', $translator->trans('Record removed sucessfuly!'));
        return $this->redirect($this->generateUrl('app_users_index'));
    }
}
