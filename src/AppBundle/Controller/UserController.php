<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of UserController
 *
 * @author Riabel Botta <raibelbotta@gmail.com>
 */
class UserController extends Controller
{
    /**
     * @Route("/profile/change-password")
     * @Method({"get", "post"})
     * @param Request
     * @return Response
     */
    public function changePasswordAction(Request $request)
    {
        $form = $this->createFormBuilder($this->getUser())
                ->add('current_password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, array(
                    'mapped' => false,
                    'label' => 'Current password',
                    'constraints' => new \Symfony\Component\Security\Core\Validator\Constraints\UserPassword()
                ))
                ->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, array(
                    'type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
                    'first_options' => array(
                        'label' => 'New password'
                    ),
                    'second_options' => array(
                        'label' => 'Repeat new password'
                    )
                ))->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $um = $this->container->get('fos_user.user_manager');
                $um->updateUser($this->getUser());

                return new JsonResponse(array(
                    'result' => 'success'
                ));
            } else {
                return new JsonResponse(array(
                    'result' => 'error'
                ));
            }
        }

        return $this->render('User/change_password.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/profile/check-current-password")
     * @Method({"post"})
     * @param Request $request
     */
    public function checkPasswordAction(Request $request)
    {
        $validator = $this->container->get('validator');
        $constraint = new \Symfony\Component\Security\Core\Validator\Constraints\UserPassword();
        $errorList = $validator->validate($request->get('password'), $constraint);

        return new Response(0 === count($errorList) ? 'true' : 'false', 200, array(
            'Content-Type' => 'text/plain'
        ));
    }
}
