<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class agregarDatos extends ConexionCrud{

    private $user_email = "";
    private $user_password= "";

    public function login( $json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);


        if(!isset($datos['user_email']) || !isset($datos['user_password'])){
            // error con los campos
           
            return $_respuestas->error_400();

        }else{
            // todo esta bien //
            $user_mail = $datos["user_email"];
            $user_password = $datos["user_password"];
            $user_password=parent::encriptar($user_password);

            $datos = $this->obtenerDatosUsuario($user_mail);
            if($datos){
                // si existe el usuario verificar la contraseña
                if($user_password == $datos[0]["user_password"]){
                    if($datos[0]["user_state"]==1){
                        //crear el token
                        $verificar = $this->insertarToken($datos[0]["id_user"]);

                        if($verificar){
                            // si se guardo
                            $result = $_respuestas->response;
                            $result["result"]=array(
                                "token" => $verificar,
                                "idnum" => $datos[0]["id_user"]
                            );
                            return  $result;
                        }else{
                            //error
                            return $_respuestas->error_500("error interno, no hemos podido guardar");
                        }
                    }else{
                        // usuario inactivo
                        return $_respuestas->error_200("el usuario esta inactivo"); 
                    }
                }else{
                    return $_respuestas->error_200("La contraseña es incorrecta");
                }
                
            }else{
                // si no existe el usuario
                return $_respuestas->error_200("El usuario $user_mail no existe");
            }
        }
    }

    private function obtenerDatosUsuario($user_mail){
        $query= "SELECT id_user,user_email,user_password,user_state from user where user_email='$user_mail'";
        $datos = parent::listar($query);
        if(isset($datos[0]["id_user"])){
            return $datos;
        }
            return 0;
    }

    //crear el token
    private function insertarToken($id_user){
        $val= true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $estado ="1"; // activo
        $fecha= date("Y-m-d H:i:s");

        $query1= "SELECT id_user,token,state_token,date_token from user_token where id_user='$id_user'";// si ya tiene un token
        $datos = parent::listar($query1);
        if(isset($datos[0]["id_user"])){//actualizar el token que tiene

            $resp = $this->actualizarToken($id_user,$token);
            if($resp >= 1){
                return $token;
            }else{
                return 0;
            }
            
        }else{// si no crear un token
            $query = "INSERT INTO user_token(id_user,token,state_token,date_token)values('$id_user','$token','$estado','$fecha')";
            $verificar = parent::nonQuery($query);
            if($verificar){
                return $token;
            }else{
                return 0;
            }
        }
        
        

    }

    private function actualizarToken($id_user,$token){
        $date = date("Y-m-d H:i:s");
        $query ="UPDATE user_token SET date_token = '$date', token= '$token' WHERE id_user='$id_user'";
        $resp = parent::nonQuery($query);// non query devuelve es las filas afectadas, por eso la condicion es si es mayor a 1
        if($resp >= 1){
            return $resp;
        }
        else{
            return 0;
        }
    }
}
?>