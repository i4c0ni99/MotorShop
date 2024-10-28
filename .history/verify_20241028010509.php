<?php
require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php"; 

$main = new Template("skins/motor-html-package/motor/login.html");

// Debug della connessione
if (!isset($conn)) {
    die("Errore: la connessione al database non è stata creata.");
}

if (isset($_GET['email']) && isset($_GET['v_cod'])) {
    $email = $conn->real_escape_string($_GET['email']);
    $v_cod = $conn->real_escape_string($_GET['v_cod']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = '$email' AND verification_id = '$v_cod' AND verified = 0";
        try {
            $result = $conn->query($sql);
            if ($result !== false) {
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $fetch_Email = $row['email'];
                    
                    // Segna l'utente come verificato
                    $update = "UPDATE users SET verified = 1 WHERE email = '$fetch_Email'";
                    if ($conn->query($update)) {
                        echo "<script>alert('Verifica completata con successo!'); window.location.href='/MotorShop/login.php'</script>";
                    } else {
                        echo "<script>alert('Errore nell\'aggiornamento dei dati!'); window.location.href='/MotorShop/login.php'</script>";
                    }
                } else {
                    echo "<script>alert('Nessun risultato trovato!'); window.location.href='/MotorShop/login.php'</script>";
                }
            } else {
                throw new Exception("Errore nella query: " . $conn->error);
            }
        } catch (Exception $e) {
            echo "<script>alert('Si è verificato un errore: " . $e->getMessage() . "'); window.location.href='/MotorShop/login.php'</script>";
        }
    } else {
        echo "<script>alert('L'indirizzo email non è valido!'); window.location.href='/MotorShop/login.php'</script>";
    }
} else {
    echo "<script>alert('Si è verificato un problema, riprova più tardi!'); window.location.href='/MotorShop/login.php'</script>";
}

$main->close();
?>