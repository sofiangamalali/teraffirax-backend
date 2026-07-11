<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CreatorDashboardVideo;
use Illuminate\Database\Seeder;

class CreatorDashboardVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $egypt = Country::where('iso2', 'eg')->first();

        if (!$egypt) {
            return;
        }

        $videos = [
            [
                'video_url' => 'https://www.tiktok.com/@deals_egypt/video/7616714967059860756',
                'thumbnail_url' => url('uploads/thumnails/boxers.png'),
                'country_id' => $egypt->id,
                'views_count' => 114400,
                'likes_count' => 1793,
                'comments_count' => 51,
                'shares_count' => 1055,
                'total_orders' => 260,
                'total_commission' => 4500,
            ],
            [
                'video_url' => 'https://www.tiktok.com/@deals_egypt/video/7606394521873681685',
                'thumbnail_url' => url('uploads/thumnails/balance.png'),
                'country_id' => $egypt->id,
                'views_count' => 136400,
                'likes_count' => 1935,
                'comments_count' => 46,
                'shares_count' => 860,
                'total_orders' => 85,
                'total_commission' => 2000,
            ],
            [
                'video_url' => 'https://www.tiktok.com/@deals_egypt/video/7611273023139613973',
                'thumbnail_url' => url('uploads/thumnails/airpods.jpg'),
                'country_id' => $egypt->id,
                'views_count' => 25000,
                'likes_count' => 1716,
                'comments_count' => 47,
                'shares_count' => 697,
                'total_orders' => 50,
                'total_commission' => 2840,
            ]
        ];

        foreach ($videos as $video) {
            CreatorDashboardVideo::updateOrCreate(
                ['video_url' => $video['video_url']],
                $video
            );
        }
    }
}
