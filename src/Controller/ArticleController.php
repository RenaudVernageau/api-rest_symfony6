<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ArticleController extends AbstractController
{
	#[Route('/article', name: 'create_article', methods: ['POST'])]
	public function create(EntityManagerInterface $em, Request $r): Response
	{
		$headers = $r->headers->all();

		if (isset($headers['token']) && !empty($headers['token'])) {
			$jwt = current($headers['token']);
			$key = $this->getParameter('jwt_secret');
			try {
				$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
				$userName = $decoded->user_id;
			} // Si la signature n'est pas verifiee ou que la date d'expiration est passee, il entrera dans le catch
			catch (\Exception $e) {
				return new JsonResponse($e->getMessage(), 403);
			}

			// On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
			if ($decoded->roles != null && in_array('ROLE_ADMIN', $decoded->roles)) {
				$article = new Article();
				$article->setTitle($r->get('title'));
				$article->setContent($r->get('content'));
				$article->setCreated(new \DateTime());
				$article->setStatus(false);
				$article->setPublishAt(new \DateTimeImmutable());
				$article->setAuthor($userName);

				//Essaie de récupérer en base la catégory qui correspond au paramètre reçu
				$category = $em->getRepository(Category::class)->findOneBy(['id' => $r->get('category')]);

				//Si la catégorie n'existe pas	
				if ($category == null) {
					return new JsonResponse('Catégorie introuvable', 404);
				}

				//Si la catégorie existe, on l'ajoute au produit
				$article->setCategory($category);

				$em->persist($article);
				$em->flush();

				return new JsonResponse('Article enregistré', 201);
			}
			return new JsonResponse('Access denied', 403);
		}
	}

	#[Route('/article/{id}', name: 'update_article', methods: ['PATCH'])]
	public function update(EntityManagerInterface $em, Request $r, $id): Response
	{
		$headers = $r->headers->all();

		if (isset($headers['token']) && !empty($headers['token'])) {
			$jwt = current($headers['token']);
			$key = $this->getParameter('jwt_secret');
			try {
				$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
				$userName = $decoded->user_id;
			} // Si la signature n'est pas verifiee ou que la date d'expiration est passee, il entrera dans le catch
			catch (\Exception $e) {
				return new JsonResponse($e->getMessage(), 403);
			}

			// On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
			if ($decoded->roles != null && in_array('ROLE_ADMIN', $decoded->roles)) {
				$article = $em->getRepository(Article::class)->findOneBy(['id' => $id]);

				//Si l'article n'existe pas	
				if ($article == null) {
					return new JsonResponse('Article introuvable', 404);
				}

				//Si l'article existe, on l'ajoute au produit
				$article->setTitle($r->get('title'));
				$article->setContent($r->get('content'));
				$article->setCreated(new \DateTime());
				$article->setStatus(false);
				$article->setPublishAt(new \DateTimeImmutable());
				$article->setAuthor($userName);

				//Essaie de récupérer en base la catégory qui correspond au paramètre reçu
				$category = $em->getRepository(Category::class)->findOneBy(['id' => $r->get('category')]);

				//Si la catégorie n'existe pas	
				if ($category == null) {
					return new JsonResponse('Catégorie introuvable', 404);
				}

				//Si la catégorie existe, on l'ajoute au produit
				$article->setCategory($category);

				$em->persist($article);
				$em->flush();

				return new JsonResponse('Article modifié', 200);
			}
			return new JsonResponse('Access denied');
		}
	}

	#[Route('/article/{id}', name: 'delete_article', methods: ['DELETE'])]
	public function delete(EntityManagerInterface $em, Request $r, $id): Response
	{
		$headers = $r->headers->all();

		if (isset($headers['token']) && !empty($headers['token'])) {
			$jwt = current($headers['token']);
			$key = $this->getParameter('jwt_secret');
			try {
				$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
				$userName = $decoded->user_id;
			} // Si la signature n'est pas verifiee ou que la date d'expiration est passee, il entrera dans le catch
			catch (\Exception $e) {
				return new JsonResponse($e->getMessage(), 403);
			}

			// On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
			if ($decoded->roles != null && in_array('ROLE_ADMIN', $decoded->roles)) {
				$article = $em->getRepository(Article::class)->findOneBy(['id' => $id]);

				if ($article == null) {
					return new JsonResponse('Article introuvable', 404);
				}

				$em->remove($article);
				$em->flush();

				return new JsonResponse('Article supprimé', 200);
			}
			return new JsonResponse('Access denied', 403);
		}
	}
}
