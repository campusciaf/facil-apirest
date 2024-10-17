<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class registro extends ConexionCrud{

    public function registrousuario($json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos['user_email']) || !isset($datos['user_password']) || !isset($datos['repeat_password'])){
            // error con los campos
           
            return $_respuestas->error_400();

        }else{
            // todo esta bien //
            $user_mail = $datos["user_email"];
            $user_password = $datos["user_password"];
            $repeat_password = $datos["repeat_password"];
           

            if($user_password != $repeat_password){
                return $_respuestas->error_200("Las contraseñas no coinciden");
            }
            else{
                $datos = $this->obtenerDatosUsuario($user_mail);
                if($datos){
                    // si existe el usuario verificar el correo
                    if($user_mail == $datos[0]["user_email"]){

                        return $_respuestas->error_200("Ya existe un usuario con la cuenta $user_mail ");
                    }
                    
                }else{//si todo esta correcto

                    $registrar = $this->insertarUsuario($user_mail,$user_password);
                    if($registrar){
                        // si se guardo
                        $result = $_respuestas->response;
                        $result["result"]=array(
                            "id_user" => $registrar
                        );
                        $this->insertarDatos($registrar);
                        return  $result;
                    }else{
                        //error
                        return $_respuestas->error_500("error interno, no hemos podido guardar");
                    }

                }
            }

        }
    }

    private function obtenerDatosUsuario($user_mail){
        $query= "SELECT id_user,user_name,user_email,user_password,user_state from user where user_email='$user_mail'";
        $datos = parent::listar($query);
        if(isset($datos[0]["id_user"])){
            return $datos;
        }
            return 0;
    }


   
     private function insertarUsuario($user_email,$user_password){
        $val= true;
        $estado ="1"; // activo
        $fecha= date("Y-m-d");
        
        $query = "INSERT INTO user (user_email,user_password,user_state,user_creation)values('$user_email','". md5($user_password)."','$estado','$fecha')";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }

    }

    
      private function insertarDatos($id_user){

        $lastupdate= date("Y-m-d");
        
        $query = "INSERT INTO user_datos (id_user,id_workplace,id_typejob,id_title,lastupdate)values('$id_user','1','1','1','$lastupdate')";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }

    }


}
?>