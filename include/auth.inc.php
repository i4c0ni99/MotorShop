<?php

DEFINE('ERROR_SCRIPT_PERMISSION', 100);
DEFINE('ERROR_USER_NOT_LOGGED', 200);
DEFINE('ERROR_OWNERSHIP', 200);

function crypto($pass) {

    return md5(md5($pass));

}

// Verifica se l`email e` presente nel DB
function isOwner($resource, $key = "id") {

    global $mysqli;

    $oid = $mysqli->query("
            SELECT email 
            FROM {$resource} 
            WHERE {$key} = '{$_REQUEST[$key]}'");
    if (!$oid) {
        // Email non trovata
    }

    $data = $oid->fetch_assoc();

    if ($data['owner_email'] != $_SESSION['user']['email']) {

        Header("Location: error.php?code=".ERROR_OWNERSHIP);
        exit;

    }

}

if (isset($_POST['email']) and isset($_POST['password'])) {

    $oid = $mysqli->query("
            SELECT name, surname, email 
            FROM users 
            WHERE email = '".$_POST['email']."'
            AND password = '".crypto($_POST['password'])."'");


    if (!$oid) {
        trigger_error("Generic error, level 21", E_USER_ERROR);
    }

    if ($oid->num_rows > 0) {
        $user = $oid->fetch_assoc();
        $_SESSION['auth'] = true;
        $_SESSION['user'] = $user;

        $oid = $mysqli->query("
                SELECT DISTINCT script FROM user 
                LEFT JOIN users_has_groups
                ON users_has_groups.user_email = users.email
                LEFT JOIN groups_has_services
                ON groups_has_services.groups_id = users_has_groups.groups_id 
                LEFT JOIN services
                ON services.id = groups_has_services.services_id
                WHERE email = '".$_POST['email']."'");

        if (!$oid) {
            trigger_error("Generic error, level 40", E_USER_ERROR);
        }

        do {
            $data = $oid->fetch_assoc();
            if ($data) {
                $scripts[$data['script']] = true;
            }
        } while ($data);

        $_SESSION['user']['script'] = $scripts;

        if (isset($_SESSION['referrer'])) {
            $referrer = $_SESSION['referrer'];
            unset($_SESSION['referrer']);
            Header("Location: {$referrer}");
            exit;
        }

    } else {
        Header("Location: login.php");
        exit;
    }

} else {
    if (!isset($_SESSION['auth'])) {
        $_SESSION['referrer'] = basename($_SERVER['SCRIPT_NAME']);
        Header("Location: login.php?not_auth");
        exit;
    } else {

        // user logged

    }
}

// user is logged

if (!isset($_SESSION['user']['script'][basename($_SERVER['SCRIPT_NAME'])])) {
    Header("Location: error.php?code=".ERROR_SCRIPT_PERMISSION);
    exit;
}

?>