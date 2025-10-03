<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create default admin user
        User::create([
            'email' => 'admin@volunteerconnect.com',
            'password' => Hash::make('Admin@123'),
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '0123456789',
            'user_type' => 'Admin',
            'city' => 'Hanoi',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created: admin@volunteerconnect.com / Admin@123');
    }
}