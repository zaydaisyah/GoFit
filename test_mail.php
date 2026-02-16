<?php
// test_mail.php
require_once 'email_helper.php';

// Enable verbose debug output for this test
function sendTestEmail($to) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION == 'tls' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'GoFit SMTP Test';
        $mail->Body    = 'If you see this, your Gmail SMTP configuration is working! 🎉';

        echo "<h3>Attempting to send test email...</h3><pre>";
        $mail->send();
        echo "</pre><h4>✅ Success! Check your inbox.</h4>";
    } catch (Exception $e) {
        echo "</pre><h4 style='color:red;'>❌ Failed! Error: {$mail->ErrorInfo}</h4>";
        
        if (MAIL_USERNAME == 'your-email@gmail.com') {
            echo "<p style='color:orange;'><b>Note:</b> It looks like you haven't updated <b>mail_config.php</b> yet. Please put your real Gmail and App Password there!</p>";
        }
    }
}

// Replace with your email to test
$test_email = MAIL_USERNAME != 'your-email@gmail.com' ? MAIL_USERNAME : 'your-test-email@example.com';

echo "<h2>GoFit Email Test Script</h2>";
sendTestEmail($test_email);
?>
