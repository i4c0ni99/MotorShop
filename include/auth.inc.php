<?php
DEFINE('ERROR_SCRIPT_PERMISSION', 100);
DEFINE('ERROR_USER_NOT_LOGGED', 200);
DEFINE('ERROR_OWNERSHIP', 200);

// Funzione hashing MD5 password
function crypto($pass) {

    return md5(md5($pass));
    
}

// Controllo email già utilizzata
/*function isOwner($resource, $key = "id") {

    global $mysqli;

    $oid = $mysqli->query("
            SELECT email 
            FROM {$resource} 
            WHERE {$key} = '{$_REQUEST[$key]}'");
    if (!$oid) {
    }

    $data = $oid->fetch_assoc();

    if ($data['owner_email'] != $_SESSION['user']['email']) {

        Header("Location: error.php?code=".ERROR_OWNERSHIP);
        exit;

    }

}*/

// Funzione LogIn
function doLogin(): void
{

    global $mysqli;

        // Query email e password utente
        $oid = $mysqli->query("
            SELECT email, password,name, surname, phone
            FROM users 
            WHERE email = '" . $_POST['email'] . "'
            AND password = '" . $_POST['password'] . "'");
        
        if ($oid->num_rows > 0) {
            // Ottiene dati utente
            $user = $oid->fetch_assoc();
            createSession($user, $mysqli);
        }

    }

// Funzione crea sessione
function createSession($user, mysqli $mysqli): void
{
         
        // Crea una sessione per l'utente
        $_SESSION['auth'] = true;
        $_SESSION['user'] = $user;
    
     $oid = $mysqli->query("

                SELECT DISTINCT services_has_group.group_id FROM users 
                LEFT JOIN users_has_group
                ON users_has_group.users_email = users.email
                LEFT JOIN services_has_group
                ON services_has_group.group_id = users_has_group.group_id 
                LEFT JOIN services
                ON services.id = services_has_group.services_id
                WHERE email = '".$_POST['email']."'"
            );

        if (!$oid) {
            trigger_error("Generic error, level 40", E_USER_ERROR);
        }
        
        foreach($oid as $item){
            foreach($item as $item2)
                echo $item2;
        }
        
         
            

         

        do {
            $data = $oid->fetch_assoc();
            if ($data) { 
            echo $data['link'];

                 $scripts[$data['link']] = true;
            
            }
        } while ($data);
        $_SESSION['auth'] = $scripts;
 
        foreach($oid as $item){
            foreach($item as $item2){
                echo $item2;
         if($item2==2)
            header("location:/MotorShop/index.php");
         if($item2==1){
         
             header("location:/MotorShop/dashBoard.php");
         }
            }

         }
        
        
}

// Funzione registrazione utente
function doSignUp():void {

    global $mysqli;
     $criptoPass=crypto($_POST['password']);

    //Inserisce l'utente nella tabella users
     $mysqli->query ("INSERT INTO users (name,surname,email,phone,password) VALUES('{$_POST['name']}','{$_POST['surname']}',
                         '{$_POST['email']}','{$_POST['phoneNumber']}','$criptoPass');");

     //Inserisce l'utente nella tabella
     $mysqli->query ("INSERT INTO users_has_group (users_email,group_id) VALUES(
        '{$_POST['email']}',2);");

                header("location:/MotorShop/login.php");               
}

?>