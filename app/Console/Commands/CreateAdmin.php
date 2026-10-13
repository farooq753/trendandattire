<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'store:create-admin {email} {--name=Store Admin} {--password=}';

    protected $description = 'Create or update a super admin. The password is printed once and is not stored in the repository.';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $password = (string) ($this->option('password') ?: Str::password(16));

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) $this->option('name'),
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $roleId = Role::query()->where('slug', 'super-admin')->value('id');

        if (! $roleId) {
            $this->error('Run database seeders first so the super-admin role exists.');

            return self::FAILURE;
        }

        $user->roles()->syncWithoutDetaching([$roleId]);
        $this->info('Super admin ready: '.$user->email);
        $this->line('Password: '.$password);

        return self::SUCCESS;
    }
}
