<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Team Invitation</title>
</head>

<body>
    <h1>Hello, {{ $user->name ?? 'User' }}</h1>

    <p>You have been invited to join the team <strong>{{ $team->name }}</strong>.</p>

    <p>Click the link below to accept the invitation:</p>

    <p>
        <a href="{{ url('api/team/invitations/' . $token . '/accept') }}">
            Accept Invitation
        </a>
    </p>

    <p>This invitation will expire on {{ $team->created_at->addDays(7)->toDateString() }}.</p>

    <p>If you didn’t expect this invitation, you can safely ignore this email.</p>
</body>

</html>
