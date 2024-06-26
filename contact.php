<?php

require "config.php";
require "mailer/EmailSender.php";

function getJsonValue($json_data, $key, $default = '')
{
    return isset($json_data[$key]) ? $json_data[$key] : $default;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $post_data = file_get_contents("php://input");

    $json_data = json_decode($post_data, true);

    if ($json_data === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data.'
        ]);
        exit;
    }

    $project_type = getJsonValue($json_data, 'project_type');
    $name_address = getJsonValue($json_data, 'name_address');
    $phone_number = getJsonValue($json_data, 'phone_number');
    $email_id = getJsonValue($json_data, 'email_id');
    $sanctioned_load = getJsonValue($json_data, 'sanctioned_load');
    $avg_monthly_bill = getJsonValue($json_data, 'avg_monthly_bill');


    $errors = [];


    if (empty($phone_number) || !preg_match('/^\d{10}$/', $phone_number)) {
        $errors[] = 'A valid 10-digit phone number is required.';
    }
    if (empty($email_id) || !filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (!empty($errors)) {
        echo json_encode([
            'status' => false,
            'message' => 'Validation failed.',
            'error' => $errors
        ]);
        exit;
    }

    // Prepare email placeholders
    $placeholders = [
        'project_type' => htmlspecialchars($project_type),
        'name_address' => htmlspecialchars($name_address),
        'phone_number' => htmlspecialchars($phone_number),
        'email_id' => htmlspecialchars($email_id),
        'sanctioned_load' => htmlspecialchars($sanctioned_load),
        'avg_monthly_bill' => htmlspecialchars($avg_monthly_bill)
    ];

    $server_config = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT_TLS,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => SMTP_FROM,
        'from_name' => SMTP_FROM_NAME,
        'to' => $email_id,
        'subject' => 'Solar Project Enquiry',
        'smtp_debug' => 0, // optional if you want to get debug information
    ];

    $body = array(
        "template_file" => dirname(__FILE__). '\template\contact-us.html',
        "placeholders" => $placeholders
    );

    // Create EmailSender instance
    $emailSender = new EmailSender($server_config, $body, true);

    // Send email
    $response = $emailSender->send();
    // Send email
    echo json_encode($response);

}else{
    http_response_code(400);
}