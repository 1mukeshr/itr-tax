<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

echo 'driver='.config('database.default').PHP_EOL;
echo 'users='.User::count().PHP_EOL;

$user = User::where('email', 'user@itr-tax.in')->first();
echo 'user='.($user?->name ?? 'missing').PHP_EOL;
echo 'pwd='.($user && Hash::check('password', $user->password) ? 'ok' : 'bad').PHP_EOL;
