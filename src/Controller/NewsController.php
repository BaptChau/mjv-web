<?php

namespace App\Controller;

use App\Service\NewsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
    #[Route('/actualites', name: 'app_news', methods:[Request::METHOD_GET])]
    public function index(Request $request, NewsService $newsService): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = in_array((int)$request->query->get('perPage', 10), [5, 10, 20]) ? (int)$request->query->get('perPage', 10) : 10;

        $allNews = $newsService->getAllNews();
        $total = count($allNews);
        $newsList = array_slice($allNews, ($page - 1) * $perPage, $perPage);

        return $this->render('news/index.html.twig', [
            'newsList' => $newsList,
            'lastNews' => $newsService->getLastNNews(5),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'maxPage' => ceil($total / $perPage),
        ]);
    }

    #[Route('/actualites/{id}', name: 'app_news_details', methods:[Request::METHOD_GET])]
    public function getNewsById(int $id, NewsService $newsService): Response
    {
        $news = $newsService->getNewsById($id);
        if ($news === null) {
            return $this->render('news/error.html.twig');
        }

        return $this->render('news/details.html.twig', [
            'news' => $news,
        ]);
    }
}
