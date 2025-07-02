<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Form\ForumPostForm;
use App\Repository\ForumPostRepository;
use App\Service\ForumService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum', methods: Request::METHOD_GET)]
    public function index(ForumPostRepository $forumPostRepository, ForumService $forumService): Response
    {
        $posts = $forumPostRepository->findAll();
        $newPost = new ForumPost();
        
        $postForm = $this->createForm(
            type: ForumPostForm::class,
            data:  $newPost,
            options: [
                'action' => $this->generateUrl('app_forum_add_post', ['postId' => 0]),
                'method' => Request::METHOD_POST,
            ]
        );
        
        return $this->render(
            view: 'forum/index.html.twig',
            parameters: [
                'posts' => $forumService->generatePostArray(),
                'form' => $postForm,
            ]
        );
    }

    #[Route('/forum/post/{id}', name:'app_forum_details', methods: Request::METHOD_GET)]
    public function getPostById(int $id, ForumPostRepository $forumPostRepository, ForumService $forumService, Request $request): Response
    {
        $post = $forumPostRepository->findOneById($id);
        if ($post == null) {
            return $this->render(
                view: 'forum/error.html.twig',
            );
        }

        $answers = $forumPostRepository->findBy(['parentId' => $id]);

        $answer = new ForumPost();
        $answerForm = $this->createForm(
            type: ForumPostForm::class,
            data: $answer,
            options: [
                'action' => $this->generateUrl('app_forum_add_post', ['postId' => $id]),
                'method' => Request::METHOD_POST,
            ]
        );

        return $this->render(
            view: 'forum/single.html.twig',
            parameters: [
                'post' => $post,
                'answers' => $answers, // Pass answers to Twig
                'answer_form' => $answerForm->createView(),
            ]
            );
    }

    #[Route(path: '/post/{postId}/', name: 'app_forum_add_post', methods:Request::METHOD_POST)]
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
        return $this->redirectToRoute(route: 'app_forum');
    }

    #[Route(path: '/answer/{postId}', name: 'app_forum_answer', methods:[Request::METHOD_GET])]
    public function getAnswerForm(int $postId, ForumService $forumService): Response
    {
        $answer = new ForumPost();

            $postForm = $this->createForm(
            type: ForumPostForm::class,
            data:  $answer,
            options: [
                'action' => $this->generateUrl('app_forum_add_post', ['postId' => $postId]),
                'method' => Request::METHOD_POST,
            ]
        );
                return $this->render(
            view: 'forum/index.html.twig',
            parameters: [
                'posts' => $forumService->generatePostArray(),
                'form' => $postForm,
            ]
        );
    }
}
