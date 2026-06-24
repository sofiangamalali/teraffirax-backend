<?php

namespace App\Services;

class ScoreCalculator
{
    /**
     * Calculate product score as a float between 0.0 and 1.0.
     */
    public static function calculate(float $price, float $discountPercentage, float $commissionPercent, int $rank): float
    {
        $discountNormalized = min($discountPercentage / config('scoring.discount_cap', 80.0), 1.0);

        $commissionValue      = $price * ($commissionPercent / 100);
        $commissionNormalized = min($commissionValue / config('scoring.commission_reference', 150.0), 1.0);

        $rankNormalized = max(1 - (($rank - 1) / config('scoring.rank_reference', 50000.0)), 0.0);

        $weights = config('scoring.weights', [
            'commission' => 0.33,
            'discount'   => 0.33,
            'rank'       => 0.34,
        ]);

        return ($commissionNormalized * $weights['commission'])
             + ($discountNormalized   * $weights['discount'])
             + ($rankNormalized       * $weights['rank']);
    }
}
