<?php 

require "include/template2.inc.php";
require "include/dbms.inc.php";

    if (isset($_GET['email']) && isset($_GET['v_cod'])) {
        
        $email = $_GET['email'];    
        $v_cod = $_GET['v_cod'];

        $sql="SELECT * FROM user WHERE email = '$email' AND verification_id = '$v_cod'";
        $result = $conn->query($sql);

        if ($result) {
            
            if ($result->num_rows == 1) {
                
                $row = $result->fetch_assoc();
                $fetch_Email = $row['email'];
                    
                if ($row['verification_status'] == 0) {
                    $update = "UPDATE user SET verification_status='1' WHERE email = '$fetch_Email'";
                    
                    if ($conn->query($update)===TRUE) {
                    echo "
                        <script>
                            alert('Verifica completata con successo');
                            window.location.href='login.php'
                        </script>"; 
                    }else{
                    echo "
                        <script>
                            alert('Query error!');
                            window.location.href='login.php' 
                        </script>";
                    }
                }else{
                    echo "
                        <script>
                            alert('Attenzione, questa email è già in uso');
                            window.location.href='login.php'
                        </script>";
                }
            }
        }   
    } else {
        echo "
            <script>
                alert('Si è verificato un problema, riprova più tardi');
                window.location.href='login.php'
            </script>";
    }
 ?>