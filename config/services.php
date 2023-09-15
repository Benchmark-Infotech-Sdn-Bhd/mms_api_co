<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'paginate_row' => env('PAGINATE_ROW', 10),
    'app_url' => env('APP_URL', ''),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
    'mail_from_name' => env('MAIL_FROM_NAME', ''),

    'APPLICATION_INTERVIEW_ITEM_NAME' => 'Interview details',
    'APPLICATION_INTERVIEW_KSM_REFERENCE_STATUS' => ['Approved'],
    'APPLICATION_LEVY_KSM_REFERENCE_STATUS' => ['Paid'],

    'APPLICATION_SUMMARY_ACTION' => ([1 => 'Proposal Submission', 2 => 'Checklist Document', 3 => 'FWCMS Details', 4 => 'Interview Details', 5 => 'Levy Payment', 6 => 'Approval Letter']),

    'FRONTEND_URL' => env('FRONTEND_URL', 'https://hcm.benchmarkit.com.my/'),

    'PENDING_PROPOSAL' => env('PENDING_PROPOSAL', 1),
    'PROPOSAL_SUBMITTED' => env('PROPOSAL_SUBMITTED', 2),
    'CHECKLIST_COMPLETED' => env('CHECKLIST_COMPLETED', 3),
    'FWCMS_COMPLETED' => env('FWCMS_COMPLETED', 4),
    'INTERVIEW_COMPLETED' => env('INTERVIEW_COMPLETED', 5),
    'LEVY_COMPLETED' => env('LEVY_COMPLETED', 6),
    'APPROVAL_COMPLETED' => env('APPROVAL_COMPLETED', 7),
    'FWCMS_REJECTED' => env('FWCMS_REJECTED', 8),

    'ROLE_TYPE_ADMIN' => 'Admin',
    'CALLING_VISA_WORKER_COUNT' => env('CALLING_VISA_WORKER_COUNT', 30),

    'TOTAL_MANAGEMENT_SERVICE' => env('TOTAL_MANAGEMENT_SERVICE', 3),

    'EMPLOYEE_ROLE_TYPE_SUPERVISOR' => 'Supervisor',

    'TOTAL_MANAGEMENT_WORKER_STATUS' => ['Assigned', 'Counselling'],

    'EMPLOYEE_ROLE_TYPE_SUPERVISOR' => 'Supervisor',

    'ECONTRACT_WORKER_STATUS' => ['Assigned', 'Counselling'],

    'WORKER_MODULE_TYPE' => ['Direct Recruitment', 'Total Management', 'E-Contract'],

    'FOMNEXTS_DETAILS' => ['company_name' => 'FOMNEXTS', 'roc_number' => '123456789', 'location' => 'A-10-12 & A-10-07, Menara A, Kompleks Atria, Damansara Jaya 47400, Petaling Jaya', 'sector' => 'Manufacturing'],

    'OTHER_EXPENSES_TITLE' => ([1 => 'Application - Levy Payment Amount', 2 => 'Onboarding - Attestation Costing', 3 => 'Onboarding - Calling Visa - I.G Insurance', 4 => 'Onboarding - Calling Visa - Immigration Fee Paid', 5 => 'Onboarding - Calling Visa - Hospitalisation', 6 => 'Onboarding - FOMEMA Total Charge + Convenient Fee (RM)', 7 => 'Onboarding - Repatriation Expenses (RM)']),
    
    'paginate_worker_row' => env('PAGINATE_WORKER_ROW', 30),

    'XERO_URL' => env('XERO_URL', "https://api.xero.com/api.xro/2.0/"),
    'XERO_CLIENT_ID' => env('XERO_CLIENT_ID', "05384CFA1A624054B05E572976EB3748"),
    'XERO_CLIENT_SECRET' => env('XERO_CLIENT_SECRET', "8CB02fkMqeGwOf6HGG1HJ3cB-wMhEPpzYd2-fuMwW72GuUBJ"),
    'XERO_TAX_RATES_URL' => env('XERO_TAX_RATES_URL', "TaxRates"),
    'XERO_ITEMS_URL' => env('XERO_ITEMS_URL', "Items"),
    'XERO_ACCOUNTS_URL' => env('XERO_ACCOUNTS_URL', "Accounts"),
    'XERO_CONTACTS_URL' => env('XERO_CONTACTS_URL', "Contacts"),
    'XERO_INVOICES_URL' => env('XERO_INVOICES_URL', "Invoices"),
    'XERO_TOKEN_URL' => env('XERO_TOKEN_URL', "token"),
    'XERO_TENANT_ID' => env('XERO_TENANT_ID', "08e3e7d9-5304-4fa6-a337-1f21262b6dca"),
    'XERO_ACCESS_TOKEN' => env('XERO_ACCESS_TOKEN'),
    'XERO_REFRESH_TOKEN' => env('XERO_REFRESH_TOKEN'),
    
];
