<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\Mailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @throws \Exception
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
            $token = md5(random_bytes(10));
            $user->setConfirmationToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $mailer->sendConfirmationMail($user);
            $this->addFlash('success', 'Inscription réussite!');

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
        return $this->render('registration/check.html.twig');
    }

    /**
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/registration/confirmed/{token}", name="registration_confirm", methods="GET")
     */
    public function confirmAction($token)
    {
        $userManager = $this->getDoctrine()->getManager()->getRepository('App:User');
        $user = $userManager->findByConfirmationToken($token);
        $user = $user[0];

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken('');
        $user->setIsActive(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->render('registration/confirmed.html.twig', ['user' => $user]);
    }
}
