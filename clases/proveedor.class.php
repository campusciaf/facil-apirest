<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class proveedor extends ConexionCrud{


    private $table= "proveedor";
    private $token="";


    private $id_proveedor_e = "";
    private $proveedor_nombre_e = "";
    private $proveedor_imagen_e = "";
    private $proveedor_estado_e = "";

    public function listarProveedores($pagina = 1){
        $inicio = 0;
        $cantidad = 100;
        if($pagina>1){
            $inicio=($cantidad*($pagina-1)) +1;
            $cantidad = $cantidad*$pagina;
        }


        $query = "SELECT id_proveedor,proveedor_nombre,	proveedor_imagen,proveedor_estado,proveedor_fecha FROM " . $this->table . " limit $inicio,$cantidad";
        return parent::listar($query);
       
    }

    public function obtenerProveedor($id){
        $query = "SELECT id_proveedor,proveedor_nombre,	proveedor_imagen,proveedor_estado,proveedor_fecha FROM " . $this->table . " WHERE id_proveedor= $id";
        return parent::listar($query);
    }

    public function registroproveedor($json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos['proveedor_nombre']) || !isset($datos['proveedor_estado']) || !isset($datos['proveedor_imagen'])){
            // error con los campos
           
            return $_respuestas->error_400();

        }else{
            // todo esta bien //
            $proveedor_nombre = $datos["proveedor_nombre"];
            $proveedor_imagen = $datos["proveedor_imagen"];
            $proveedor_estado = $datos["proveedor_estado"];

            $registrar = $this->insertarProveedor($proveedor_nombre,$proveedor_imagen,$proveedor_estado);
            if($registrar){

                // move_uploaded_file($_FILES["proveedor_imagen"]["tmp_name"], '../'. $proveedor_imagen);
                // si se guardo
                $result = $_respuestas->response;
                $result["result"]=array(
                    "id_user" => $registrar
                );
                return  $result;
            }else{
                //error
                return $_respuestas->error_500("error interno, no hemos podido guardar");
            }

        }
            

        
    }

    private function insertarProveedor($proveedor_nombre,$proveedor_imagen,$proveedor_estado){
        $val= true;
        $estado ="1"; // activo
        $fecha= date("Y-m-d");
        $query = "INSERT INTO proveedor (proveedor_nombre,proveedor_imagen,proveedor_estado,proveedor_fecha)values('$proveedor_nombre','$proveedor_imagen','$proveedor_estado','$fecha')";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }

    }

    public function formularioEditar($json){// PUT toma los datos del formulario para editar un usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_proveedor_e']) || !isset($datos["proveedor_nombre_e"])){
                    return $_respuestas->error_400();
                }else{
                    if(isset($datos["id_proveedor_e"])) { $this->id_proveedor_e = $datos['id_proveedor_e']; }
                    if(isset($datos["proveedor_nombre_e"])) { $this->proveedor_nombre_e = $datos['proveedor_nombre_e']; }
                    if(isset($datos["proveedor_imagen_e"])) { $this->proveedor_imagen_e = $datos['proveedor_imagen_e']; }
                    if(isset($datos["proveedor_estado_e"])) { $this->proveedor_estado_e = $datos['proveedor_estado_e']; }

                  
                    $resp = $this->editarUsuario();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_proveedor" => $this->id_proveedor_e
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

    private function editarUsuario(){// modelo para editar los datos del usuario
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');

        $query= "UPDATE ".$this->table." SET 
        `proveedor_nombre`='" .$this->proveedor_nombre_e."', 
        `proveedor_imagen`='" .$this->proveedor_imagen_e."', 
        `proveedor_estado`='" .$this->proveedor_estado_e."' 
        WHERE `id_proveedor`='" . $this->id_proveedor_e . "'"; 
  
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