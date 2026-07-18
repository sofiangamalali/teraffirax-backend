<?php

// تفعيل وتحميل Laravel لتشغيل سكريبت خارجي بأمان
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Vendor;
use App\Models\CreatorVendorTracking;
use App\Models\Commission;

$trackId = 'dealsegypt21-21';
$vendorSlug = 'amazon';

try {
    $vendor = Vendor::where('slug', $vendorSlug)->first();
    if (!$vendor) {
        throw new Exception("Vendor not found for slug: {$vendorSlug}");
    }

    $tracking = CreatorVendorTracking::where('track_id', $trackId)
        ->where('vendor_id', $vendor->id)
        ->first();

    if (!$tracking) {
        throw new Exception("Tracking link not found for track_id: {$trackId} and vendor: {$vendorSlug}");
    }

    $platformPercentage = $tracking->platform_commission_percentage;
    $creatorPercentage = 100.00 - $platformPercentage;

    // البيانات المستخرجة لشهر 2 و 3 و 4
    $data = [
        // --- شهر فبراير 2026 ---
        ['earned_at' => '2026-02-28', 'clicks_count' => 5, 'items_count' => 7, 'returned_count' => 0, 'revenue_amount' => 710.35, 'total_earnings' => 35.52],
        ['earned_at' => '2026-02-27', 'clicks_count' => 11, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-02-26', 'clicks_count' => 5, 'items_count' => 4, 'returned_count' => 0, 'revenue_amount' => 516.78, 'total_earnings' => 54.93],
        ['earned_at' => '2026-02-23', 'clicks_count' => 5, 'items_count' => 6, 'returned_count' => 0, 'revenue_amount' => 483.87, 'total_earnings' => 24.65],
        ['earned_at' => '2026-02-21', 'clicks_count' => 6, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],

        // --- شهر مارس 2026 ---
        ['earned_at' => '2026-03-31', 'clicks_count' => 274, 'items_count' => 28, 'returned_count' => 6, 'revenue_amount' => 7190.95, 'total_earnings' => 735.12],
        ['earned_at' => '2026-03-30', 'clicks_count' => 429, 'items_count' => 39, 'returned_count' => 6, 'revenue_amount' => 11239.07, 'total_earnings' => 1137.84],
        ['earned_at' => '2026-03-29', 'clicks_count' => 439, 'items_count' => 65, 'returned_count' => 5, 'revenue_amount' => 10753.94, 'total_earnings' => 982.30],
        ['earned_at' => '2026-03-28', 'clicks_count' => 403, 'items_count' => 50, 'returned_count' => 7, 'revenue_amount' => 5053.69, 'total_earnings' => 380.95],
        ['earned_at' => '2026-03-27', 'clicks_count' => 617, 'items_count' => 14, 'returned_count' => 3, 'revenue_amount' => 3285.71, 'total_earnings' => 437.06],
        ['earned_at' => '2026-03-26', 'clicks_count' => 435, 'items_count' => 21, 'returned_count' => 4, 'revenue_amount' => 2536.98, 'total_earnings' => 175.37],
        ['earned_at' => '2026-03-25', 'clicks_count' => 678, 'items_count' => 37, 'returned_count' => 2, 'revenue_amount' => 4920.58, 'total_earnings' => 427.42],
        ['earned_at' => '2026-03-24', 'clicks_count' => 580, 'items_count' => 49, 'returned_count' => 9, 'revenue_amount' => 11950.99, 'total_earnings' => 924.07],
        ['earned_at' => '2026-03-23', 'clicks_count' => 653, 'items_count' => 34, 'returned_count' => 4, 'revenue_amount' => 7174.20, 'total_earnings' => 805.74],
        ['earned_at' => '2026-03-22', 'clicks_count' => 773, 'items_count' => 22, 'returned_count' => 0, 'revenue_amount' => 3087.68, 'total_earnings' => 350.94],
        ['earned_at' => '2026-03-21', 'clicks_count' => 558, 'items_count' => 5, 'returned_count' => 12, 'revenue_amount' => -835.52, 'total_earnings' => -121.71],
        ['earned_at' => '2026-03-20', 'clicks_count' => 734, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-03-19', 'clicks_count' => 360, 'items_count' => 35, 'returned_count' => 10, 'revenue_amount' => 4246.04, 'total_earnings' => 273.82],
        ['earned_at' => '2026-03-18', 'clicks_count' => 403, 'items_count' => 59, 'returned_count' => 9, 'revenue_amount' => 8788.44, 'total_earnings' => 962.28],
        ['earned_at' => '2026-03-17', 'clicks_count' => 588, 'items_count' => 97, 'returned_count' => 10, 'revenue_amount' => 16289.45, 'total_earnings' => 1657.28],
        ['earned_at' => '2026-03-16', 'clicks_count' => 740, 'items_count' => 76, 'returned_count' => 3, 'revenue_amount' => 44053.01, 'total_earnings' => 2238.30],
        ['earned_at' => '2026-03-15', 'clicks_count' => 1881, 'items_count' => 136, 'returned_count' => 2, 'revenue_amount' => 27450.93, 'total_earnings' => 3529.42],
        ['earned_at' => '2026-03-14', 'clicks_count' => 2958, 'items_count' => 66, 'returned_count' => 1, 'revenue_amount' => 10613.32, 'total_earnings' => 1271.36],
        ['earned_at' => '2026-03-13', 'clicks_count' => 433, 'items_count' => 5, 'returned_count' => 2, 'revenue_amount' => 186.76, 'total_earnings' => 23.66],
        ['earned_at' => '2026-03-12', 'clicks_count' => 312, 'items_count' => 26, 'returned_count' => 3, 'revenue_amount' => 3834.17, 'total_earnings' => 229.90],
        ['earned_at' => '2026-03-11', 'clicks_count' => 557, 'items_count' => 21, 'returned_count' => 1, 'revenue_amount' => 4015.04, 'total_earnings' => 591.55],
        ['earned_at' => '2026-03-10', 'clicks_count' => 335, 'items_count' => 11, 'returned_count' => 0, 'revenue_amount' => 2021.90, 'total_earnings' => 111.94],
        ['earned_at' => '2026-03-09', 'clicks_count' => 458, 'items_count' => 10, 'returned_count' => 2, 'revenue_amount' => 1502.78, 'total_earnings' => 68.20],
        ['earned_at' => '2026-03-08', 'clicks_count' => 430, 'items_count' => 8, 'returned_count' => 1, 'revenue_amount' => 3509.84, 'total_earnings' => 211.04],
        ['earned_at' => '2026-03-07', 'clicks_count' => 343, 'items_count' => 5, 'returned_count' => 0, 'revenue_amount' => 777.99, 'total_earnings' => 70.40],
        ['earned_at' => '2026-03-05', 'clicks_count' => 214, 'items_count' => 7, 'returned_count' => 0, 'revenue_amount' => 3287.83, 'total_earnings' => 355.01],
        ['earned_at' => '2026-03-04', 'clicks_count' => 166, 'items_count' => 5, 'returned_count' => 1, 'revenue_amount' => 597.47, 'total_earnings' => 31.45],

        // --- شهر أبريل 2026 ---
        ['earned_at' => '2026-04-30', 'clicks_count' => 192, 'items_count' => 4, 'returned_count' => 0, 'revenue_amount' => 2966.75, 'total_earnings' => 15.23],
        ['earned_at' => '2026-04-27', 'clicks_count' => 210, 'items_count' => 8, 'returned_count' => 2, 'revenue_amount' => 89.83, 'total_earnings' => 52.70],
        ['earned_at' => '2026-04-26', 'clicks_count' => 214, 'items_count' => 11, 'returned_count' => 1, 'revenue_amount' => 3171.61, 'total_earnings' => 270.82],
        ['earned_at' => '2026-04-25', 'clicks_count' => 213, 'items_count' => 14, 'returned_count' => 2, 'revenue_amount' => 2529.08, 'total_earnings' => 291.19],
        ['earned_at' => '2026-04-24', 'clicks_count' => 213, 'items_count' => 9, 'returned_count' => 0, 'revenue_amount' => 1772.44, 'total_earnings' => 116.64],
        ['earned_at' => '2026-04-23', 'clicks_count' => 336, 'items_count' => 6, 'returned_count' => 2, 'revenue_amount' => 2654.57, 'total_earnings' => 347.02],
        ['earned_at' => '2026-04-22', 'clicks_count' => 192, 'items_count' => 12, 'returned_count' => 2, 'revenue_amount' => 1905.76, 'total_earnings' => 148.96],
        ['earned_at' => '2026-04-21', 'clicks_count' => 327, 'items_count' => 12, 'returned_count' => 1, 'revenue_amount' => 7335.70, 'total_earnings' => 693.53],
        ['earned_at' => '2026-04-20', 'clicks_count' => 264, 'items_count' => 5, 'returned_count' => 1, 'revenue_amount' => -1339.99, 'total_earnings' => -259.69],
        ['earned_at' => '2026-04-19', 'clicks_count' => 339, 'items_count' => 6, 'returned_count' => 0, 'revenue_amount' => 608.55, 'total_earnings' => 61.36],
        ['earned_at' => '2026-04-18', 'clicks_count' => 330, 'items_count' => 15, 'returned_count' => 0, 'revenue_amount' => 5458.04, 'total_earnings' => 683.11],
        ['earned_at' => '2026-04-17', 'clicks_count' => 394, 'items_count' => 23, 'returned_count' => 0, 'revenue_amount' => 2792.42, 'total_earnings' => 305.55],
        ['earned_at' => '2026-04-16', 'clicks_count' => 267, 'items_count' => 8, 'returned_count' => 1, 'revenue_amount' => 1085.00, 'total_earnings' => 140.43],
        ['earned_at' => '2026-04-15', 'clicks_count' => 311, 'items_count' => 10, 'returned_count' => 3, 'revenue_amount' => 2296.02, 'total_earnings' => 248.77],
        ['earned_at' => '2026-04-14', 'clicks_count' => 351, 'items_count' => 22, 'returned_count' => 2, 'revenue_amount' => 3770.02, 'total_earnings' => 282.12],
        ['earned_at' => '2026-04-13', 'clicks_count' => 387, 'items_count' => 11, 'returned_count' => 1, 'revenue_amount' => 3353.08, 'total_earnings' => 393.98],
        ['earned_at' => '2026-04-12', 'clicks_count' => 302, 'items_count' => 8, 'returned_count' => 1, 'revenue_amount' => 2388.71, 'total_earnings' => 289.95],
        ['earned_at' => '2026-04-11', 'clicks_count' => 413, 'items_count' => 19, 'returned_count' => 1, 'revenue_amount' => 2845.57, 'total_earnings' => 126.79],
        ['earned_at' => '2026-04-10', 'clicks_count' => 377, 'items_count' => 10, 'returned_count' => 3, 'revenue_amount' => -1548.57, 'total_earnings' => -229.53],
        ['earned_at' => '2026-04-09', 'clicks_count' => 263, 'items_count' => 15, 'returned_count' => 4, 'revenue_amount' => 4662.38, 'total_earnings' => 527.31],
        ['earned_at' => '2026-04-08', 'clicks_count' => 396, 'items_count' => 24, 'returned_count' => 2, 'revenue_amount' => 3738.63, 'total_earnings' => 239.81],
        ['earned_at' => '2026-04-07', 'clicks_count' => 331, 'items_count' => 10, 'returned_count' => 5, 'revenue_amount' => 51.77, 'total_earnings' => -5.49],
        ['earned_at' => '2026-04-06', 'clicks_count' => 265, 'items_count' => 16, 'returned_count' => 0, 'revenue_amount' => 3079.36, 'total_earnings' => 240.02],
        ['earned_at' => '2026-04-05', 'clicks_count' => 417, 'items_count' => 34, 'returned_count' => 2, 'revenue_amount' => 2960.25, 'total_earnings' => 77.33],
        ['earned_at' => '2026-04-04', 'clicks_count' => 462, 'items_count' => 25, 'returned_count' => 4, 'revenue_amount' => 4740.57, 'total_earnings' => 419.42],
        ['earned_at' => '2026-04-03', 'clicks_count' => 445, 'items_count' => 16, 'returned_count' => 6, 'revenue_amount' => 498.17, 'total_earnings' => 125.08],
        ['earned_at' => '2026-04-02', 'clicks_count' => 355, 'items_count' => 12, 'returned_count' => 2, 'revenue_amount' => 2138.52, 'total_earnings' => 90.05],
        ['earned_at' => '2026-04-01', 'clicks_count' => 372, 'items_count' => 28, 'returned_count' => 8, 'revenue_amount' => 2372.22, 'total_earnings' => 111.70],

        // --- شهر مايو 2026 ---
        ['earned_at' => '2026-05-30', 'clicks_count' => 110, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-05-29', 'clicks_count' => 140, 'items_count' => 12, 'returned_count' => 0, 'revenue_amount' => 6326.91, 'total_earnings' => 368.15],
        ['earned_at' => '2026-05-27', 'clicks_count' => 104, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-05-26', 'clicks_count' => 127, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-05-25', 'clicks_count' => 107, 'items_count' => 11, 'returned_count' => 1, 'revenue_amount' => 724.73, 'total_earnings' => 39.51],
        ['earned_at' => '2026-05-24', 'clicks_count' => 118, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-05-23', 'clicks_count' => 98, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-05-21', 'clicks_count' => 100, 'items_count' => 9, 'returned_count' => 0, 'revenue_amount' => 728.35, 'total_earnings' => 78.49],
        ['earned_at' => '2026-05-20', 'clicks_count' => 165, 'items_count' => 8, 'returned_count' => 5, 'revenue_amount' => 1410.22, 'total_earnings' => 188.39],
        ['earned_at' => '2026-05-19', 'clicks_count' => 198, 'items_count' => 8, 'returned_count' => 0, 'revenue_amount' => 774.86, 'total_earnings' => 78.87],
        ['earned_at' => '2026-05-18', 'clicks_count' => 179, 'items_count' => 13, 'returned_count' => 0, 'revenue_amount' => 1183.05, 'total_earnings' => 80.98],
        ['earned_at' => '2026-05-17', 'clicks_count' => 132, 'items_count' => 14, 'returned_count' => 2, 'revenue_amount' => 2353.76, 'total_earnings' => 103.75],
        ['earned_at' => '2026-05-16', 'clicks_count' => 232, 'items_count' => 37, 'returned_count' => 2, 'revenue_amount' => 3219.85, 'total_earnings' => 307.01],
        ['earned_at' => '2026-05-15', 'clicks_count' => 201, 'items_count' => 21, 'returned_count' => 0, 'revenue_amount' => 1060.88, 'total_earnings' => 69.30],
        ['earned_at' => '2026-05-14', 'clicks_count' => 117, 'items_count' => 25, 'returned_count' => 0, 'revenue_amount' => 3044.61, 'total_earnings' => 228.99],
        ['earned_at' => '2026-05-13', 'clicks_count' => 268, 'items_count' => 10, 'returned_count' => 0, 'revenue_amount' => 5891.04, 'total_earnings' => 710.79],
        ['earned_at' => '2026-05-12', 'clicks_count' => 245, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-05-10', 'clicks_count' => 153, 'items_count' => 6, 'returned_count' => 4, 'revenue_amount' => 107.65, 'total_earnings' => -0.59],
        ['earned_at' => '2026-05-08', 'clicks_count' => 203, 'items_count' => 6, 'returned_count' => 0, 'revenue_amount' => 681.66, 'total_earnings' => 58.61],
        ['earned_at' => '2026-05-07', 'clicks_count' => 96, 'items_count' => 4, 'returned_count' => 0, 'revenue_amount' => 347.69, 'total_earnings' => 23.68],
        ['earned_at' => '2026-05-06', 'clicks_count' => 86, 'items_count' => 7, 'returned_count' => 0, 'revenue_amount' => 760.90, 'total_earnings' => 61.63],
        ['earned_at' => '2026-05-04', 'clicks_count' => 162, 'items_count' => 7, 'returned_count' => 1, 'revenue_amount' => 1550.66, 'total_earnings' => 119.94],
        ['earned_at' => '2026-05-03', 'clicks_count' => 155, 'items_count' => 9, 'returned_count' => 1, 'revenue_amount' => 2052.50, 'total_earnings' => 45.60],
        ['earned_at' => '2026-05-01', 'clicks_count' => 206, 'items_count' => 22, 'returned_count' => 0, 'revenue_amount' => 3373.75, 'total_earnings' => 163.18],

        // --- شهر يونيو 2026 ---
        ['earned_at' => '2026-06-30', 'clicks_count' => 48, 'items_count' => 7, 'returned_count' => 1, 'revenue_amount' => -122.26, 'total_earnings' => 19.58],
        ['earned_at' => '2026-06-28', 'clicks_count' => 66, 'items_count' => 4, 'returned_count' => 1, 'revenue_amount' => 487.25, 'total_earnings' => 27.60],
        ['earned_at' => '2026-06-27', 'clicks_count' => 74, 'items_count' => 5, 'returned_count' => 2, 'revenue_amount' => 996.86, 'total_earnings' => 51.01],
        ['earned_at' => '2026-06-25', 'clicks_count' => 111, 'items_count' => 11, 'returned_count' => 0, 'revenue_amount' => 2290.37, 'total_earnings' => 91.36],
        ['earned_at' => '2026-06-24', 'clicks_count' => 102, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-06-21', 'clicks_count' => 62, 'items_count' => 5, 'returned_count' => 0, 'revenue_amount' => 1589.25, 'total_earnings' => 112.31],
        ['earned_at' => '2026-06-17', 'clicks_count' => 71, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-06-14', 'clicks_count' => 110, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-06-09', 'clicks_count' => 114, 'items_count' => 5, 'returned_count' => 0, 'revenue_amount' => 2787.09, 'total_earnings' => 212.31],
        ['earned_at' => '2026-06-08', 'clicks_count' => 71, 'items_count' => 9, 'returned_count' => 5, 'revenue_amount' => 1353.02, 'total_earnings' => 95.95],
        ['earned_at' => '2026-06-07', 'clicks_count' => 91, 'items_count' => 25, 'returned_count' => 0, 'revenue_amount' => 4737.46, 'total_earnings' => 396.52],
        ['earned_at' => '2026-06-06', 'clicks_count' => 88, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-06-05', 'clicks_count' => 119, 'items_count' => 5, 'returned_count' => 5, 'revenue_amount' => 639.24, 'total_earnings' => 42.26],
        ['earned_at' => '2026-06-03', 'clicks_count' => 125, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-06-02', 'clicks_count' => 79, 'items_count' => 17, 'returned_count' => 1, 'revenue_amount' => 12399.95, 'total_earnings' => 628.86],

        // --- شهر يوليو 2026 ---
        ['earned_at' => '2026-07-14', 'clicks_count' => 76, 'items_count' => 6, 'returned_count' => 0, 'revenue_amount' => 2025.00, 'total_earnings' => 266.84],
        ['earned_at' => '2026-07-10', 'clicks_count' => 47, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
        ['earned_at' => '2026-07-07', 'clicks_count' => 85, 'items_count' => 8, 'returned_count' => 0, 'revenue_amount' => 746.92, 'total_earnings' => 37.14],
        ['earned_at' => '2026-07-04', 'clicks_count' => 41, 'items_count' => 5, 'returned_count' => 0, 'revenue_amount' => 751.54, 'total_earnings' => 35.21],
        ['earned_at' => '2026-07-01', 'clicks_count' => 48, 'items_count' => 0, 'returned_count' => 0, 'revenue_amount' => 0.00, 'total_earnings' => 0.00],
    ];

    $count = 0;
    foreach ($data as $row) {
        $commissionAmount = $row['total_earnings'] * ($creatorPercentage / 100.00);

        Commission::updateOrCreate(
            [
                'creator_id' => $tracking->creator_id,
                'vendor_id' => $tracking->vendor_id,
                'earned_at' => $row['earned_at'],
            ],
            [
                'clicks_count' => $row['clicks_count'],
                'items_count' => $row['items_count'],
                'returned_count' => $row['returned_count'],
                'revenue_amount' => $row['revenue_amount'],
                'commission_amount' => $commissionAmount,
                'calculated_at' => null,
                'matured_at' => null,
            ]
        );
        $count++;
    }

    echo "\nDone! Imported {$count} rows successfully for track_id: {$trackId}\n";

} catch (Exception $e) {
    echo "\nError occurred: " . $e->getMessage() . "\n";
}
