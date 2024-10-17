<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';


class cuenta extends ConexionCrud{
    private $table= "user";
    private $table2="user_token" ;
    private $token="";
    private $id="";
    private $usuario_tema="";

    public function datoCuenta($id,$token){
        $query = "SELECT * FROM " . $this->table . " tab1 INNER JOIN " . $this->table2 . " tab2 on tab1.id_user=tab2.id_user WHERE tab1.id_user= '$id' and tab2.token='$token'";
        return parent::listar($query);
    }
    public function temaActualizar($json){// PUT toma los datos del formulario para actualziar el tema
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id']) || !isset($datos["usuario_tema"])){
                    return $_respuestas->error_400();
                }else{
                    if(isset($datos["id"])) { $this->id = $datos['id']; }
                    if(isset($datos["usuario_tema"])) { $this->usuario_tema = $datos['usuario_tema']; }

                    $resp = $this->editarTema();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_user" => $this->id
                        );
                        return $respuesta;
                    }else{
                        return $resp;
                    }
                }
            }else{
                return $_respuestas->error_401("El token que envio es invalido o caducado");
            }
        }
    }

    private function editarTema(){// modelo para editar el tema

        $query= "UPDATE ".$this->table." SET 
        `usuario_tema`='" .$this->usuario_tema."'
        WHERE `id_user`='" . $this->id . "'"; 
  
        $resp = parent::nonQuery($query);
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


}



?>