<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class producto extends ConexionCrud{


    private $table= "producto";
    private $table2= "proveedor";
    private $token="";


    private $id_producto_e = "";
    private $producto_nombre_e = "";
    private $producto_imagen_e = "";
    private $producto_venta_e = "";
    private $producto_estado_e = "";

    public function listarProductos(){
        $query = "SELECT * FROM " . $this->table ;
        return parent::listar($query);
    }

    public function listarProducto($id){
        $query = "SELECT id_producto,id_proveedor,producto_nombre,producto_imagen,producto_disponibles,producto_venta,producto_estado,producto_fecha FROM " . $this->table . " WHERE id_proveedor=$id";
        return parent::listar($query);
    }

    public function obtenerProductoId($id){
        $query = "SELECT id_producto,producto_nombre,producto_imagen,producto_disponibles,producto_venta,producto_estado,producto_fecha FROM " . $this->table . " WHERE id_producto= $id";
        return parent::listar($query);
    }

    public function listarProductosDisponibles(){
        $query = "SELECT * FROM " . $this->table . " tab1 INNER JOIN "  . $this->table2 . " tab2 ON tab1.id_proveedor=tab2.id_proveedor WHERE tab1.producto_estado=1";
        return parent::listar($query);
    }

    public function listarProductosDisponiblesVentas(){
        $query = "SELECT * FROM " . $this->table . " tab1 INNER JOIN "  . $this->table2 . " tab2 ON tab1.id_proveedor=tab2.id_proveedor WHERE tab1.producto_disponibles > 0";
        return parent::listar($query);
    }


    public function registroproducto($json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);
       
               
        if(!isset($datos['producto_nombre']) || !isset($datos['producto_imagen']) || !isset($datos['producto_estado']) || !isset($datos['producto_id_usuario'])){
            // error con los campos

            return $_respuestas->error_400();
           
        }else{
            // todo esta bien //
            
            $producto_nombre = $datos["producto_nombre"];
            $producto_imagen = $datos["producto_imagen"];
            $producto_estado = $datos["producto_estado"];
            $producto_id_usuario = $datos["producto_id_usuario"];

            if($datos['producto_imagen'] == "a"){
                $registrar = $this->insertarProducto($producto_nombre,$producto_estado,$producto_id_usuario);
                
            }else{
                $registrar = $this->insertarProductoImagen($producto_nombre,$producto_imagen,$producto_estado,$producto_id_usuario);
            }
            
            if($registrar){
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

    private function insertarProducto($producto_nombre,$producto_estado,$producto_id_usuario){
        $fecha= date("Y-m-d");
        $query = "INSERT INTO producto (producto_nombre,producto_estado,producto_fecha,producto_id_usuario)values('$producto_nombre','$producto_estado','$fecha','$producto_id_usuario')";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }

    }
    private function insertarProductoImagen($producto_nombre,$producto_imagen,$producto_estado,$producto_id_usuario){
        $val= true;
        $estado ="1"; // activo
        $fecha= date("Y-m-d");
        $query = "INSERT INTO producto (producto_nombre,producto_imagen,producto_estado,producto_fecha,producto_id_usuario)values('$producto_nombre','$producto_imagen','$producto_estado','$fecha','$producto_id_usuario')";
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
                if(!isset($datos['id_producto_e']) || !isset($datos["producto_nombre_e"])){
                    return $_respuestas->error_400();
                }else{
                    if(isset($datos["id_producto_e"])) { $this->id_producto_e = $datos['id_producto_e']; }
                    if(isset($datos["producto_nombre_e"])) { $this->producto_nombre_e = $datos['producto_nombre_e']; }
                    if(isset($datos["producto_imagen_e"])) { $this->producto_imagen_e = $datos['producto_imagen_e']; }
                    if(isset($datos["producto_venta_e"])) { $this->producto_venta_e = $datos['producto_venta_e']; }
                    if(isset($datos["producto_estado_e"])) { $this->producto_estado_e = $datos['producto_estado_e']; }

                  
                    $resp = $this->editarProducto();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_producto" => $this->id_producto_e
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

    private function editarProducto(){// modelo para editar los datos del usuario
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');

        $query= "UPDATE ".$this->table." SET 
        `producto_nombre`='" .$this->producto_nombre_e."', 
        `producto_imagen`='" .$this->producto_imagen_e."',
        `producto_venta`='" .$this->producto_venta_e."',  
        `producto_estado`='" .$this->producto_estado_e."' 
        WHERE `id_producto`='" . $this->id_producto_e . "'"; 
  
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