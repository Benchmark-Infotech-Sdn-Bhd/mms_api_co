<p>Hello {{ $params['name'] }},</p>
<p>This Email is to notify that,</p>
@isset($params['message']['fomemaRenewal']['mail_message'])
<p><b>Fomema Renewal</b></p>
<p>{{ $params['message']['fomemaRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['passportRenewal']['mail_message'])
<p><b>passport Renewal</b></p>
<p>{{ $params['message']['passportRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['plksRenewal']['mail_message'])
<p><b>Plks Renewal</b></p>
<p>{{ $params['message']['plksRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['callingVisaRenewal']['mail_message'])
<p><b>CallingVisa Renewal</b></p>
<p>{{ $params['message']['callingVisaRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['specialPassRenewal']['mail_message'])
<p><b>SpecialPass Renewal</b></p>
<p>{{ $params['message']['specialPassRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['insuranceRenewal']['mail_message'])
<p><b>Insurance Renewal</b></p>
<p>{{ $params['message']['insuranceRenewal']['mail_message'] }}</p>
@endisset

<p>To view the details, follow the link below.</p>

<p><a href="https://hcm.benchmarkit.com.my/#/sign-in">https://hcm.benchmarkit.com.my</a></p>

</p>This message was generated automatically. Please do not reply to this email.</p>

<p>Thank you.</p>

<p>Regards,</p>

</p>MMS</p>