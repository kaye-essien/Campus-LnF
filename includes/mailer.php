<?php
function sendVerificationEmail($to_email, $to_name, $token) {
    $link    = "https://campus-lnf.page.gd/pages/verify.php?token=$token";
    $subject = "Verify your CampusL&F account";
    $message = "Hi $to_name,\n\nClick below to verify:\n\n$link\n\n— CampusL&F UMaT";
    $headers = "From: noreply@campus-lnf.page.gd";
    return mail($to_email, $subject, $message, $headers);
}
function sendPasswordResetEmail($to_email, $to_name, $token) {
    $link    = "https://campus-lnf.page.gd/pages/reset.php?token=$token";
    $subject = "Reset your CampusL&F password";
    $message = "Hi $to_name,\n\nReset here:\n\n$link\n\n— CampusL&F UMaT";
    $headers = "From: noreply@campus-lnf.page.gd";
    return mail($to_email, $subject, $message, $headers);
}
