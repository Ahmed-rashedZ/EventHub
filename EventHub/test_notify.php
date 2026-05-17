<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$event = App\Models\Event::where('event_type', 'تقنية')->latest()->first();
$users = \App\Models\User::whereNotNull('interests')->get();

foreach ($users as $user) {
    $interests = is_array($user->interests) ? $user->interests : [];
    if (in_array('تقنية', $interests)) {
        $user->notify(new \App\Notifications\SystemNotification(
            'فعالية جديدة تهمك! 🎉 (تجربة)',
            "تم نشر فعالية جديدة \"{$event->title}\" من نوع {$event->event_type}.",
            'event',
            '🎉',
            '/user/events/' . $event->id,
            $event->id
        ));
        echo "Sent to {$user->id}\n";
    }
}
