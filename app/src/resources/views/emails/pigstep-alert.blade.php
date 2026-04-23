<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:0; background:#f3f6fb; font-family:Arial, sans-serif; color:#172033;">
    <div style="max-width:640px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
            <div style="background:#0f172a; color:#ffffff; padding:20px 24px;">
                <h1 style="margin:0; font-size:22px;">Pigstep Alert</h1>
            </div>

            <div style="padding:24px;">
                <h2 style="margin:0 0 16px; font-size:20px; color:#172033;">{{ $headline }}</h2>

                @foreach ($lines as $line)
                    <p style="margin:0 0 12px; line-height:1.6; color:#334155;">{{ $line }}</p>
                @endforeach

                @if (!empty($actionText) && !empty($actionUrl))
                    <div style="margin-top:24px;">
                        <a
                            href="{{ $actionUrl }}"
                            style="display:inline-block; background:#2563eb; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700;"
                        >
                            {{ $actionText }}
                        </a>
                    </div>
                @endif
            </div>

            <div style="padding:16px 24px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px;">
                Automated email from Pigstep.
            </div>
        </div>
    </div>
</body>
</html>
