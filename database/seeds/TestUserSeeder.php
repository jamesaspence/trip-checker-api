<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email = 'testUser@packit.com';

        $exists = User::where('email', '=', $email)
            ->exists();


        if (!$exists) {
            factory(User::class)->create([
                'email' => $email,
                'password' => Hash::make('packit')
            ]);

            $this->command->info('Created test user with email ' . $email . ' and password "packit".');
        }
    }
}
