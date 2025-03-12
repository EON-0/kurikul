<?php
function createPasswordHash($password) { //za generiranje novih lozinki
    $salt = random_bytes(16); // Generira 16 bajtova nasumičnog salta
    $hash = hash_pbkdf2("sha256", $password, $salt, 100000, 20, true); // Generira hash
    return base64_encode($salt . $hash); // Spaja salt i hash te ih enkodira u Base64
}
function checkPassword($password, $savedPasswordHash) {
    // Dekodiranje spremljenog hasha iz Base64 formata
    $hashBytes = base64_decode($savedPasswordHash);

    // Ekstrahiranje salta (prvih 16 bajtova)
    $salt = substr($hashBytes, 0, 16);

    // Ekstrahiranje spremljenog hash-a (sljedećih 20 bajtova)
    $storedHash = substr($hashBytes, 16, 20);

    // Generiranje hash-a za unesenu lozinku koristeći PBKDF2
    $generatedHash = hash_pbkdf2("sha256", $password, $salt, 100000, 20, true);

    // Usporedba spremljenog hash-a s generiranim hash-om
    if (hash_equals($storedHash, $generatedHash)) {
        return true; // Lozinka je ispravna
    } else {
        return false; // Lozinka je pogrešna
    }
}


?>