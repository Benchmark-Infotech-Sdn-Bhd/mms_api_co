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
    'CHECKLIST_PENDING' => env('CHECKLIST PENDING', 2),
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

    'WORKER_MODULE_TYPE' => ['Direct Recruitment', 'Total Management', 'e-Contract'],

    'CRM_MODULE_TYPE' => ['Direct Recruitment', 'e-Contract', 'Total Management'],

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
    'XERO_TOKEN_URL' => env('XERO_TOKEN_URL', "https://identity.xero.com/connect/token"),
    'XERO_TENANT_ID' => env('XERO_TENANT_ID', "08e3e7d9-5304-4fa6-a337-1f21262b6dca"),
    'XERO_ACCESS_TOKEN' => env('XERO_ACCESS_TOKEN'),
    'XERO_REFRESH_TOKEN' => env('XERO_REFRESH_TOKEN'),


    'ZOHO_URL' => env('ZOHO_URL', "https://www.zohoapis.com/books/v3/"),
    'ZOHO_CLIENT_ID' => env('ZOHO_CLIENT_ID', "1000.F3G2JM08SUXQF67CFPMAN5L0U75J7O"),
    'ZOHO_CLIENT_SECRET' => env('ZOHO_CLIENT_SECRET', "462c552560ed9781ab9788d098f55dece6856fff3b"),
    'ZOHO_TAX_RATES_URL' => env('ZOHO_TAX_RATES_URL', "settings/taxes"),
    'ZOHO_ITEMS_URL' => env('ZOHO_ITEMS_URL', "items"),
    'ZOHO_ACCOUNTS_URL' => env('ZOHO_ACCOUNTS_URL', "chartofaccounts"),
    'ZOHO_CONTACTS_URL' => env('ZOHO_CONTACTS_URL', "contacts"),
    'ZOHO_INVOICES_URL' => env('ZOHO_INVOICES_URL', "invoices"),
    'ZOHO_TOKEN_URL' => env('ZOHO_TOKEN_URL', "https://accounts.zoho.com/oauth/v2/token"),
    'ZOHO_TENANT_ID' => env('ZOHO_TENANT_ID', ""),
    'ZOHO_ACCESS_TOKEN' => env('ZOHO_ACCESS_TOKEN'),
    'ZOHO_REFRESH_TOKEN' => env('ZOHO_REFRESH_TOKEN'),

    'DIRECT_RECRUITMENT_WORKER_STATUS' => ['Pending','Accepted','Not Arrived','Arrived','FOMEMA Fit','Processed', 'Expired'],

    'POST_ARRIVAL_CANCELLED_STATUS' => env('POST_ARRIVAL_CANCELLED_STATUS', 2),
    
    'STANDARD_FEE_NAMES' => ['Processing Fee', 'FOMEMA Female', 'FOMEMA Male', 'PLKS Fee'],
    'STANDARD_FEE_COST' => [100.00, 94.00, 50.00, 120.00],

    'OTHERS_EVENT_TYPE' => ['Repatriated', 'e-Run', 'Deceased'],

    'NOTIFICATION_TYPE' => 'RENEWALNOTIFICATIONS',
    'FOMEMA_NOTIFICATION_TITLE' => 'FOMEMA RENEWAL',
    'FOMEMA_NOTIFICATION_MESSAGE' => 'workers Fomema should be renewed.',
    'PASSPORT_NOTIFICATION_TITLE' => 'PASSPORT RENEWAL',
    'PASSPORT_NOTIFICATION_MESSAGE' => 'workers Passport should be renewed.',
    'PLKS_NOTIFICATION_TITLE' => 'PLKS(WORK PERMIT) RENEWAL',
    'PLKS_NOTIFICATION_MESSAGE' => 'workers PLKS Visa(Work Permit) should be renewed.',
    'CALLING_VISA_NOTIFICATION_TITLE' => 'CALLING VISA RENEWAL',
    'CALLING_VISA_NOTIFICATION_MESSAGE' => 'workers Calling Visa should be renewed.',
    'SPECIAL_PASS_NOTIFICATION_TITLE' => 'SPECIAL PASS RENEWAL',
    'SPECIAL_PASS_NOTIFICATION_MESSAGE' => 'workers Special Pass should be renewed.',
    'INSURANCE_NOTIFICATION_TITLE' => 'INSURANCE RENEWAL',
    'INSURANCE_NOTIFICATION_MESSAGE' => 'workers Insurance should be renewed.',
    'ENTRY_VISA_NOTIFICATION_TITLE' => 'ENTRY VISA RENEWAL',
    'ENTRY_VISA_NOTIFICATION_MESSAGE' => 'workers Entry Visa should be renewed.',

    'EXPIRY_NOTIFICATION_TYPE' => 'EXPIRYNOTIFICATIONS',
    'FOMEMA_EXPIRY_NOTIFICATION_TITLE' => 'FOMEMA EXPIRY',
    'FOMEMA_EXPIRY_NOTIFICATION_MESSAGE' => 'workers Fomema are expired.',
    'PASSPORT_EXPIRY_NOTIFICATION_TITLE' => 'PASSPORT EXPIRY',
    'PASSPORT_EXPIRY_NOTIFICATION_MESSAGE' => 'workers Passport are expired.',
    'PLKS_EXPIRY_NOTIFICATION_TITLE' => 'PLKS(WORK PERMIT) EXPIRY',
    'PLKS_EXPIRY_NOTIFICATION_MESSAGE' => 'workers PLKS Visa(Work Permit) are expired.',
    'CALLING_VISA_EXPIRY_NOTIFICATION_TITLE' => 'CALLING VISA EXPIRY',
    'CALLING_VISA_EXPIRY_NOTIFICATION_MESSAGE' => 'workers Calling Visa are expired.',
    'SPECIAL_PASS_EXPIRY_NOTIFICATION_TITLE' => 'SPECIAL PASS EXPIRY',
    'SPECIAL_PASS_EXPIRY_NOTIFICATION_MESSAGE' => 'workers Special Pass are expired.',
    'INSURANCE_EXPIRY_NOTIFICATION_TITLE' => 'INSURANCE EXPIRY',
    'INSURANCE_EXPIRY_NOTIFICATION_MESSAGE' => 'workers Insurance are expired.',
    'ENTRY_VISA_EXPIRY_NOTIFICATION_TITLE' => 'ENTRY VISA EXPIRY',
    'ENTRY_VISA_EXPIRY_NOTIFICATION_MESSAGE' => 'workers Entry Visa are expired.',

    'DISPATCH_NOTIFICATION_TITLE' => 'DISPATCHES',
    'SERVICE_AGREEMENT_NOTIFICATION_TITLE' => 'SERVICE AGREEMENT',

    'FOMEMA_MAIL_MESSAGE' => 'The FOMEMA of',
    'PASSPORT_MAIL_MESSAGE' => 'The passports of',
    'PLKS_MAIL_MESSAGE' => 'The PLKS(Work Permit) of',
    'CALLING_VISA_MAIL_MESSAGE' => 'The Calling Visas of',
    'SPECIAL_PASS_MAIL_MESSAGE' => 'The Special Passes of',
    'INSURANCE_MAIL_MESSAGE' => 'The Insurance of',
    'ENTRY_VISA_MAIL_MESSAGE' => 'The Entry Visa of',
    'SERVICE_AGREEMENT_MAIL_MESSAGE' => 'Service agreement is to expire on',
    'DISPATCH_MAIL_MESSAGE' => 'Dispatch is Pending',
    'COMMON_RENEWAL_MAIL_MESSAGE' => 'workers are about to expire',

    'SERVICE_AGREEMENT_EXPIRY_MAIL_MESSAGE' => 'Service agreement is to expired on',
    'COMMON_EXPIRY_MAIL_MESSAGE' => 'workers are expired',

    'ACCESS_MODULE_TYPE' => ['Dashboard', 'Maintain Masters', 'Branches', 'CRM', 'Direct Recruitment', 'e-Contract', 'Total Management', 'Employee', 'Access Management', 'Workers', 'Dispatch Management', 'Invoice', 'Reports'],

    'NOT_UTILISED_STATUS_TYPE' => ['Pending', 'Rejected', 'Repatriated', 'Cancelled', 'Expired'],

    'THIRDPARTYLOG_DELETE_DURATION' => 90,
    
    'WORKER_BIODATA_TEMPLATE' => ['import_sheet' => 'IMPORT', 'reference_sheet' => 'REFERENCE'],

    'SUPER_ADMIN_MODULES' => [14,15,4],

    'INVOICE_RESUBMISSION_FAILED_MAIL' => env('INVOICE_RESUBMISSION_FAILED_MAIL', "muralidharan.n@codtesma.com"),

    'COMPANY_ACCOUNT_SYSTEM_TITLE' => ['XERO', 'ZOHO'],

    'CUSTOMER_LOGIN' => 16,

    'SERVICES_MODULES' => [5,6,7],

    'VIEW_PERMISSION' => 1,

    'QUEUE_CONNECTION' => env('QUEUE_CONNECTION', "database"),
    'WORKER_IMPORT_QUEUE' => env('WORKER_IMPORT_QUEUE', "worker_import"),
    'COMMON_WORKER_IMPORT_QUEUE' => env('COMMON_WORKER_IMPORT_QUEUE', "common_worker_import"),
    'ECONTRACT_PAYROLL_IMPORT' => env('ECONTRACT_PAYROLL_IMPORT', "e_contract_payrolls_import"),
    'PAYROLL_IMPORT' => env('PAYROLL_IMPORT', "payrolls_import"),
    'RUNNER_NOTIFICATION_MAIL' => env('RUNNER_NOTIFICATION_MAIL', "runner_notification_mail"),
    'ADMIN_NOTIFICATION_MAIL' => env('ADMIN_NOTIFICATION_MAIL', "admin_notification_mail"),
    'EMPLOYER_NOTIFICATION_MAIL' => env('EMPLOYER_NOTIFICATION_MAIL', "employer_notification_mail"),

    'SUB_DOMAIN_DB_NAME_ONE' => env('DB_DATABASE'),
    'SUB_DOMAIN_DB_NAME_TWO' => env('SUB_DOMAIN_DB_NAME_TWO'),

    'INVOICE_MODULE_ID' => 12,    

    'RENEWAL_NOTIFICATION_TYPE' => ['FOMEMA Renewal' => 1, 'Passport Renewal' => 2, 'PLKS Renewal' => 3, 'Calling Visa Renewal' => 4, 'Special Passes Renewal' => 5, 'Insurance Renewal' => 6, 'Entry Visa Renewal' => 7, 'Service Agreement Renewal' => 8],

    'COMPANY_NOTIFICATION_TYPE' => ['renewal', 'expired'],

    'NOTIFICATION_FREQUENCY' => ['daily', 'weekly', 'monthly'],

    'DIRECT_RECRUITMENT_NOTIFICATION_ID' => [1,2,3,4,5,6,7],
    
    'TOTAL_MANAGEMENT_NOTIFICATION_ID' => [1,2,3,6,8],

    'ECONTRACT_NOTIFICATION_ID' => [1,2,3,6,8],
];
