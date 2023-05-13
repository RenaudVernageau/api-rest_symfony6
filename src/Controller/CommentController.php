<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
	#[Route('/comments', name: 'app_comments', methods: ['GET'])]
	public function index(EntityManagerInterface $em): Response
	{
		$comments = $em->getRepository(Comment::class)->findAll();

		return new JsonResponse($comments);
	}

	#[Route('/comment/{article_id}', name: 'add_comment', methods: ['POST'])]
	public function addComment($article_id, EntityManagerInterface $em, Request $request): Response
	{
		//récupérer l'article à commenter
		$article = $em->getRepository(Article::class)->findOneById($article_id);

		if (!$article) {
			return new JsonResponse('Article introuvable', 404);
		}

		//créer un nouveau commentaire
		$comment = new Comment();
		$comment->setArticle($article);
		$comment->setAuthor($this->getUser());
		$comment->setPublishAt(new \DateTimeImmutable());
		$comment->setStatus(false);

		//récupérer le contenu du commentaire
		$comment->setComment($request->get('comment'));

		//ajouter le commentaire
		$em->persist($comment);
		$em->flush();

		return new JsonResponse('success', 201);
	}
}
