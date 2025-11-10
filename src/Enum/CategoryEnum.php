<?php

namespace App\Enum;
enum CategoryEnum: int
{
    case BABY = 1;
    CASE U7 = 2;
    CASE U9 = 3;
    CASE U11 = 4;
    CASE U13 = 5;
    CASE U15 = 6;
    CASE U18 = 7;
    CASE SENIORS = 8;
    CASE LOISIRS = 9;

    public static function getLabel(CategoryEnum $category): string
    {
        return match($category) {
            CategoryEnum::BABY => 'Baby',
            CategoryEnum::U7 => 'U7',
            CategoryEnum::U9 => 'U9',
            CategoryEnum::U11 => 'U11',
            CategoryEnum::U13 => 'U13',
            CategoryEnum::U15 => 'U15',
            CategoryEnum::U18 => 'U18',
            CategoryEnum::SENIORS => 'Seniors',
            CategoryEnum::LOISIRS => 'Loisirs',
        };
    }

    public static function fromLabel(string $label): ?CategoryEnum
    {
        return match(strtolower($label)) {
            'baby' => CategoryEnum::BABY,
            'u7' => CategoryEnum::U7,
            'u9' => CategoryEnum::U9,
            'u11' => CategoryEnum::U11,
            'u13' => CategoryEnum::U13,
            'u15' => CategoryEnum::U15,
            'u18' => CategoryEnum::U18,
            'seniors' => CategoryEnum::SENIORS,
            'loisirs' => CategoryEnum::LOISIRS,
            default => null,
        };
    }
}