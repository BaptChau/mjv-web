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
     * Return an array of ['team' => Team, 'categoryLabel' => string]
     *
     * @return array<int, array{team: Team, categoryLabel: string}>
     */
    public function getAllWithCategoryLabel(): array
    {
        $teams = $this->teamRepository->findAll();

        return array_map(function (Team $team) {
            return [
                'team' => $team,
                'categoryLabel' => $this->getCategoryLabel($team->getCategory()),
            ];
        }, $teams);
    }

    public function getAll(): array
    {
        return $this->teamRepository->findAll();
    }

    public function findBySlug(string $slug): ?Team
    {
        // first try by slug field if present on entity
        $team = $this->teamRepository->findOneBy(['slug' => $slug]);
        if ($team) {
            return $team;
        }

        // fallback: try to resolve category from CategoryEnum label
        if (method_exists(CategoryEnum::class, 'fromLabel')) {
            $enum = CategoryEnum::fromLabel($slug);
            $value = $enum?->value ?? null;
            if ($value !== null) {
                return $this->teamRepository->findOneBy(['category' => $value]);
            }
        }

        return null;
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
}
