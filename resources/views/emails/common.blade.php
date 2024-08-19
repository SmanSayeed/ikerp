<!-- resources/views/emails/common.blade.php -->

@if ($type === 'verification')
    <h1>Verification Email</h1>
    <p>Hi {{ $data['name'] }},</p>
    <p>Please verify your email address by clicking the link below:</p>
    <a href="{{ $data['verification_url'] }}">Verify Email</a>

@elseif ($type === 'password_reset')
    <h1>Password Reset</h1>
    <p>Hi {{ $data['name'] }},</p>
    <p>Click the link below to reset your password:</p>
    <a href="{{ $data['reset_url'] }}">Reset Password</a>

@elseif ($type === 'file')
    <h1>File Attached</h1>
    <p>Hi {{ $data['name'] }},</p>
    <p>Find the attached file:</p>
    <!-- Handle attachment in the controller -->

@elseif ($type === 'warning')
    <h1>Warning</h1>
    <p>Hi {{ $data['name'] }},</p>
    <p>{{ $data['warning_message'] }}</p>

@elseif ($type === 'general')
    <h1>General Email</h1>
    <p>Hi {{ $data['name'] }},</p>
    <p>{{ $data['message'] }}</p>

@else
    <h1>Message Email</h1>
    <p>Hi {{ $data['name'] }},</p>
    <p>{{ $data['message'] }}</p>
@endif
