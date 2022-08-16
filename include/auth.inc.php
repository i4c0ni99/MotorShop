<?php

    DEFINE('ERROR_SCRIPT_PERMISSION', 100);
    DEFINE('ERROR_USER_NOT_LOGGED', 200);
    DEFINE('ERROR_OWNERSHIP', 200);

    function crypto($pass) {

        return md5(md5($pass));

    }

    function isOwner($resource, $key = "id") {

        global $mysqli;

        $oid = $mysqli->query("
            SELECT owner_username 
            FROM {$resource} 
            WHERE {$key} = '{$_REQUEST[$key]}'");
        if (!$oid) {
            // error
        }
        
        $data = $oid->fetch_assoc();

        if ($data['owner_username'] != $_SESSION['user']['username']) {

            Header("Location: error.php?code=".ERROR_OWNERSHIP);
            exit;

        }

    }

    if (isset($_POST['username']) and isset($_POST['password'])) {
        global $mysqli;
        $oid = $mysqli->query("
            SELECT username, name, surname, email 
            FROM user 
            WHERE username = '".$_POST['username']."'
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
                LEFT JOIN user_has_ugroup
                ON user_has_ugroup.user_username = user.username
                LEFT JOIN ugroup_has_service
                ON ugroup_has_service.ugroup_id = user_has_ugroup.ugroup_id 
                LEFT JOIN service
                ON service.id = ugroup_has_service.service_id
                WHERE username = '".$_POST['username']."'");
            
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