<?php
/**
 * Created by PhpStorm.
 * User: aragorn
 * Date: 11/07/18
 * Time: 11:53
 */

namespace App\Service;


use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    protected $templating;
    protected $mailer;
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * Mailer constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $templating
     * @param UrlGeneratorInterface $router
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating, UrlGeneratorInterface $router)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->router = $router;
    }

    /**
     * @param User $user
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendConfirmationMail(User $user)
    {
        $message = new \Swift_Message();
        $template = 'mail/confirmation.html.twig';
        $url = $this->router->generate('registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $body = $this->templating->render($template, [
            'user' => $user,
            'confirmationUrl' => $url,
        ]);
        $message
            ->setFrom('no-reply@lemauvaiscoincoin.com')
            ->setTo($user->getEmail())
            ->setSubject('Confirmation d\'inscription')
            ->setBody($body, 'text/html');
        $this->mailer->send($message);
    }
}