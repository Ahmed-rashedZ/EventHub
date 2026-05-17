<?php
require 'vendor/autoload.php';
$client = new \GuzzleHttp\Client();
// We need to login to get a token.
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(24);
$token = $user->createToken('auth_token')->plainTextToken;

$res = $client->request('GET', 'http://127.0.0.1:8000/api/profile', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json'
    ]
]);
echo $res->getBody();
