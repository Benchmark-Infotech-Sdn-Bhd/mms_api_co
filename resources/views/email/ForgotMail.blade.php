<h3>Greetings {{ $params['name'] }},</h3>

<p>We have received a request to reset your password for Manpower Management System. To proceed with resetting your password, please click the button below:</p>

<p><a href="{{ $params['url']}}#/password-reset?token={{$params['token']}}&email={{ $params['email'] }}"><b> Reset Password </b></a></p>