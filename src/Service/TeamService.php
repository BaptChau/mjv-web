<?php

namespace App\Service;

use App\Repository\TeamRepository;
use App\Enum\CategoryEnum;
use App\Entity\Team;

class TeamService
{
    public function __construct(private TeamRepository $teamRepository)
    {
    }

    /**
     * @return array<int, array{categoryLabel: string, slug: string, teams: Team[]}>
     */
    public function getAllWithCategoryLabel(): array
    {
        $teams = $this->teamRepository->findAll();
        $grouped = [];

        foreach ($teams as $team) {
            $category = $team->getCategory();
            if ($category === null) {
                continue;
            }

            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'categoryLabel' => $this->getCategoryLabel($category),
                    'slug' => $this->getCategorySlug($category),
                    'teams' => [],
                ];
            }

            $grouped[$category]['teams'][] = $team;
        }

        ksort($grouped);

        return array_values($grouped);
    }

    public function getAll(): array
    {
        return $this->teamRepository->findAll();
    }

    public function findBySlug(string $slug): ?Team
    {
        $teams = $this->findAllByCategorySlug($slug);

        return $teams[0] ?? null;
    }

    /**
     * @return Team[]
     */
    public function findAllByCategorySlug(string $slug): array
    {
        $enum = CategoryEnum::fromSlug($slug);
        if ($enum === null) {
            return [];
        }

        return $this->teamRepository->findBy(
            ['category' => $enum->value],
            ['gender' => 'DESC', 'label' => 'ASC']
        );
    }

    /**
     * Resolve a human readable label from category value or enum case.
     */
    public function getCategoryLabel(mixed $category): string
    {
        // If already an enum case
        if ($category instanceof CategoryEnum) {
            if (method_exists(CategoryEnum::class, 'getLabel')) {
                return CategoryEnum::getLabel($category);
            }
            return $category->name;
        }

        // If a backed value (int/string), try tryFrom
        if (method_exists(CategoryEnum::class, 'tryFrom')) {
            $enum = CategoryEnum::tryFrom($category);
            if ($enum) {
                if (method_exists(CategoryEnum::class, 'getLabel')) {
                    return CategoryEnum::getLabel($enum);
                }
                return $enum->name;
            }
        }

        // If fromLabel exists, try it (accepts slug/label)
        if (method_exists(CategoryEnum::class, 'fromLabel')) {
            $enum = CategoryEnum::fromLabel((string) $category);
            if ($enum) {
                if (method_exists(CategoryEnum::class, 'getLabel')) {
                    return CategoryEnum::getLabel($enum);
                }
                return $enum->name;
            }
        }

        // fallback: string cast
        return (string) $category;
    }

    public function getCategorySlug(mixed $category): string
    {
        // If already an enum case
        if ($category instanceof CategoryEnum) {
            if (method_exists(CategoryEnum::class, 'getSlug')) {
                return CategoryEnum::getSlug($category);
            }
            return strtolower($category->name);
        }

        if (method_exists(CategoryEnum::class, 'tryFrom')) {
            $enum = CategoryEnum::tryFrom($category);
            if ($enum) {
                if (method_exists(CategoryEnum::class, 'getSlug')) {
                    return CategoryEnum::getSlug($enum);
                }
                return strtolower($enum->name);
            }
        }

        if (method_exists(CategoryEnum::class, 'fromLabel')) {
            $enum = CategoryEnum::fromLabel((string) $category);
            if ($enum) {
                if (method_exists(CategoryEnum::class, 'getSlug')) {
                    return CategoryEnum::getSlug($enum);
                }
                return strtolower($enum->name);
            }
        }

        return strtolower((string) $category);
    }

    public function getCategoryFromSlug(string $slug): ?CategoryEnum
    {
        return CategoryEnum::fromSlug($slug);
    }

    public function findOne(int $id): ?Team
    {
        return $this->teamRepository->find($id);
    }

    public function ensureTeamMatchesSlug(Team $team, string $slug): bool
    {
        $category = $team->getCategory();
        return $this->getCategorySlug($category) === $slug;
    }

    /**
     * @return array{youth: array<int, array{label: string, slug: string}>, adult: array<int, array{label: string, slug: string}>}
     */
    public function getMenuGroups(): array
    {
        $teams = $this->teamRepository->findAll();
        $seen = [];
        $groups = [
            'youth' => [],
            'adult' => [],
        ];

        foreach ($teams as $team) {
            $category = $team->getCategory();
            if ($category === null || isset($seen[$category])) {
                continue;
            }

            $seen[$category] = true;
            $entry = [
                'label' => $this->getCategoryLabel($category),
                'slug' => $this->getCategorySlug($category),
            ];

            if (in_array($category, [CategoryEnum::SENIORS->value, CategoryEnum::LOISIRS->value], true)) {
                $groups['adult'][] = $entry;
            } else {
                $groups['youth'][] = $entry;
            }
        }

        usort($groups['youth'], fn ($a, $b) => $a['label'] <=> $b['label']);
        usort($groups['adult'], fn ($a, $b) => $a['label'] <=> $b['label']);

        return $groups;
    }
}
