<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Messages
    |--------------------------------------------------------------------------
    */

    // Common messages
    'registration_completed' => 'Registration completed.',
    'registration_failed' => 'Registration failed.',
    'registration_error' => 'Registration failed.',
    'save_completed' => 'Save completed.',
    'edit_failed' => 'Edit failed.',
    'data_fetch_failed' => 'Failed to fetch data.',
    'data_fetch_succeeded' => 'Data fetched successfully.',
    'upload_completed' => 'Upload completed.',
    'search_failed' => 'Search failed.',
    'file_not_selected' => 'No file selected.',
    'file_not_found' => 'File not found.',
    'session_expired' => 'Session has expired.',

    // Device related
    'device_not_found' => 'The specified device was not found.',
    'device_registration_failed' => 'Device registration failed.',
    'device_multi_registration_failed' => 'Multi-device registration failed.',
    'image_analysis_failed' => 'Image analysis failed.',
    'image_save_failed' => 'Image save failed.',
    'image_delete_failed' => 'Image deletion failed.',

    // Rental related
    'rental_registration_failed' => 'Rental information registration failed.',
    'rental_process_failed' => 'Rental process failed.',
    'rental_history_not_found' => 'The specified rental history was not found.',
    'csv_parse_failed' => 'CSV parsing failed.',
    'device_not_exists' => 'Non-existent device found. Device ID: :device_id',
    'device_already_sold' => 'Already sold device included. Device ID: :device_id',
    'device_currently_rented' => 'Currently rented device included. Device ID: :device_id',
    'device_defective' => 'Defective device included. Device ID: :device_id',

    // Sales related
    'sales_process_failed' => 'Sales process failed.',
    'sales_csv_failed' => 'Sales CSV processing failed.',
    'sales_history_not_found' => 'The specified sales history was not found.',
    'cart_device_registration_failed' => 'Cart device registration failed.',
    'device_duplicate' => 'Duplicate found. Device ID: :device_id',

    // Client related
    'client_not_found' => 'The specified client was not found.',
    'client_registration_failed' => 'Client registration failed.',
    'client_data_fetch_failed' => 'Failed to fetch client data.',
    'crm_sync_failed' => 'Failed to fetch or update customer data.',

    // User related
    'user_registered' => 'User registered successfully.',
    'user_registration_failed' => 'User registration failed.',
    'user_updated' => 'User information updated successfully.',
    'user_update_failed' => 'Failed to update user information.',
    'user_registration_error' => 'User registration error.',
    'email_not_available' => 'The entered email address cannot be used.',
    'email_verification_sent' => 'Verification email sent.',
    'user_not_found' => 'User does not exist.',
    'email_changed' => 'Email address changed successfully.',
    'email_change_expired' => 'Email change link has expired.',
    'email_verification_error' => 'Email change verification error.',
    'email_verification_failed' => 'Verification failed.',

    // Mail related
    'return_deadline_subject' => '[DeviceManager] Return Deadline Notice',
    'return_deadline_mail_success' => 'Return deadline notification email sent successfully.',
    'return_deadline_mail_failed' => 'Return deadline notification email failed.',
    'return_deadline_mail_exception' => 'Return deadline notification email exception.',

    // Personnel related
    'personnel_registration_failed' => 'Registration failed.',
    'personnel_data_fetch_failed' => 'Failed to fetch data.',

    // Log messages
    'log_search_failed' => 'Search failed.',
    'log_edit_failed' => 'Edit failed.',
];
