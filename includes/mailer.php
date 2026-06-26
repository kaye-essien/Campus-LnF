<?php
function sendVerificationEmail($to_email, $to_name, $token) {
    $link    = "https://eg361.ceiscy.com/pages/verify.php?token=$token";
    $subject = "Verify your CampusL&F account";
    $message = "Hi $to_name,\n\nClick below to verify your account:\n\n$link\n\n— CampusL&F UMaT";
    $headers = "From: noreply@eg361.ceiscy.com";
    return mail($to_email, $subject, $message, $headers);
}

function sendPasswordResetEmail($to_email, $to_name, $token) {
    $link    = "https://eg361.ceiscy.com/pages/reset.php?token=$token";
    $subject = "Reset your CampusL&F password";
    $message = "Hi $to_name,\n\nReset your password here:\n\n$link\n\n— CampusL&F UMaT";
    $headers = "From: noreply@eg361.ceiscy.com";
    return mail($to_email, $subject, $message, $headers);
}
