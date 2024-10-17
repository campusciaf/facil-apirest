<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';




class usuarios extends ConexionCrud{

    private $table= "user";
    private $table2="user_datos" ;
    private $id_user="";
    private $user_name = "";
    private $user_email = "";
    private $user_password= "";
    private $user_tel= "";
    private $user_state= "1";
    private $token="";

    private $firstname="";
    private $middlename="";
    private $lastname="";
    private $facebook="";



    public function listarUsuarios($pagina = 1){
        $inicio = 0;
        $cantidad = 100;
        if($pagina>1){
            $inicio=($cantidad*($pagina-1)) +1;
            $cantidad = $cantidad*$pagina;
        }


        $query = "SELECT id_user,user_name,user_email,user_tel,user_state,user_creation FROM " . $this->table . " limit $inicio,$cantidad";
        return parent::listar($query);
       
    }

    public function obtenerUsuario($id){
        $query = "SELECT * FROM " . $this->table . " tab1 INNER JOIN " . $this->table2 . " tab2 ON tab1.id_user=tab2.id_user WHERE tab1.id_user= '$id'";
        return parent::listar($query);
    }

    public function formularioUsuario($json){// POST toma los datos del formulario para insertar un nuevo usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){

                if(!isset($datos['user_name']) || !isset($datos['user_email']) || !isset($datos['user_password'])){
                    return $_respuestas->error_400();
                }else{
                    $this->user_name=$datos["user_name"];
                    $this->user_email=$datos["user_email"];
                    $this->user_password=$datos["user_password"];
                    if(isset($datos["user_tel"])) { $this->user_tel = $datos['user_tel']; }
                    if(isset($datos["user_state"])) { $this->user_state = $datos['user_state']; }
                    $resp = $this->insertarUsuario();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_user" => $resp
                        );
                        return $respuesta;
                    }else{
                        return $_respuestas->error_500();
                    }
                }

            }else{
                return $_respuestas->error_401("El token que envio es invalido o caducado");
            }
            
        }

        
    }

    private function insertarUsuario(){
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');


        $query ="INSERT INTO " . $this->table . " (user_name,user_email,user_password,user_tel,user_state,user_creation) 
        values ('". $this->user_name."','". $this->user_email."','". md5($this->user_password)."','". $this->user_tel."','". $this->user_state."','". $fecha."') ";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }
    }

    public function formularioEditarUsuario($json){// PUT toma los datos del formulario para editar un usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        
        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_user'])){
                    return $_respuestas->error_400();
                }else{
                    $this->id_user=$datos['id_user'];
                    if(isset($datos["user_name"])) { $this->user_name = $datos['user_name']; }
                    if(isset($datos["user_email"])) { $this->user_email = $datos['user_email']; }
                    if(isset($datos["user_password"])) { $this->user_password = $datos['user_password']; }
                    if(isset($datos["user_tel"])) { $this->user_tel = $datos['user_tel']; }
                    if(isset($datos["user_state"])) { $this->user_state = $datos['user_state']; }
                    
                    $resp = $this->editarUsuario();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_user" => $this->id_user
                        );
                        
                        return $respuesta;
                    }else{
                        return $_respuestas->error_500();
                    }
                }

            }else{
                return $_respuestas->error_401("El token que envio es invalido o caducado");
            }
            
        }


    }

    private function editarUsuario(){// modelo para editar los datos del usuario
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');


        $query ="UPDATE " . $this->table . 
        " tab1 INNER JOIN ". $this->table2 . " tab2 ON tab1.id_user=tab2.id_user SET tab1.user_name='" .$this->user_name .
        "',tab1.user_email='" .$this->user_email . 
        "',tab1.user_tel='" .$this->user_tel.
        "',tab1.user_state='" .$this->user_state . 
        "' WHERE tab1.id_user='" . $this->id_user . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }
        else{
            return 0;
        }
    }

    public function formularioBorrarUsuario($json){// DELETE toma los datos para eliminar un usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        
        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_user'])){
                    return $_respuestas->error_400();
                }else{
                    $this->id_user=$datos['id_user'];
                    $resp = $this->borrarUsuario();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_user" => $this->id_user
                        );
                        return $respuesta;
                    }else{
                        return $_respuestas->error_500();
                    }
                }

            }else{
                return $_respuestas->error_401("El token que envio es invalido o caducado");
            }
            
        }
    }

    private function borrarUsuario(){
        $query = "DELETE FROM " . $this->table . " WHERE id_user='" .$this->id_user. "'";
        $resp = parent::nonQuery($query);// non query devuelve es las filas afectadas, por eso la condicion es si es mayor a 1
        if($resp >= 1){
            return $resp;
        }
        else{
            return 0;
        }
    }
      
    private function buscarToken(){
        $query = "SELECT id_user,token,state_token FROM user_token WHERE token='" .$this->token. "' AND state_token='1'";
        $resp = parent::listar($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }
    }

    private function actualizarToken($id_user_token){
        $date = date("Y-m-d H:i:s");
        $query ="UPDATE user_token SET date_token = '$date' WHERE id_user_token='$id_user_token'";
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