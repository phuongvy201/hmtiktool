<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Team TikTok Shop A',
                'slug' => 'team-tiktok-shop-a',
                'description' => 'Team quản lý TikTok Shop cho thương hiệu A - chuyên về quần áo thời trang',
                'status' => 'active',
            ],
            [
                'name' => 'Team TikTok Shop B',
                'slug' => 'team-tiktok-shop-b',
                'description' => 'Team quản lý TikTok Shop cho thương hiệu B - chuyên về mỹ phẩm và làm đẹp',
                'status' => 'active',
            ],
            [
                'name' => 'Team TikTok Shop C',
                'slug' => 'team-tiktok-shop-c',
                'description' => 'Team quản lý TikTok Shop cho thương hiệu C - chuyên về điện tử và công nghệ',
                'status' => 'active',
            ],
            [
                'name' => 'Team TikTok Shop D',
                'slug' => 'team-tiktok-shop-d',
                'description' => 'Team quản lý TikTok Shop cho thương hiệu D - chuyên về thực phẩm và đồ uống',
                'status' => 'inactive',
            ],
            [
                'name' => 'Team TikTok Shop E',
                'slug' => 'team-tiktok-shop-e',
                'description' => 'Team quản lý TikTok Shop cho thương hiệu E - chuyên về đồ gia dụng và nhà cửa',
                'status' => 'suspended',
            ],
        ];

        foreach ($teams as $teamData) {
            Team::create($teamData);
        }
    }
}
