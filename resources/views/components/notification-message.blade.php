@props(['message', 'module' => null, 'limit' => null])

{!! \App\Support\NotificationMessageFormatter::format($message, $module, $limit) !!}
