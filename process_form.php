<?php
// Configure error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set recipient email
$recipient_email = "sayheysourav@gmail.com"; // Replace with your email

// Function to sanitize form inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to send email
function send_notification_email($data) {
    global $recipient_email;
    
    $subject = "New Contact Form Submission from FotoBoxx Website";
    
    // Create email body
    $message = "New contact form submission details:\n\n";
    $message .= "Full Name: " . $data['full_name'] . "\n";
    $message .= "Company Name: " . $data['company_name'] . "\n";
    $message .= "Phone Number: " . $data['phone'] . "\n";
    $message .= "Email Address: " . $data['email'] . "\n";
    $message .= "Requirements: " . $data['requirements'] . "\n\n";
    $message .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
    
    // Email headers
    $headers = "From: " . $data['email'] . "\r\n";
    $headers .= "Reply-To: " . $data['email'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    return mail($recipient_email, $subject, $message, $headers);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize response array
    $response = array(
        'success' => false,
        'message' => ''
    );
    
    try {
        // Validate required fields
        $required_fields = array('full_name', 'company_name', 'phone', 'email');
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }
        
        // Sanitize and collect form data
        $form_data = array(
            'full_name' => sanitize_input($_POST['full_name']),
            'company_name' => sanitize_input($_POST['company_name']),
            'phone' => sanitize_input($_POST['phone']),
            'email' => sanitize_input($_POST['email']),
            'requirements' => isset($_POST['requirements']) ? sanitize_input($_POST['requirements']) : ''
        );
        
        // Validate email
        if (!is_valid_email($form_data['email'])) {
            throw new Exception("Invalid email address");
        }
        
        // Send email
        if (send_notification_email($form_data)) {
            $response['success'] = true;
            $response['message'] = "Thank you for your message. We will get back to you soon!";
        } else {
            throw new Exception("Failed to send email. Please try again later.");
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>