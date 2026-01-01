<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Form\ForumPostForm;
use App\Repository\ForumPostRepository;
use App\Service\ForumService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum', methods: [Request::METHOD_GET])]
    public function index(ForumService $forumService, ForumPostRepository $forumPostRepository, Request $request): Response
    {
        $limit = 10;
        $currentPage = max(1, (int) $request->query->get('page', 1));
        $totalPosts = $forumPostRepository->countRootPosts();
        $totalPages = max(1, (int) ceil($totalPosts / $limit));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $limit;

        $newPost = new ForumPost();
        
        $postForm = $this->createForm(
            type: ForumPostForm::class,
            data:  $newPost,
            options: [
                'action' => $this->generateUrl('app_forum_add_post', ['postId' => 0]),
                'method' => Request::METHOD_POST,
                'parent' => false,
            ]
        );
    
        return $this->render(
            view: 'forum/index.html.twig',
            parameters: [
                'posts' => $forumService->generatePostArray(
                    limit: $limit,
                    offset: $offset
                ),
                'form' => $postForm->createView(),
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalPosts' => $totalPosts,
            ]
        );
    }

    #[Route('/forum/post/{id}', name:'app_forum_details', methods: [Request::METHOD_GET])]
    public function getPostById(int $id, ForumPostRepository $forumPostRepository, ForumService $forumService, Request $request): Response
    {
        $post = $forumPostRepository->findOneById($id);
        if ($post == null) {
            return $this->render(
                view: 'forum/error.html.twig',
            );
        }

        $answersTree = $forumService->getAnswersTree($id);
        $answersCount = $forumService->countAnswersTree($answersTree);

        $answer = new ForumPost();
        $answerForm = $this->createForm(
            type: ForumPostForm::class,
            data: $answer,
            options: [
                'action' => $this->generateUrl('app_forum_add_post', ['postId' => $id]),
                'method' => Request::METHOD_POST,
                'parent' => true,
            ]
        );

        return $this->render(
            view: 'forum/single.html.twig',
            parameters: [
                'post' => $post,
                'answers' => $answersTree,
                'answers_count' => $answersCount,
                'answer_form' => $answerForm->createView(),
            ]
            );
    }

    #[Route(
        path: '/forum/post/{postId}',
        name: 'app_forum_add_post',
        requirements: ['postId' => '\d+'],
        defaults: ['postId' => 0],
        methods:[Request::METHOD_POST]
    )]
    public function postNewPost(?int $postId, Request $request, EntityManagerInterface $em): Response
    {   
        $sanitizedPostId = $postId === 0 ? null : $postId;

        $post = new ForumPost();
        $form = $this->createForm(ForumPostForm::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post->setFlag(false);
            if ($sanitizedPostId != null) {
                $post->setParentId($sanitizedPostId);
            }
                $em->persist($post);
                $em->flush();
        }
        if ($sanitizedPostId !== null) {
            return $this->redirectToRoute('app_forum_details', ['id' => $sanitizedPostId]);
        }
        return $this->redirectToRoute(route: 'app_forum');
    }

    #[Route(path: '/answer/{postId}', name: 'app_forum_answer', methods:[Request::METHOD_GET])]
    public function getAnswerForm(int $postId, ForumService $forumService, ForumPostRepository $forumPostRepository, Request $request): Response
    {
        $limit = 10;
        $currentPage = max(1, (int) $request->query->get('page', 1));
        $totalPosts = $forumPostRepository->countRootPosts();
        $totalPages = max(1, (int) ceil($totalPosts / $limit));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $limit;

        $answer = new ForumPost();

            $postForm = $this->createForm(
            type: ForumPostForm::class,
            data:  $answer,
            options: [
                'action' => $this->generateUrl('app_forum_add_post', ['postId' => $postId]),
                'method' => Request::METHOD_POST,
                'parent' => true,
            ]
        );
                return $this->render(
            view: 'forum/index.html.twig',
            parameters: [
                'posts' => $forumService->generatePostArray(
                    limit: $limit,
                    offset: $offset
                ),
                'form' => $postForm->createView(),
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalPosts' => $totalPosts,
            ]
        );
    }
}
