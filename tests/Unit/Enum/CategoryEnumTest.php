<?php

namespace App\Tests\Unit\Enum;

use App\Enum\CategoryEnum;
use PHPUnit\Framework\TestCase;

class CategoryEnumTest extends TestCase
{
    /**
     * @dataProvider provideLabelCases
     */
    public function testGetLabel(CategoryEnum $enum, string $expected): void
    {
        self::assertSame($expected, CategoryEnum::getLabel($enum));
    }

    public static function provideLabelCases(): iterable
    {
        yield [CategoryEnum::BABY, 'Baby'];
        yield [CategoryEnum::U7, 'U7'];
        yield [CategoryEnum::U9, 'U9'];
        yield [CategoryEnum::U11, 'U11'];
        yield [CategoryEnum::U13, 'U13'];
        yield [CategoryEnum::U15, 'U15'];
        yield [CategoryEnum::U18, 'U18'];
        yield [CategoryEnum::SENIORS, 'Seniors'];
        yield [CategoryEnum::LOISIRS, 'Loisirs'];
    }

    /**
     * @dataProvider provideSlugCases
     */
    public function testGetSlug(CategoryEnum $enum, string $expected): void
    {
        self::assertSame($expected, CategoryEnum::getSlug($enum));
    }

    public static function provideSlugCases(): iterable
    {
        yield [CategoryEnum::BABY, 'baby'];
        yield [CategoryEnum::SENIORS, 'seniors'];
        yield [CategoryEnum::LOISIRS, 'loisirs'];
        yield [CategoryEnum::U15, 'u15'];
    }

    public function testFromLabelReturnsEnumForValidLabel(): void
    {
        self::assertSame(CategoryEnum::BABY, CategoryEnum::fromLabel('Baby'));
        self::assertSame(CategoryEnum::SENIORS, CategoryEnum::fromLabel('seniors'));
    }

    public function testFromLabelReturnsNullForUnknown(): void
    {
        self::assertNull(CategoryEnum::fromLabel('unknown'));
    }

    public function testFromSlugReturnsEnumForValidSlug(): void
    {
        self::assertSame(CategoryEnum::U15, CategoryEnum::fromSlug('u15'));
        self::assertSame(CategoryEnum::SENIORS, CategoryEnum::fromSlug('seniors'));
    }

    public function testFromSlugReturnsNullForUnknown(): void
    {
        self::assertNull(CategoryEnum::fromSlug('does-not-exist'));
    }

    public function testAllCasesHaveLabelsAndSlugs(): void
    {
        foreach (CategoryEnum::cases() as $case) {
            $label = CategoryEnum::getLabel($case);
            $slug = CategoryEnum::getSlug($case);

            self::assertNotEmpty($label);
            self::assertNotEmpty($slug);
            self::assertSame(strtolower($label), $slug);
        }
    }

    public function testFromSlugIsRoundTripConsistentWithFromLabel(): void
    {
        foreach (CategoryEnum::cases() as $case) {
            $slug = CategoryEnum::getSlug($case);
            $recovered = CategoryEnum::fromSlug($slug);

            self::assertSame($case, $recovered, "fromSlug(getSlug({$case->name})) must return the original case.");
        }
    }

    public function testFromLabelIsCaseInsensitive(): void
    {
        self::assertSame(CategoryEnum::U15, CategoryEnum::fromLabel('U15'));
        self::assertSame(CategoryEnum::U15, CategoryEnum::fromLabel('u15'));
        self::assertSame(CategoryEnum::SENIORS, CategoryEnum::fromLabel('SENIORS'));
        self::assertSame(CategoryEnum::SENIORS, CategoryEnum::fromLabel('Seniors'));
    }

    public function testFromSlugReturnsNullForEmptyString(): void
    {
        self::assertNull(CategoryEnum::fromSlug(''));
    }

    public function testEnumHasNineCases(): void
    {
        self::assertCount(9, CategoryEnum::cases());
    }

    public function testAllEnumValuesAreDistinct(): void
    {
        $values = array_map(fn (CategoryEnum $case) => $case->value, CategoryEnum::cases());

        self::assertSame(count($values), count(array_unique($values)), 'All CategoryEnum values must be unique.');
    }
}
