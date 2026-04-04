<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
if (!$user) {
    $user = \App\Models\User::create(['name'=>'test','email'=>'test2@demo.com','password'=>bcrypt('pass'),'role'=>'Sponsor']);
}

$request = \Illuminate\Http\Request::create('/api/profile', 'PUT', [
    'name'=>'test3',
    'email'=>'test3@demo.com',
    'bio'=>'new bio test',
    'contacts'=>'[{"type":"phone","value":"1234567890"}]'
]);
$request->setUserResolver(function() use($user) { return $user; });

$controller = new \App\Http\Controllers\AuthController();
$response = $controller->updateProfile($request);

echo $response->getContent();
