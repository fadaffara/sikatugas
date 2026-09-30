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
    $mail->addAddress($email, 'User');        
    $mail->isHTML(true);      
    $email_template = 
    "
    <h2> Halo User Baru Sikatugas</h2>
    
    <p>Untuk verifikasi akun Anda harap klik link berikut ini : 
    <a href=\"http://127.0.0.1/sikatugas/user/account-activation.php?token=" . urlencode($verification_token_hash) . "\">
        Verifikasi Akun
    </a>
    </p>
    <br>
    <br>
    <p> Terima Kasih, <br>
        Tim Sikatugas
    </p>
    ";                          
    $mail->Subject = 'Verifikasi Akun';
    $mail->Body    = $email_template;
    $mail->send();
} catch (Exception $e) {
    echo "Pesan tidak dapat dikirim. {$mail->ErrorInfo}";
}