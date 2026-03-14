<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMTP Test</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2>SMTP Test Successful</h2>

    <p>This email confirms that the SMTP account is working.</p>

    <p><strong>Name:</strong> {{ $smtp->name }}</p>
    <p><strong>Host:</strong> {{ $smtp->host }}</p>
    <p><strong>Port:</strong> {{ $smtp->port }}</p>
    <p><strong>From:</strong> {{ $smtp->from_name }} &lt;{{ $smtp->from_email }}&gt;</p>

    <p>You can now use this SMTP account for future campaign sending and rotation.</p>
</body>
</html>