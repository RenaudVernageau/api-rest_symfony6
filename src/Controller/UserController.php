<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends AbstractController
{
	#[Route('/login', name: 'login', methods: ['POST'])]
	public function index(Request $r, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
	{
		//On tente de récupérer un utilisateur grâce à son email
		$user = $em->getRepository(User::class)->findOneBy(['email' => $r->get('email')]);

		//Si aucun utilisateur n'est trouvé, on retourne une erreur
		if ($user == null) {
			return new JsonResponse('Utilisateur introuvable', 404);
		}
		// //Récupérer l'id de l'utilisateur à partir du système d'authentification
		// $userId = $user->getId();

		//Si le mot de passe ne correspond pas
		if ($r->get('pwd') == null || !$userPasswordHasher->isPasswordValid($user, $r->get('pwd'))) {
			return new JsonResponse('Mot de passe incorrect', 400);
		}
		$key = $this->getParameter('jwt_secret');
		$payload = [
			'iat' => time(),  //ossued at (date de creation)
			'exp' => time() + 3600,  //Expiration(date de creation + x secondes)
			// 'user_id' => $userId,
			'roles' => $user->getRoles(),
			'username' => $user->getEmail(),
		];

		$jwt = JWT::encode($payload, $key, 'HS256');

		return new JsonResponse($jwt, 200);
	}
}
