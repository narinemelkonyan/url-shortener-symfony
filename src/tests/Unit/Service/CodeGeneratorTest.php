<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\CodeGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CodeGenerator.
 */
final class CodeGeneratorTest extends TestCase
{
    public function testGeneratesCodeOfConfiguredLength(): void
    {
        $generator = new CodeGenerator(7);

        self::assertSame(7, strlen($generator->generate()));
    }

    public function testGeneratesCodeWithinAllowedLengthBounds(): void
    {
        foreach ([4, 5, 6, 7, 8] as $length) {
            $generator = new CodeGenerator($length);

            self::assertSame($length, strlen($generator->generate()));
        }
    }

    public function testGeneratedCodeContainsOnlyAllowedCharacters(): void
    {
        $generator = new CodeGenerator(8);

        for ($i = 0; $i < 50; $i++) {
            self::assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $generator->generate());
        }
    }

    public function testProducesDifferentCodes(): void
    {
        $generator = new CodeGenerator(8);

        self::assertNotSame($generator->generate(), $generator->generate());
    }

    public function testLengthBelowMinimumIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CodeGenerator(3);
    }

    public function testLengthAboveMaximumIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CodeGenerator(9);
    }
}
