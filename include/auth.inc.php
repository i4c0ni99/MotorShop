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

    if (isset($_POST['email']) and isset($_POST['password'])) {

        $username=$_POST['username'];
        $password=crypto($_POST['password']);

        $oid = $mysqli->query("
        SELECT name,surname,email,phone
        FROM users
        WHERE email= '".$_POST['email']."'
        AND password = '".crypto($_POST['password'])."'");
    
    
    if (!$oid) {
        trigger_error("Generic error, level 21", E_USER_ERROR);
    } 
    
    if ($oid->num_rows > 0) {
    
        $user = $oid->fetch_assoc();
        $_SESSION['auth'] = true;
        $_SESSION['user'] = $user;
        $_SESSION['sizes']=array();
    
        //prende gli script che il gruppo dell'utente può vedere
        $result = $mysqli->query("
        SELECT DISTINCT script FROM users LEFT JOIN (users_has_groups,groups,groups_has_services,services) 
        ON(users.email=users_has_groups.users_email 
        AND users_has_groups.groups_id=groups.id 
        AND groups.id= groups_has_services.groups_id 
        AND groups_has_services.services_id=services.id) 
        WHERE users.email='".$_SESSION['user']['email']."'");
            
            if (!$result) {
                trigger_error("Generic error, level 40", E_USER_ERROR);
            }
            
            if(mysqli_num_rows($result) == 1){
              header("Location:login.php?error");
                exit;
            }
            $scripts=array();
            while($data=$result->fetch_assoc()){
                $scripts[$data['script']]=true;
            }
            

            
            $_SESSION['user']['script'] = $scripts;
        
            if (isset($_SESSION['referrer'])) {
                $referrer = $_SESSION['referrer'];
                unset($_SESSION['referrer']);
                Header("Location: {$referrer}");
                exit;
            }
            echo $_SESSION['user']['script'][basename($_SERVER['SCRIPT_NAME'])];
        } else {
            Header("Location: login.php");
            echo $mysqli->error;
            exit();
        }

    } else {
        if (!isset($_SESSION['auth'])) {
            $_SESSION['referrer'] = basename($_SERVER['SCRIPT_NAME']);
            Header("Location: login.php?not_auth");
        } else {

        }
    }

        
    // user is logged
    if (!isset($_SESSION['user']['script'][basename($_SERVER['SCRIPT_NAME'])] )) {
        if(!$_SESSION['user']['script'][basename($_SERVER['SCRIPT_NAME'])]){
          //controlla se l'utente ha i permessi per quella pagina
            Header("Location: error.php?code=".ERROR_SCRIPT_PERMISSION);
            exit;  
        }
        
    }

    // Funzione registrazione utente

    function doSignUp():void {

        global $mysqli;
         $criptoPass=crypto($_POST['password']);
    
        $exist= $mysqli->query("SELECT email from users where email='{$_POST['email']}'");
    
         if($exist->num_rows > 0) {
    
            echo "<script type='text/javascript'>alert('Attenzione, l'email è già in uso');</script>";
         } else {
             
        //Inserisce l'utente nella tabella users
        $mysqli->query ("INSERT INTO users (email,name,surname,password,phone) VALUES('{$_POST['email']}','{$_POST['name']}',
                             '{$_POST['surname']}','$criptoPass','{$_POST['phoneNumber']}');");
    
         //Inserisce l'utente nella tabella
         $mysqli->query ("INSERT INTO users_has_groups (users_email,groups_id) VALUES(
           '{$_POST['email']}',2);");
    
                    header("location:/MotorShop/login.php"); }
                    
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
