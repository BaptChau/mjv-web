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
        $totalPagination = $newsService->getPaginatedNews(1, 1);

        return $this->render('news/index.html.twig', [
            'lastNews' => $newsService->getLastNNews(6),
            'total' => $totalPagination['total'] ?? 0,
        ]);
    }

    #[Route('/actualites/archives', name: 'app_news_archives', methods:[Request::METHOD_GET])]
    public function archives(Request $request, NewsService $newsService): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = in_array((int) $request->query->get('perPage', 10), [5, 10, 20], true)
            ? (int) $request->query->get('perPage', 10)
            : 10;

        $pagination = $newsService->getPaginatedNews($page, $perPage);

        return $this->render('news/archives.html.twig', [
            'newsList' => $pagination['items'],
            'page' => $page,
            'perPage' => $perPage,
            'total' => $pagination['total'],
            'maxPage' => $pagination['maxPage'],
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
