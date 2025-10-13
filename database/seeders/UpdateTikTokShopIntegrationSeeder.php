<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TikTokShopIntegration;
use Carbon\Carbon;

class UpdateTikTokShopIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Token data từ response TikTok Shop API
        $tokenData = [
            'access_token' => 'GCP_9cKzKQAAAABMbZul1iFwlPGIepgCyHEOzmDbm_E3ENE0BNWuhPEZhaone2ynABe8wT0uP56NIckbF7By5Znb4gTgIvS3feEks6RgSf2bpYrcvI-A-ST3iwJwBGo0iVREW69BOdvmIIqcs-EBGm7HXkCMZ-0vXSHlve6_rZ_LksXn_dHjmF5Kz7bAex6iBI5DBC0gfjRFinM-8N7rcAUUZBm3-dpOiYAfJqm01Vft9gV0nxK4XJyUSocFGl7PsTWQJX88VMhEjhZj01Ul7DpB44nbOk7kfCK1_c9vLI5dgcpAvHTOvpTeLbhUi-5KhixkX100yBPRIcQZklNu9UMVQWMN584p12QkpBVGNFskDB8hT2ZrmPJdVkY5SmAHicWOo9I8j7DkrkU-O8sQflILCgk96I-1CYG8kbXshHQJ9F36mOCF3REv3Mkdl4P5_Tmj-UVZyce5oMHTwFgvYJdCvvs_o91li1swvWnd-AckiegGMzPP5Ke-KN3ua3PnS1zAyGoHlHpXioepM4u3yETKPGnasgodgnOuaLPjIFO_a_CgAS3xZjqeng',
            'refresh_token' => 'GCP_rj7O2AAAAAAu99O-tQgOcUCFoWGuSOR2FjOFSWr5IIX7Rsr3U4I6AQ1eQNiAeTpebkLIDNZqANE',
            'open_id' => 'Qr02MwAAAAB7EXg2YuF9ECFvslSS0tbKAiL9pbs_tuTguT-CUWFkcw',
            'seller_name' => 'BLUPRINTER Tees',
            'seller_base_region' => 'GB',
            'access_token_expire_in' => 1756290759,
            'refresh_token_expire_in' => 1763457446,
        ];

        $this->command->info('=== Cập nhật TikTok Shop Integration ===');

        // Tìm tích hợp với app_key
        $integration = TikTokShopIntegration::where('app_key', '6h5b0bsgaonml')->first();

        if (!$integration) {
            $this->command->error('❌ Không tìm thấy tích hợp với app_key: 6h5b0bsgaonml');
            $this->command->info('Vui lòng tạo tích hợp trước trong admin panel.');
            return;
        }

        $this->command->info('✅ Tìm thấy tích hợp cho team: ' . $integration->team->name);
        $this->command->info('Trạng thái hiện tại: ' . $integration->status);

        // Cập nhật tokens và thông tin
        $updateData = [
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'open_id' => $tokenData['open_id'],
            'seller_name' => $tokenData['seller_name'],
            'seller_region' => $tokenData['seller_base_region'],
            'access_token_expires_at' => Carbon::createFromTimestamp($tokenData['access_token_expire_in']),
            'refresh_token_expires_at' => Carbon::createFromTimestamp($tokenData['refresh_token_expire_in']),
            'status' => 'active',
            'error_message' => null,
        ];

        try {
            $integration->update($updateData);

            $this->command->info('✅ Cập nhật thành công!');
            $this->command->info('Access Token: ' . substr($tokenData['access_token'], 0, 50) . '...');
            $this->command->info('Refresh Token: ' . substr($tokenData['refresh_token'], 0, 50) . '...');
            $this->command->info('Open ID: ' . $tokenData['open_id']);
            $this->command->info('Shop Name: ' . $tokenData['seller_name']);
            $this->command->info('Region: ' . $tokenData['seller_base_region']);
            $this->command->info('Access Token Expires: ' . Carbon::createFromTimestamp($tokenData['access_token_expire_in'])->format('Y-m-d H:i:s'));
            $this->command->info('Refresh Token Expires: ' . Carbon::createFromTimestamp($tokenData['refresh_token_expire_in'])->format('Y-m-d H:i:s'));
            $this->command->info('Status: active');

            $this->command->info('🎉 Tích hợp TikTok Shop đã được kích hoạt thành công!');
            $this->command->info('Bây giờ bạn có thể:');
            $this->command->info('1. Vào admin panel để xem trạng thái');
            $this->command->info('2. Test kết nối API');
            $this->command->info('3. Lấy thông tin sản phẩm và đơn hàng');
        } catch (\Exception $e) {
            $this->command->error('❌ Lỗi khi cập nhật: ' . $e->getMessage());
        }

        $this->command->info('=== Hoàn thành ===');
    }
}
