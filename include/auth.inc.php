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
            AND password = '" .crypto( $_POST['password']) . "'");
        
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

                SELECT DISTINCT groups_has_services.groups_id FROM users 
                LEFT JOIN users_has_groups
                ON users_has_groups.users_email = users.email
                LEFT JOIN groups_has_services
                ON groups_has_services.groups_id = users_has_groups.groups_id 
                LEFT JOIN services
                ON services.id = groups_has_services.services_id
                WHERE email = '".$_POST['email']."'"
            );

        if (!$oid) {
            trigger_error("Generic error, level 40", E_USER_ERROR);
        }
        
        foreach($oid as $item){
            foreach($item as $item2){
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
    $mysqli->query ("INSERT INTO users (email,name,surname,password,phone) VALUES('{$_POST['email']}','{$_POST['name']}',
                         '{$_POST['surname']}','$criptoPass','{$_POST['phoneNumber']}');");

     //Inserisce l'utente nella tabella
     $mysqli->query ("INSERT INTO users_has_groups (users_email,groups_id) VALUES(
       '{$_POST['email']}',2);");

                header("location:/MotorShop/login.php");               
}

function doRegister():void{
  
    global $mysqli;
    $criptoPass=crypto($_POST['password']);
   //Inserisce l'utente nella tabella users


   $mysqli->query ("INSERT INTO users (email,name,surname,password,phone) VALUES('{$_POST['email']}','{$_POST['name']}',
                   '{$_POST['surname']}','$criptoPass','{$_POST['phoneNumber']}');");
    //Inserisce l'utente nella tabella
    $mysqli->query ("INSERT INTO users_has_groups  (users_email,groups_id) VALUES(
       '{$_POST['email']}',1);");
     

               header("location:/MotorShop/user-list.php");   
}

?>