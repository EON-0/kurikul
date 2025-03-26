<?php
function createPasswordHash($password) {
    return md5($password);
}
function checkPassword($password, $savedPasswordHash) {
    // Hash the input password using createPasswordHash
    $passwordHash = createPasswordHash($password);
    // Compare the hashed password with the saved hash
    return $passwordHash === $savedPasswordHash;
}
?>