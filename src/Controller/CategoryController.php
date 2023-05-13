<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CategoryController extends AbstractController
{
	#[Route('/categories', name: 'app_categories', methods: ['GET'])]
	public function index(EntityManagerInterface $em): Response
	{
		$categories = $em->getRepository(Category::class)->findLastThreeCategories();

		return new JsonResponse($categories);
	}

	#[Route('/category/{id}', name: 'one_category', methods: ['GET'])]
	public function get($id, EntityManagerInterface $em): Response
	{
		$category = $em->getRepository(Category::class)->findOneById($id);

		if ($category === null) {
			return new JsonResponse('Catégorie introuvable', 404);
		}

		return new JsonResponse($category);
	}

	#[Route('/category', name: 'create_category', methods: ['POST'])]
	public function create(EntityManagerInterface $em, Request $r): Response
	{
		$headers = $r->headers->all();

		if (isset($headers['token']) && !empty($headers['token'])) {
			$jwt = current($headers['token']);
			$key = $this->getParameter('jwt_secret');
			try {
				$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
			} // Si la signature n'est pas verifiee ou que la date d'expiration est passee, il entrera dans le catch
			catch (\Exception $e) {
				return new JsonResponse($e->getMessage(), 403);
			}

			// On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
			if ($decoded->roles != null && in_array('ROLE_ADMIN', $decoded->roles)) {
				$category = new Category();
				$category->setTitle($r->get('title'));

				$em->persist($category);
				$em->flush();

				return new JsonResponse('Catégorie enregistré', 201);
			}
		}

		return new JsonResponse('Access denied', 403);
	}


	#[Route('/category/{id}', name: 'update_category', methods: ['PATCH'])]
	public function update(Category $category = null, EntityManagerInterface $em, Request $r): Response
	{

		$headers = $r->headers->all();

		if (isset($headers['token']) && !empty($headers['token'])) {
			$jwt = current($headers['token']);
			$key = $this->getParameter('jwt_secret');
			try {
				$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
			} // Si la signature n'est pas verifiee ou que la date d'expiration est passee, il entrera dans le catch
			catch (\Exception $e) {
				return new JsonResponse($e->getMessage(), 403);
			}

			//On regarde si la clé 'roles' existe et si l'utilisateur possede le bon role
			if ($decoded->roles != null && in_array('ROLE_ADMIN', $decoded->roles)) {

				if ($category === null) {
					return new JsonResponse('Categorie introuvable', 404);
				}

				$category->setTitle($r->get('title'));

				$em->persist($category);
				$em->flush();

				return new JsonResponse('success', 200);
			}
		}
		return new JsonResponse('Access denied', 403);
	}


	#[Route('/category/{id}', name: 'delete_category', methods: ['DELETE'])]
	public function delete(Category $category, EntityManagerInterface $em, Request $r): Response
	{

		$headers = $r->headers->all();

		if (isset($headers['token']) && !empty($headers['token'])) {
			$jwt = current($headers['token']);
			$key = $this->getParameter('jwt_secret');
			try {
				$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
			} // Si la signature n'est pas verifiee ou que la date d'expiration est passee, il entrera dans le catch
			catch (\Exception $e) {
				return new JsonResponse($e->getMessage(), 403);
			}

			//On regarde si la clé 'roles' existe et si l'utilisateur possede le bon role
			if ($decoded->roles != null && in_array('ROLE_ADMIN', $decoded->roles)) {
				if ($category === null) {
					return new JsonResponse('Catégorie introuvable', 404);
				}

				$em->remove($category);
				$em->flush();

				return new JsonResponse('Catégorie supprimée', 200);
			}
			return new JsonResponse('Access denied', 403);
		}
	}
}
