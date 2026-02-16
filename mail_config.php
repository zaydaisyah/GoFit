<?php
// mail_config.php

// GMAIL SMTP CONFIGURATION
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587); // Use 587 for TLS, 465 for SSL
define('MAIL_USERNAME', '2025395581@student.uitm.edu.my'); // YOUR GMAIL EMAIL
define('MAIL_PASSWORD', 'poiuytrewq#12345');   // YOUR GMAIL APP PASSWORD
define('MAIL_ENCRYPTION', 'tls');             // tls or ssl
define('MAIL_FROM_NAME', 'GoFit Club');

/**
 * INSTRUCTIONS TO GET APP PASSWORD:
 * 1. Go to your Google Account (myaccount.google.com)
 * 2. Go to Security
 * 3. Enable 2-Step Verification
 * 4. Search for "App Passwords"
 * 5. Create an app password for "Mail" and "Windows Computer" (or Other)
 * 6. Copy the 16-character code and paste it in MAIL_PASSWORD above.
 */
?>
