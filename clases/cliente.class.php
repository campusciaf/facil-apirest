<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class cliente extends ConexionCrud{


    private $table= "cliente";
    private $token="";


    private $id_cliente_e = "";
    private $cliente_nombre_e = "";
    private $cliente_identificacion_e = "";
    private $cliente_municipio_e = "";
    private $cliente_direccion_e = "";
    private $cliente_barrio_e = "";
    private $cliente_celular_e = "";
    private $cliente_email_e = "";
    private $cliente_estado_e = "";

    /* estos son para la ubiacion */
    private $id_cliente = "";
    private $cliente_latitud = "";
    private $cliente_longitud = "";
    /* ******************/

    public function listarCliente(){
        $query = "SELECT * FROM " . $this->table . " WHERE cliente_estado=1";
        return parent::listar($query);
    }

    public function obtenerCliente($id){
        $query = "SELECT `id_cliente`, `cliente_identificacion`, `cliente_nombre`, `cliente_municipio`, `cliente_direccion`, `cliente_barrio`, `cliente_celular`, `cliente_email`, `cliente_imagen`, `cliente_estado`, `cliente_fecha`, `cliente_hora`, `cliente_user` FROM " . $this->table . " WHERE id_cliente= $id";
        return parent::listar($query);
    }

    public function obtenerUbicacion($id){
        $query = "SELECT `id_cliente`, `cliente_latitud`, `cliente_longitud` FROM " . $this->table . " WHERE id_cliente= $id";
        return parent::listar($query);
    }


    public function registroCliente($json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos['cliente_nombre']) || !isset($datos['cliente_identificacion']) || !isset($datos['cliente_celular']) || !isset($datos['cliente_email']) || !isset($datos['cliente_direccion']) || !isset($datos['cliente_estado'])){
            // error con los campos
           
            return $_respuestas->error_400();

        }else{
            // todo esta bien //
            $cliente_nombre = $datos["cliente_nombre"];
            $cliente_identificacion = $datos["cliente_identificacion"];
            $cliente_celular = $datos["cliente_celular"];
            $cliente_email = $datos["cliente_email"];
            $cliente_direccion = $datos["cliente_direccion"];
            $cliente_estado = $datos["cliente_estado"];
            $cliente_user = $datos["iduser"];
           

            $registrar = $this->insertarCLiente($cliente_identificacion,$cliente_nombre,$cliente_celular,$cliente_email,$cliente_direccion,$cliente_estado,$cliente_user);
            if($registrar){
                $result = $_respuestas->response;
                $result["result"]=array(
                    "cliente_user" => $registrar
                );
                return  $result;
            }else{
                //error
                return $_respuestas->error_500("error interno, no hemos podido guardar");
            }

        }
        
    }

    private function insertarCliente($cliente_identificacion,$cliente_nombre,$cliente_celular,$cliente_email,$cliente_direccion,$cliente_estado,$cliente_user){

        $fecha= date("Y-m-d");
        $hora= date("H:m:s");
        $cliente_clave=md5($cliente_identificacion);

        $query = "INSERT INTO cliente (cliente_identificacion,cliente_nombre,cliente_celular,cliente_email,cliente_direccion,cliente_clave,cliente_estado,cliente_fecha,cliente_hora,cliente_user)values('$cliente_identificacion','$cliente_nombre','$cliente_celular','$cliente_email','$cliente_direccion','$cliente_clave','$cliente_estado','$fecha','$hora','$cliente_user')";
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
                if($datos["accion"]=="editar"){

                    if(!isset($datos['id_cliente_e']) || !isset($datos["cliente_nombre_e"]) || !isset($datos["cliente_estado_e"])){//esto es para actualizar el perfil del cliente
                        return $_respuestas->error_400();
                    }else{
                        if(isset($datos["id_cliente_e"])) { $this->id_cliente_e = $datos['id_cliente_e']; }
                        if(isset($datos["cliente_nombre_e"])) { $this->cliente_nombre_e = $datos['cliente_nombre_e']; }
                        if(isset($datos["cliente_identificacion_e"])) { $this->cliente_identificacion_e = $datos['cliente_identificacion_e']; }
                        if(isset($datos["cliente_municipio_e"])) { $this->cliente_municipio_e = $datos['cliente_municipio_e']; }
                        if(isset($datos["cliente_direccion_e"])) { $this->cliente_direccion_e = $datos['cliente_direccion_e']; }
                        if(isset($datos["cliente_barrio_e"])) { $this->cliente_barrio_e = $datos['cliente_barrio_e']; }
                        if(isset($datos["cliente_celular_e"])) { $this->cliente_celular_e = $datos['cliente_celular_e']; }
                        if(isset($datos["cliente_email_e"])) { $this->cliente_email_e = $datos['cliente_email_e']; }
                        if(isset($datos["cliente_estado_e"])) { $this->cliente_estado_e = $datos['cliente_estado_e']; }
    
                        $resp = $this->editarCliente();
                        if($resp){
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "id_cliente" => $this->id_cliente_e
                            );
                            
                            return $respuesta;
                        }else{
                            return $resp;
                        }
                    }

                }else if($datos["accion"]=="ubicacion"){// esto es solo para actulizar la ubicacion
                    if(!isset($datos['id_cliente']) || !isset($datos["cliente_latitud"]) || !isset($datos["cliente_longitud"])){
                        return $_respuestas->error_400();
                    }else{
                        if(isset($datos["id_cliente"])) { $this->id_cliente = $datos['id_cliente']; }
                        if(isset($datos["cliente_latitud"])) { $this->cliente_latitud = $datos['cliente_latitud']; }
                        if(isset($datos["cliente_longitud"])) { $this->cliente_longitud = $datos['cliente_longitud']; }
    
                        $resp = $this->editarClienteUbicacion();
                        if($resp){
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "id_cliente" => $this->id_cliente
                            );
                            
                            return $respuesta;
                        }else{
                            return $resp;
                        }
                    }
                }
                else{
                    return $_respuestas->error_401(); 
                }

            }else{
                return $_respuestas->error_401("El token que envio es invalido o caducado");
            } 
        }
    }

    private function editarCliente(){// modelo para editar los datos del cliente
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');

        $query= "UPDATE ".$this->table." SET 
        `cliente_nombre`='" .$this->cliente_nombre_e."', 
        `cliente_identificacion`='" .$this->cliente_identificacion_e."', 
        `cliente_municipio`='" .$this->cliente_municipio_e."',
        `cliente_direccion`='" .$this->cliente_direccion_e."',
        `cliente_barrio`='" .$this->cliente_barrio_e."',
        `cliente_celular`='" .$this->cliente_celular_e."',
        `cliente_email`='" .$this->cliente_email_e."',
        `cliente_estado`='" .$this->cliente_estado_e."' 
        WHERE `id_cliente`='" . $this->id_cliente_e . "'"; 
  
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }
        else{
            return 0;
        }
    }

    private function editarClienteUbicacion(){// modelo para editar los datos del cliente pero solo la ubicacion

        $query= "UPDATE ".$this->table." SET 
        `cliente_latitud`='" .$this->cliente_latitud."', 
        `cliente_longitud`='" .$this->cliente_longitud."' 
        WHERE `id_cliente`='" . $this->id_cliente . "'"; 
  
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