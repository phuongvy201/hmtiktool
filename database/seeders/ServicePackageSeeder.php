<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicePackage;
use Illuminate\Support\Str;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Gói Cơ bản',
                'slug' => 'goi-co-ban',
                'description' => 'Gói dịch vụ cơ bản cho người dùng mới bắt đầu',
                'price' => 500000,
                'currency' => 'VND',
                'duration_days' => 30,
                'features' => json_encode(['user_management', 'project_management', 'file_upload']),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Gói Pro',
                'slug' => 'goi-pro',
                'description' => 'Gói dịch vụ chuyên nghiệp cho doanh nghiệp vừa và nhỏ',
                'price' => 1500000,
                'currency' => 'VND',
                'duration_days' => 30,
                'features' => json_encode(['user_management', 'project_management', 'file_upload', 'api_access', 'advanced_analytics', 'team_collaboration']),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gói Enterprise',
                'slug' => 'goi-enterprise',
                'description' => 'Gói dịch vụ cao cấp cho doanh nghiệp lớn',
                'price' => 5000000,
                'currency' => 'VND',
                'duration_days' => 30,
                'features' => json_encode(['user_management', 'project_management', 'file_upload', 'api_access', 'advanced_analytics', 'priority_support', 'custom_branding', 'backup_restore', 'team_collaboration', 'advanced_security']),
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $packageData) {
            ServicePackage::create($packageData);
        }
    }
}
