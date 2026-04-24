<?php

namespace App\Services;

use InvalidArgumentException;

class SlugService
{
    private const CHARS = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private const BASE = 62;
    private const MULTIPLIER = 7919;
    private const OFFSET = 1237;
    private const SLUG_LENGTH = 8;

    /**
     * Generate an obfuscated Base62 slug from an ID.
     */
    public function generateSlug(int $id): string
    {
        // Step 1: Obfuscate
        $obfuscated = ($id * self::MULTIPLIER) + self::OFFSET;

        // Step 2: Convert to Base62
        $encoded = "";
        $temp = $obfuscated;
        
        if ($temp === 0) {
            $encoded = "0";
        } else {
            while ($temp > 0) {
                $remainder = $temp % self::BASE;
                $encoded = self::CHARS[$remainder] . $encoded;
                $temp = intdiv($temp, self::BASE);
            }
        }

        // Step 3: Pad to fixed length
        return str_pad($encoded, self::SLUG_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Decode an obfuscated Base62 slug back to an ID.
     * 
     * @throws InvalidArgumentException
     */
    public function decodeSlug(string $slug): int
    {
        // Step 1: Base62 decode
        $num = 0;
        $len = strlen($slug);
        
        for ($i = 0; $i < $len; $i++) {
            $char = $slug[$i];
            $value = strpos(self::CHARS, $char);
            
            if ($value === false) {
                throw new InvalidArgumentException("Invalid character in slug");
            }
            
            $num = ($num * self::BASE) + $value;
        }

        // Step 2: Reverse obfuscation
        $adjusted = $num - self::OFFSET;
        
        if ($adjusted < 0 || $adjusted % self::MULTIPLIER !== 0) {
            throw new InvalidArgumentException("Invalid slug structure");
        }

        return $adjusted / self::MULTIPLIER;
    }
}
