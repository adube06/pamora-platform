<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $notification->title }}</title>
</head>
<body style="font-family: sans-serif; color: #1f2933; background: #f5f5f5; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 24px;">
        <h1 style="font-size: 18px; margin: 0 0 12px;">{{ $notification->title }}</h1>
        <p style="font-size: 14px; line-height: 1.5; margin: 0 0 20px;">{{ $notification->body }}</p>
        <a href="{{ config('app.url') }}" style="font-size: 13px; color: #4f46e5;">Open Pamora</a>
    </div>
</body>
</html>
