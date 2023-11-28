<p>[Subject]:{{ $params['mail_subject'] }}</p>
<p>Hello {{ $params['name'] }},</p>
<p>This Email is to notify that,</p>
@isset($params['message']['fomemaRenewal']['mail_message'])
<p><b>Fomema Expiry:</b></p>
<p>{{ $params['message']['fomemaRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['passportRenewal']['mail_message'])
<p><b>Passport Expiry:</b></p>
<p>{{ $params['message']['passportRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['plksRenewal']['mail_message'])
<p><b>PLKS:</b></p>
<p>{{ $params['message']['plksRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['callingVisaRenewal']['mail_message'])
<p><b>Calling Visa:</b></p>
<p>{{ $params['message']['callingVisaRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['specialPassRenewal']['mail_message'])
<p><b>Special Pass:</b></p>
<p>{{ $params['message']['specialPassRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['insuranceRenewal']['mail_message'])
<p><b>Insurance:</b></p>
<p>{{ $params['message']['insuranceRenewal']['mail_message'] }}</p>
@endisset
@isset($params['message']['entryVisaRenewal']['mail_message'])
<p><b>Entry Visa:</b></p>
<p>{{ $params['message']['entryVisaRenewal']['mail_message'] }}</p>
@endisset
@if(isset($params['message']['serviceAgreement']['mail_message']) && !empty($params['message']['serviceAgreement']['mail_message']))
<p><b>Service Agreement:</b></p>
<p>{!! $params['message']['serviceAgreement']['mail_message'] !!}</p>
@endif

<p>To view the details, follow the link below.</p>

<p><a href="https://hcm.benchmarkit.com.my/#/sign-in">https://hcm.benchmarkit.com.my</a></p>
</p>This message was generated automatically. Please do not reply to this email.</p>

<p>Thank you.</p>

<p>Regards,</p>
</p>MMS</p>