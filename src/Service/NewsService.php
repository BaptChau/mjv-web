<?php
namespace App\Service;

use App\Repository\NewsCommentRepository;
use App\Repository\NewsPostRepository;

class NewsService {
    public function __construct(
        public readonly NewsPostRepository $newsRepository,
        public readonly NewsCommentRepository $newsCommentRepository,
    ) {
    }

    public function getLastNNews(int $n): array {
        return $this->newsRepository->findLatest($n);
    }

    public function getNewsById(int $id): ?array {
        $post = $this->newsRepository->findOneById($id);
        if (!$post) {
            return null;
        }
        $comments = $this->newsCommentRepository->findBy(['newsId' => $post->getId()], ['createdAt' => 'DESC']);
        return [
            'post' => $post,
            'comments' => $comments,
        ];
    }

    public function getPaginatedNews(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $items = $this->newsRepository->findPaginated($page, $perPage);
        $total = $this->newsRepository->countAll();

        return [
            'items' => $items,
            'total' => $total,
            'maxPage' => max(1, (int) ceil($total / $perPage)),
        ];
    }
}
