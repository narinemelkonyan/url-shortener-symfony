<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Generates random short codes made of letters and digits.
 */
final class CodeGenerator
{
    private const string ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    private const int MIN_LENGTH = 4;
    private const int MAX_LENGTH = 8;
    private const int DEFAULT_LENGTH = 7;

    public function __construct(
        private readonly int $length = self::DEFAULT_LENGTH,
    ) {
        if ($this->length < self::MIN_LENGTH || $this->length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Short code length must be between %d and %d.', self::MIN_LENGTH, self::MAX_LENGTH)
            );
        }
    }

    /**
     * Generates a single random short code of the configured length.
     */
    public function generate(): string
    {
        $alphabetLength = strlen(self::ALPHABET);
        $code = '';

        for ($i = 0; $i < $this->length; $i++) {
            $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }
}
