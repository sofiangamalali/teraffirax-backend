<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ScoreCalculator;

class ScoreCalculatorTest extends TestCase
{
    /** @test */
    public function it_calculates_product_score_correctly_with_default_config()
    {
        // 1. Minimum values: Price=0, Discount=0, Rank=50000 (worst rank)
        $scoreMin = ScoreCalculator::calculate(
            price: 0,
            discountPercentage: 0,
            commissionPercent: 5.0,
            rank: 50001
        );
        $this->assertEquals(0.0, $scoreMin);

        // 2. Maximum values: Price=10000, Discount=80 (caps at 80), Commission=10% (1000 EGP, caps at 20), Rank=1 (best rank)
        $scoreMax = ScoreCalculator::calculate(
            price: 10000,
            discountPercentage: 80,
            commissionPercent: 10,
            rank: 1
        );
        $this->assertEquals(1.0, $scoreMax);

        // 3. Middle values
        // Price = 1000, Commission = 1.0% -> Commission Value = 10. Normalized = 10 / 20 = 0.5
        // Discount = 40 -> Normalized = 40 / 80 = 0.5
        // Rank = 25001 -> Normalized = 1 - (25000 / 50000) = 0.5
        // Total score should be: (0.5 * 0.33) + (0.5 * 0.33) + (0.5 * 0.34) = 0.5
        $scoreMid = ScoreCalculator::calculate(
            price: 1000,
            discountPercentage: 40,
            commissionPercent: 1.0,
            rank: 25001
        );
        $this->assertEquals(0.5, $scoreMid);
    }
}
