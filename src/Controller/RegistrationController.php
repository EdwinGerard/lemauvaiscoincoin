<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Mailer $mailer
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/registration", name="registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setIsActive(false);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $mailer->sendConfirmationMail($user);

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('registration_check');
        }

        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/registration/check", name="registration_check")
     */
    public function registrationSuccess()
    {
        return $this->render('registration/checkMail.html.twig');
    }

    /**
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route(name="registration_confirm")
     */
    public function confirmAction($token)
    {
        $userManager = $this->getDoctrine()->getManager()->getRepository('User');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }
        $user->setConfirmationToken(null);
        $user->setIsActive(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->render('registration/confirmed.html.twig', array('user' => $user));
    }
}
