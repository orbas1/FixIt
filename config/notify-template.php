<?php
return [
    'name' => 'Notify Templates',
    'slug' => 'notify-templates',
    'email-templates' => [
        'add-extra-charge-admin' => [
            'name' => 'Add Extra Charge (Admin)',
            'description' => 'Sent to admin for extra charge added by user.',
            'slug' => 'add-extra-charge-admin',
            'shortcodes' => [
                    ['type' => 'menuitem', 'text' => 'Admin Name', 'action' => '{{admin_name}}'],
                    ['type' => 'menuitem', 'text' => 'Booking Id', 'action' => '{{booking_id}}'],
                    ['type' => 'menuitem', 'text' => 'Service Amount', 'action' => '{{service_amount}}'],
                    ['type' => 'menuitem', 'text' => 'Number of Services Done', 'action' => '{{no_of_service_done}}'],
                    ['type' => 'menuitem', 'text' => 'Payment Method', 'action' => '{{payment_method}}'],
                    ['type' => 'menuitem', 'text' => 'Payment Status', 'action' => '{{payment_status}}'],
                    ['type' => 'menuitem', 'text' => 'Total Amount', 'action' => '{{total_amount}}'],  
            ]  
        ],
        'add-extra-charge-consumer' => [
            'name' => 'Add Extra Charge (User)',
            'description' => 'Sent to consumer for extra charge added by user.',
            'slug' => 'add-extra-charge-consumer',
            'shortcodes' => [
                    ['type' => 'menuitem', 'text' => 'Admin Name', 'action' => '{{admin_name}}'],
                    ['type' => 'menuitem', 'text' => 'Booking Id', 'action' => '{{booking_id}}'],
                    ['type' => 'menuitem', 'text' => 'Service Amount', 'action' => '{{service_amount}}'],
                    ['type' => 'menuitem', 'text' => 'Number of Services Done', 'action' => '{{no_of_service_done}}'],
                    ['type' => 'menuitem', 'text' => 'Payment Method', 'action' => '{{payment_method}}'],
                    ['type' => 'menuitem', 'text' => 'Payment Status', 'action' => '{{payment_status}}'],
                    ['type' => 'menuitem', 'text' => 'Total Amount', 'action' => '{{total_amount}}'],  
            ]
        ],
        'add-extra-charge-provider' => [
            'name' => 'Add Extra Charge (Provider)',
            'description' => 'Sent to provider for extra charge added by user.',
            'slug' => 'add-extra-charge-provider',
            'shortcodes' => [
                    ['type' => 'menuitem', 'text' => 'Admin Name', 'action' => '{{admin_name}}'],
                    ['type' => 'menuitem', 'text' => 'Booking Id', 'action' => '{{booking_id}}'],
                    ['type' => 'menuitem', 'text' => 'Service Amount', 'action' => '{{service_amount}}'],
                    ['type' => 'menuitem', 'text' => 'Number of Services Done', 'action' => '{{no_of_service_done}}'],
                    ['type' => 'menuitem', 'text' => 'Payment Method', 'action' => '{{payment_method}}'],
                    ['type' => 'menuitem', 'text' => 'Payment Status', 'action' => '{{payment_status}}'],
                    ['type' => 'menuitem', 'text' => 'Total Amount', 'action' => '{{total_amount}}'],  
            ]
        ],
        'booking-scheduled-admin' => [
            'name' => 'New Booking Scheduled (Admin)',
            'description' => 'Sent to admin when a consumer schedules a new booking.',
            'slug' => 'booking-scheduled-admin',
         
                'shortcodes' => [
                    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
                    ['type' => 'menuitem', 'text' => 'Consumer Name', 'action' => '{{consumer_name}}'],
                    ['type' => 'menuitem', 'text' => 'Booking Date', 'action' => '{{booking_date}}'],
                    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
                ]
          
        ],
        'booking-reminder-provider' => [
            'name' => 'Booking Reminder (Provider)',
            'description' => 'Sent to provider as a reminder for a booking scheduled today.',
            'slug' => 'booking-reminder-provider',
            'shortcodes' => [
                    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
                    ['type' => 'menuitem', 'text' => 'Consumer Name', 'action' => '{{consumer_name}}'],
                    ['type' => 'menuitem', 'text' => 'Booking Date', 'action' => '{{booking_date}}'],
                    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
                ]
        ],
        'booking-reminder-consumer' => [
            'name' => 'Booking Reminder (User)',
            'description' => 'Sent to consumer as a reminder for their booking scheduled today.',
            'slug' => 'booking-reminder-consumer',
            'shortcodes' => [
                    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
                    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
                    ['type' => 'menuitem', 'text' => 'Booking Date', 'action' => '{{booking_date}}'],
                    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
            ]
        ],
        'assign-booking-serviceman' => [
            'name' => 'Booking Assigned (Serviceman)',
            'description' => 'Sent to servicemen when a booking is assigned to them.',
            'slug' => 'booking-assigned-serviceman',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Serviceman Name', 'action' => '{{serviceman_name}}'],
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
                ['type' => 'menuitem', 'text' => 'Booking Status', 'action' => '{{booking_status}}'],
                ['type' => 'menuitem', 'text' => 'Service Name', 'action' => '{{service_name}}'],
                ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
                ['type' => 'menuitem', 'text' => 'Date and Time', 'action' => '{{date_time}}'],
            ]
        ],
        'booking-created-admin' => [
            'name' => 'Booking Created (Admin)',
            'description' => 'Sent to admin when a new booking is created.',
            'slug' => 'booking-created-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking ID', 'action' => '{{booking_id}}'],
    ['type' => 'menuitem', 'text' => 'Payment Status', 'action' => '{{payment_status}}'],
    ['type' => 'menuitem', 'text' => 'Booking Status', 'action' => '{{booking_status}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'booking-created-provider' => [
            'name' => 'Booking Created (Provider)',
            'description' => 'Sent to provider when a consumer creates a new booking for their services.',
            'slug' => 'booking-created-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Payment Status', 'action' => '{{payment_status}}'],
    ['type' => 'menuitem', 'text' => 'Booking Status', 'action' => '{{booking_status}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'booking-created-consumer' => [
            'name' => 'Booking Created (User)',
            'description' => 'Sent to consumer when they successfully create a booking.',
            'slug' => 'booking-created-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Consumer Name', 'action' => '{{consumer_name}}'],
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Payment Status', 'action' => '{{payment_status}}'],
    ['type' => 'menuitem', 'text' => 'Booking Status', 'action' => '{{booking_status}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'new-provider-registered-user' => [
            'name' => 'New Provider Registered (User)',
            'description' => 'Sent to users when a new provider joins the platform.',
            'slug' => 'new-provider-registered-user',
            'shortcodes' => [
            ['type' => 'menuitem', 'text' => 'User Name', 'action' => '{{user_name}}'],
            ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
            ['type' => 'menuitem', 'text' => 'Your Company Name', 'action' => '{{Your Company Name}}'],
        ]

        ],
        'new-service-request-admin' => [
            'name' => 'New Service Request (Admin)',
            'description' => 'Sent to admin when a new service request is submitted and requires review.',
            'slug' => 'new-service-request-admin',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Service Title', 'action' => '{{service_title}}'],
                ['type' => 'menuitem', 'text' => 'Service Description', 'action' => '{{service_description}}'],
                ['type' => 'menuitem', 'text' => 'Price', 'action' => '{{price}}'],
                ['type' => 'menuitem', 'text' => 'Booking Date', 'action' => '{{booking_date}}'],
            ]
        ],
        'new-service-request-provider' => [
            'name' => 'New Service Request (Provider)',
            'description' => 'Sent to provider when a new service request is created and is available for bidding.',
            'slug' => 'new-service-request-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Service Title', 'action' => '{{service_title}}'],
                ['type' => 'menuitem', 'text' => 'Service Description', 'action' => '{{service_description}}'],
                ['type' => 'menuitem', 'text' => 'Price', 'action' => '{{price}}'],
                ['type' => 'menuitem', 'text' => 'Booking Date', 'action' => '{{booking_date}}'],
            ]
        ],
        'withdrawal-request-admin' => [
            'name' => 'Withdrawal Request (Admin)',
            'description' => 'Sent to admin when a provider submits a withdrawal request.',
            'slug' => 'withdrawal-request-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'User Name', 'action' => '{{user_name}}'],
    ['type' => 'menuitem', 'text' => 'Requested Amount', 'action' => '{{requested_amount}}'],
    ['type' => 'menuitem', 'text' => 'User Message', 'action' => '{{user_message}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'update-bid-provider' => [
            'name' => 'Update Bid (Provider)',
            'description' => 'Sent to provider when a bid is updated for their service request.',
            'slug' => 'update-bid-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Service Title', 'action' => '{{service_request_title}}'],
                ['type' => 'menuitem', 'text' => 'User Name', 'action' => '{{user_name}}'],
                ['type' => 'menuitem', 'text' => 'Service Request ID', 'action' => '{{service_request_id}}'],
                ['type' => 'menuitem', 'text' => 'Updated Bid Amount', 'action' => '{{updated_bid_amount}}'],
                ['type' => 'menuitem', 'text' => 'Bid Status', 'action' => '{{bid_status}}'],
                ['type' => 'menuitem', 'text' => 'Your Company Name', 'action' => '{{Your Company Name}}'],
            ]
        ],
        'update-booking-status-consumer' => [
            'name' => 'Update Booking Status (User)',
            'description' => 'Sent to consumer when their booking status is updated.',
            'slug' => 'update-booking-status-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Consumer Name', 'action' => '{{consumer_name}}'],
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Booking Status', 'action' => '{{booking_status}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'serivce-proof-admin' => [
            'name' => 'Proof Mail (Admin)',
            'description' => 'Sent to admin when proof related to a booking is submitted.',
            'slug' => 'serivce-proof-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'service-proof-provider' => [
            'name' => 'Proof Mail (Provider)',
            'description' => 'Sent to provider when proof related to their booking is submitted.',
            'slug' => 'service-proof-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'update-service-proof-admin' => [
            'name' => 'Update Service Proof (Admin)',
            'description' => 'Sent to admin when service proof related to a booking is updated.',
            'slug' => 'update-service-proof-admin',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
                ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
            ]
        ],
        'update-service-proof-provider' => [
            'name' => 'Update Service Proof (Provider)',
            'description' => 'Sent to provider when they update the service proof for their booking.',
            'slug' => 'update-service-proof-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
                ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
            ] 
        ],
        'update-withdraw-request-user' => [
            'name' => 'Update Withdraw Request (User)',
            'description' => 'Sent to provider when their withdrawal request is updated.',
            'slug' => 'update-withdraw-request-user',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'User Name', 'action' => '{{user_name}}'],
    ['type' => 'menuitem', 'text' => 'Withdraw Amount', 'action' => '{{withdraw_amount}}'],
    ['type' => 'menuitem', 'text' => 'Withdraw Status', 'action' => '{{withdraw_status}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ]
    ],
    'sms-templates' => [
        'add-extra-charge-admin' => [
            'name' => 'Add Extra Charge (Admin)',
            'description' => 'Sent to admin for extra charge added by user.',
            'slug' => 'add-extra-charge-admin',
            'shortcodes' => [
                [
                    'type' => 'menuitem', 
                    'text' => 'Booking Number', 
                    'action' => '{{booking_number}}',
                    'description' => 'The unique identifier for the booking.'
                ],
                [
                    'type' => 'menuitem', 
                    'text' => 'Total', 
                    'action' => '{{total}}',
                    'description' => 'The total amount for the booking.'
                ],
                [
                    'type' => 'menuitem', 
                    'text' => 'Per Service Amount', 
                    'action' => '{{per_service_amount}}',
                    'description' => 'The amount charged per service in the booking.'
                ],
                [
                    'type' => 'menuitem', 
                    'text' => 'Company Name', 
                    'action' => '{{company_name}}',
                    'description' => 'The name of the company associated with the booking.'
                ],
]
  
        ],
        'add-extra-charge-consumer' => [
            'name' => 'Add Extra Charge (User)',
            'description' => 'Sent to consumer for extra charge added by user.',
            'slug' => 'add-extra-charge-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Total', 'action' => '{{total}}'],
    ['type' => 'menuitem', 'text' => 'Per Service Amount', 'action' => '{{per_service_amount}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'add-extra-charge-provider' => [
            'name' => 'Add Extra Charge (Provider)',
            'description' => 'Sent to provider for extra charge added by user.',
            'slug' => 'add-extra-charge-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Total', 'action' => '{{total}}'],
    ['type' => 'menuitem', 'text' => 'Per Service Amount', 'action' => '{{per_service_amount}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'booking-scheduled-admin' => [
            'name' => 'New Booking Scheduled (Admin)',
            'description' => 'Sent to admin when a consumer schedules a new booking.',
            'slug' => 'booking-scheduled-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
            
        ],
        'booking-reminder-provider' => [
            'name' => 'Booking Reminder (Provider)',
            'description' => 'Sent to provider as a reminder for a booking scheduled today.',
            'slug' => 'booking-reminder-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
            
        ],
        'booking-reminder-consumer' => [
            'name' => 'Booking Reminder (User)',
            'description' => 'Sent to consumer as a reminder for their booking scheduled today.',
            'slug' => 'booking-reminder-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
            
        ],
        'new-bid-notification-consumer' => [
            'name' => 'New Bid Notification (User)',
            'description' => 'Sent to consumer when a provider submits a new bid on their service request.',
            'slug' => 'new-bid-notification-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Service Request Title', 'action' => '{{service_request_title}}'],
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
]

        ],
        'booking-created-admin' => [
            'name' => 'Booking Created (Admin)',
            'description' => 'Sent to admin when a new booking is created.',
            'slug' => 'booking-created-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
        ],
        'booking-created-provider' => [
            'name' => 'Booking Created (Provider)',
            'description' => 'Sent to provider when a consumer creates a new booking for their services.',
            'slug' => 'booking-created-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
        ],
        'booking-created-consumer' => [
            'name' => 'Booking Created (User)',
            'description' => 'Sent to consumer when they successfully create a booking.',
            'slug' => 'booking-created-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
        ],
        'new-provider-registered-admin' => [
            'name' => 'New Provider Registered (Admin)',
            'description' => 'Sent to admin when a new provider registers on the platform.',
            'slug' => 'new-provider-registered-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
]

        ],
        'new-provider-registered-user' => [
            'name' => 'New Provider Registered (User)',
            'description' => 'Sent to users when a new provider joins the platform.',
            'slug' => 'new-provider-registered-user',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
]

        ],
        'new-service-request-provider' => [
            'name' => 'New Service Request (Provider)',
            'description' => 'Sent to provider when a new service request is created and is available for bidding.',
            'slug' => 'new-service-request-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Service Request Title', 'action' => '{{service_request_title}}'],
]

        ],
        'withdrawal-request-admin' => [
            'name' => 'Withdrawal Request (Admin)',
            'description' => 'Sent to admin when a provider submits a withdrawal request.',
            'slug' => 'withdrawal-request-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Amount', 'action' => '{{amount}}'],
]

        ],
        'update-bid-provider' => [
            'name' => 'Update Bid (Provider)',
            'description' => 'Sent to provider when a bid is updated for their service request.',
            'slug' => 'update-bid-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Service Request Title', 'action' => '{{service_request_title}}'],
    ['type' => 'menuitem', 'text' => 'User Name', 'action' => '{{user_name}}'],
    ['type' => 'menuitem', 'text' => 'Status', 'action' => '{{status}}'],
]

        ],
        'update-booking-status-consumer' => [
            'name' => 'Update Booking Status (User)',
            'description' => 'Sent to consumer when their booking status is updated.',
            'slug' => 'update-booking-status-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Status', 'action' => '{{status}}'],
]

        ],
        'service-proof-admin' => [
            'name' => 'Proof Mail (Admin)',
            'description' => 'Sent to admin when proof related to a booking is submitted.',
            'slug' => 'service-proof-admin',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'service-proof-provider' => [
            'name' => 'Proof Mail (Provider)',
            'description' => 'Sent to provider when proof related to their booking is submitted.',
            'slug' => 'service-proof-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'update-service-proof-admin' => [
            'name' => 'Update Service Proof (Admin)',
            'description' => 'Sent to admin when service proof related to a booking is updated.',
            'slug' => 'update-service-proof-admin',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'update-service-proof-provider' => [
            'name' => 'Update Service Proof (Provider)',
            'description' => 'Sent to provider when they update the service proof for their booking.',
            'slug' => 'update-service-proof-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'update-withdraw-request-user' => [
            'name' => 'Update Withdraw Request (User)',
            'description' => 'Sent to provider when their withdrawal request is updated.',
            'slug' => 'update-withdraw-request-user',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Amount', 'action' => '{{amount}}'],
            ]
        ]
    ],
    'push-notification-templates' => [
        'add-extra-charge-admin' => [
            'name' => 'Add Extra Charge (Admin)',
            'description' => 'Sent to admin for extra charge added by user.',
            'slug' => 'add-extra-charge-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Total', 'action' => '{{total}}'],
    ['type' => 'menuitem', 'text' => 'Per Service Amount', 'action' => '{{per_service_amount}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]
  
        ],
        'add-extra-charge-consumer' => [
            'name' => 'Add Extra Charge (User)',
            'description' => 'Sent to consumer for extra charge added by user.',
            'slug' => 'add-extra-charge-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Total', 'action' => '{{total}}'],
    ['type' => 'menuitem', 'text' => 'Per Service Amount', 'action' => '{{per_service_amount}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'add-extra-charge-provider' => [
            'name' => 'Add Extra Charge (Provider)',
            'description' => 'Sent to provider for extra charge added by user.',
            'slug' => 'add-extra-charge-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Total', 'action' => '{{total}}'],
    ['type' => 'menuitem', 'text' => 'Per Service Amount', 'action' => '{{per_service_amount}}'],
    ['type' => 'menuitem', 'text' => 'Company Name', 'action' => '{{company_name}}'],
]

        ],
        'booking-scheduled-admin' => [
            'name' => 'New Booking Scheduled (Admin)',
            'description' => 'Sent to admin when a consumer schedules a new booking.',
            'slug' => 'booking-scheduled-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
            
        ],
        'booking-reminder-provider' => [
            'name' => 'Booking Reminder (Provider)',
            'description' => 'Sent to provider as a reminder for a booking scheduled today.',
            'slug' => 'booking-reminder-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
            
        ],
        'booking-reminder-consumer' => [
            'name' => 'Booking Reminder (User)',
            'description' => 'Sent to consumer as a reminder for their booking scheduled today.',
            'slug' => 'booking-reminder-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
            
        ],
        'new-bid-notification-consumer' => [
            'name' => 'New Bid Notification (User)',
            'description' => 'Sent to consumer when a provider submits a new bid on their service request.',
            'slug' => 'new-bid-notification-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Service Request Title', 'action' => '{{service_request_title}}'],
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
]

        ],
        'booking-created-admin' => [
            'name' => 'Booking Created (Admin)',
            'description' => 'Sent to admin when a new booking is created.',
            'slug' => 'booking-created-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
        ],
        'booking-created-provider' => [
            'name' => 'Booking Created (Provider)',
            'description' => 'Sent to provider when a consumer creates a new booking for their services.',
            'slug' => 'booking-created-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
        ],
        'booking-created-consumer' => [
            'name' => 'Booking Created (User)',
            'description' => 'Sent to consumer when they successfully create a booking.',
            'slug' => 'booking-created-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
]
        ],
        'new-provider-registered-admin' => [
            'name' => 'New Provider Registered (Admin)',
            'description' => 'Sent to admin when a new provider registers on the platform.',
            'slug' => 'new-provider-registered-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
]

        ],
        'new-provider-registered-user' => [
            'name' => 'New Provider Registered (User)',
            'description' => 'Sent to users when a new provider joins the platform.',
            'slug' => 'new-provider-registered-user',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Provider Name', 'action' => '{{provider_name}}'],
]

        ],
        'new-service-request-provider' => [
            'name' => 'New Service Request (Provider)',
            'description' => 'Sent to provider when a new service request is created and is available for bidding.',
            'slug' => 'new-service-request-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Service Request Title', 'action' => '{{service_request_title}}'],
]

        ],
        'withdrawal-request-admin' => [
            'name' => 'Withdrawal Request (Admin)',
            'description' => 'Sent to admin when a provider submits a withdrawal request.',
            'slug' => 'withdrawal-request-admin',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Amount', 'action' => '{{amount}}'],
]

        ],
        'update-bid-provider' => [
            'name' => 'Update Bid (Provider)',
            'description' => 'Sent to provider when a bid is updated for their service request.',
            'slug' => 'update-bid-provider',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Service Request Title', 'action' => '{{service_request_title}}'],
    ['type' => 'menuitem', 'text' => 'User Name', 'action' => '{{user_name}}'],
    ['type' => 'menuitem', 'text' => 'Status', 'action' => '{{status}}'],
]

        ],
        'update-booking-status-consumer' => [
            'name' => 'Update Booking Status (User)',
            'description' => 'Sent to consumer when their booking status is updated.',
            'slug' => 'update-booking-status-consumer',
            'shortcodes' => [
    ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
    ['type' => 'menuitem', 'text' => 'Status', 'action' => '{{status}}'],
]

        ],
        'service-proof-admin' => [
            'name' => 'Proof Mail (Admin)',
            'description' => 'Sent to admin when proof related to a booking is submitted.',
            'slug' => 'service-proof-admin',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'service-proof-provider' => [
            'name' => 'Proof Mail (Provider)',
            'description' => 'Sent to provider when proof related to their booking is submitted.',
            'slug' => 'service-proof-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'update-service-proof-admin' => [
            'name' => 'Update Service Proof (Admin)',
            'description' => 'Sent to admin when service proof related to a booking is updated.',
            'slug' => 'update-service-proof-admin',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'update-service-proof-provider' => [
            'name' => 'Update Service Proof (Provider)',
            'description' => 'Sent to provider when they update the service proof for their booking.',
            'slug' => 'update-service-proof-provider',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Booking Number', 'action' => '{{booking_number}}'],
            ]
        ],
        'update-withdraw-request-user' => [
            'name' => 'Update Withdraw Request (User)',
            'description' => 'Sent to provider when their withdrawal request is updated.',
            'slug' => 'update-withdraw-request-user',
            'shortcodes' => [
                ['type' => 'menuitem', 'text' => 'Amount', 'action' => '{{amount}}'],
            ]
        ]
        
    ],
];