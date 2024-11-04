<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/login.html");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require "include/auth.inc.php";
    
    try {
        $stmt = $mysqli->prepare("
            SELECT users.email, users.password, users.name, users.surname, groups_has_services.groups_id 
            FROM users
            LEFT JOIN users_has_groups
            ON users_has_groups.users_email = users.email
            LEFT JOIN groups_has_services
            ON groups_has_services.groups_id = users_has_groups.groups_id
            LEFT JOIN services
            ON services.id = groups_has_services.services_id
            WHERE users.email = ?"
        );

        if ($stmt === false) {
            throw new Exception($mysqli->error);
        }

        $stmt->bind_param("s", $_POST['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if ($data) {
            // Salva informazioni dell'utente nella sessione
            $_SESSION['user'] = [
                'email' => $data['email'],
                'password' => $data['password'], 
                'name' => $data['name'],
                'surname' => $data['surname'],
                'phone' => $data['phone'],
                'groups' => $data['groups_id']
            ];

            if ($data['groups_id'] == '1') {
                header('location: /MotorShop/dashboard.php');
                exit();
            } else {
                header("location: /MotorShop/index.php");
                exit();
            }
        } else {
            echo "Nessun utente trovato con questa email.";
        }
    } catch (Exception $e) {
        // Gestione dell'eccezione
        echo "Errore: " . $e->getMessage();
    }
}

$main->setContent("dynamic", $body->get());
$main->close();

?>