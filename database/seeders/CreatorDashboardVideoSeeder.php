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
                'thumbnail_url' => 'https://p19-common-sign.tiktokcdn.com/tos-alisg-p-0037/okIAQ49eAfuId4IjjAaTMALPeAZG6FADAWKEoE~tplv-tiktokx-dmt-logom:tos-alisg-i-0068/oIouqAhERQgAfcGfrFIA0UDpAPOFEBxACgoHAt.image?dr=14573&x-expires=1779199200&x-signature=5rMVaavFBRtYFG05OG83pkb2T70%3D&t=4d5b0474&ps=13740610&shp=81f88b70&shcp=43f4a2f9&idc=my2',
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
                'thumbnail_url' => 'https://p16-common-sign.tiktokcdn.com/tos-alisg-p-0037/oMGe5rAHeAQi36IeAGrTorE0rID4LEAknqAgvp~tplv-tiktokx-origin.image?dr=14575&x-expires=1779199200&x-signature=3uCpsNttlLYaAk6feIXGPXy8E28%3D&t=4d5b0474&ps=13740610&shp=81f88b70&shcp=43f4a2f9&idc=my2',
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
                'thumbnail_url' => 'https://p16-common-sign.tiktokcdn.com/tos-alisg-p-0037/oUABgFrYuogIDrvjRfPBKIEpgcE8BqNISp4eEg~tplv-tiktokx-origin.image?dr=14575&x-expires=1779199200&x-signature=6mWWn7nWYRAm1Bkj6uk%2FBijW%2FIQ%3D&t=4d5b0474&ps=13740610&shp=81f88b70&shcp=43f4a2f9&idc=my2',
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
