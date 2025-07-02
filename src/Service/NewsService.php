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
        $posts = $this->newsRepository->findBy([], ['createdAt' => 'DESC'], $n);
        return $posts;
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

    public function getAllNews(): array {
        $posts = $this->newsRepository->findBy([], ['id' => 'DESC']);
        return $posts;
    }
}