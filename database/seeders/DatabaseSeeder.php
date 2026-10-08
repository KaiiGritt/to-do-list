<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Avery Student',
                'email' => 'avery.student@example.test',
                'password' => 'student-demo-password',
                'role' => 'student',
                'request' => [
                    'requester_name' => 'Avery Student',
                    'requester_email' => 'avery.student@example.test',
                    'item_name' => 'Laboratory notebook',
                    'quantity' => 1,
                    'purpose' => 'Record observations for the biology laboratory.',
                ],
            ],
            [
                'name' => 'Jordan Student',
                'email' => 'jordan.student@example.test',
                'password' => 'student-demo-password',
                'role' => 'student',
                'request' => [
                    'requester_name' => 'Jordan Student',
                    'requester_email' => 'jordan.student@example.test',
                    'item_name' => 'Safety goggles',
                    'quantity' => 1,
                    'purpose' => 'Use during the chemistry laboratory session.',
                ],
            ],
            [
                'name' => 'Morgan Administrator',
                'email' => 'morgan.admin@example.test',
                'password' => 'admin-demo-password',
                'role' => 'administrator',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                ],
            );

            $user->forceFill(['role' => $account['role']])->save();

            if (isset($account['request'])) {
                $user->serviceRequests()->firstOrCreate(
                    ['item_name' => $account['request']['item_name']],
                    $account['request'],
                );
            }
        }
    }
}
