<?php
include_once '../backend-php/database.php';

$new_password = "123"; // Set new password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT); // Hash it

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'abcnd@gmail.com'");
$stmt->bind_param("s", $hashed_password);
$stmt->execute();
// Esak@123zxvds
echo "Password reset successfully! New hash: " . $hashed_password;?>
