
<p>Hello {{ $params['name'] }},</p>

<p>This Email is to notify that,</p>
@isset($params['message'])
<p><b>Dispatch Status</b></p>
<p>{!! $params['message'] !!} </p>
@endisset

<p>To view the details, follow the link below.</p>

<p><a href="https://hcm.benchmarkit.com.my/#/sign-in">https://hcm.benchmarkit.com.my</a></p>

</p>This message was generated automatically. Please do not reply to this email.</p>

<p>Thank you.</p>

<p>Regards,</p>

</p>MMS</p>