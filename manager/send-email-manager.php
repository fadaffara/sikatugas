<?php
session_start(); 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();                                           
    $mail->Host       = 'smtp.gmail.com';                
    $mail->SMTPAuth   = true;                              
    $mail->Username   = 'daffarafiansyah123@gmail.com';                    
    $mail->Password   = 'dxvbkszyrqrpdneg';                               
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           
    $mail->Port       = 587;                                   
    $mail->setFrom('daffarafiansyah123@gmail.com', 'Sikatugas');
    $mail->addAddress($email, 'Manager');        
    $mail->isHTML(true);      
    $email_template = 




    "
    <h2> Halo Manager Sikatugas</h2>
    <p>Kami telah menerima permintaan untuk mereset password akun Anda.
    Untuk mereset password silakan untuk click link berikut ini
    <a href=\"http://127.0.0.1/sikatugas/manager/reset-password-manager.php?reset_token=" . urlencode($token_hash) . "\">
        Reset Password
    </a>
    </p>
    <br>
    <br>
    <p> Jika Anda tidak merasa melakukan permintaan untuk mereset password harap abaikan saja pesan ini</p>
    <strong>Link untuk reset password ini hanya berlaku selama 30 menit sejak pesan ini dikirim, harap untuk digunakan sesegera mungkin</strong>
    <p> Terima Kasih, <br>
        Tim Sikatugas
    </p>
    ";                          
    $mail->Subject = 'Reset Password';
    $mail->Body    = $email_template;
    $mail->send();
} catch (Exception $e) {
    echo "Pesan tidak dapat dikirim. {$mail->ErrorInfo}";
}