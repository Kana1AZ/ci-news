<?php
$userName = esc($mail_data['user']['name']);
$postTitle = esc($mail_data['postTitle']);
$expiryDate = esc($mail_data['expiryDate']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiry Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #dddddd;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            text-align: center;
            border-bottom: 1px solid #dddddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .email-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #555555;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Expiry Notification</h1>
        </div>
        <p>Hello <?= $userName ?>,</p>
        <p>Your Guarantee titled "<strong><?= $postTitle ?></strong>" is expiring soon on <?= $expiryDate ?>. Please review it and take necessary action if required.</p>
        <p>Thank you for your attention.</p>
        <p>Best regards,<br>Your Website Team</p>
    </div>
</body>
</html>