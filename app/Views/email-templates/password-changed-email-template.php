<p>Dear <?= $mail_data['user']->name ?></p>
<br>
<p>
   Your password on CI-News has been succesfully changed. Here are your new login credentials:
    <br><br>
    <b>Login ID:</b> <?= $mail_data['user']->username?> or <?= $mail_data['user']->email?>
    <br><br>
    <b>Password:</b> <?= $mail_data['new_password']?>
</p>
<br><br>
Please, keep your credentials safe. You should naever share them with anybody.
<p>
    CI-News will not be liable for any misuse of your credentials.
</p>
<br>
------------------------------------------------------------------
<p>
    This email was automatically sent by CI-News system. Do not reply.
</p>