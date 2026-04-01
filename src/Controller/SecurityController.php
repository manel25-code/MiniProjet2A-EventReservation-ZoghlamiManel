<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never { throw new \LogicException('Intercepté par Symfony.'); }

    #[Route('/register', name: 'user_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setUsername($request->request->get('username'));
            $user->setPassword($hasher->hashPassword($user, $request->request->get('password')));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Compte créé ! Connectez-vous maintenant.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/register.html.twig');
    }
}