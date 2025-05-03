<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $this->seedDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }

    /**
     * Seed default settings.
     */
    private function seedDefaultSettings(): void
    {
        $settings = [
            'app_name' => 'Smart Waste',
            'contact_email' => 'contact@smartwaste.com',
            'contact_phone' => '',
            'maintenance_mode' => 'false',
            'base_points_multiplier' => '1.0',
            'minimum_points_redemption' => '100',
            'enable_rewards' => 'true',
            'auto_approve_rewards' => 'false',
            'max_points_per_day' => '0',
            'auto_approve_centers' => 'false',
            'center_approval_interval' => '24',
            'max_centers_per_user' => '1',
            'min_app_version' => '1.0.0',
            'force_update' => 'false',
            'app_announcement' => '',
            'email_notifications' => 'true',
            'push_notifications' => 'true',
            'notify_center_registration' => 'true',
            'notify_reward_redemption' => 'true',
            'notify_center_approval' => 'true',
        ];

        $settingsTable = DB::table('settings');

        foreach ($settings as $key => $value) {
            $settingsTable->insert([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }
};
