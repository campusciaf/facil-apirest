<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class compra extends ConexionCrud{


    private $table= "compra";
    private $table2= "compra_productos";
    private $table3= "producto";
    private $token="";

    /* datos para terminar la compra */

    private $id_compra="";
    private $id_proveedor="";
    private $compra_neto_suma="";
    private $compra_iva="";
    private $compra_total_iva="";
    private $compra_total="";
    
    /* ******************* */

    public function verificarCompra($id){
        $query = "SELECT id_compra,id_proveedor,compra_neto_suma,compra_iva,compra_total_iva,compra_total,compra_fecha,compra_hora,compra_estado,compra_id_usuario FROM " . $this->table . " WHERE compra_id_usuario=$id and compra_estado=1";
        return parent::listar($query);
    }

    public function crearCompra($iduser){
        $fecha= date("Y-m-d");
        $hora=date("H:m:s");
        $id_usuario=$iduser;
        $query = "INSERT INTO $this->table (compra_fecha,compra_hora,compra_id_usuario) values('$fecha','$hora','$id_usuario')";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }

    }

    public function traercompra($id_compra){
        $query = "SELECT * FROM " . $this->table2 . " tb2 INNER JOIN  " . $this->table3 . " tb3 ON tb2.id_producto=tb3.id_producto WHERE tb2.id_compra= $id_compra";
        return parent::listar($query);
    }

    public function registroCompra($json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos['id_compra']) || !isset($datos['id_producto']) || !isset($datos['compra_productos_cantidad']) || !isset($datos['compra_productos_precio']) || !isset($datos['compra_productos_id_usuario'])){
            // error con los campos
           
            return $_respuestas->error_400();

        }else{
            // todo esta bien //
            $id_compra = $datos["id_compra"];
            $id_producto = $datos["id_producto"];
            $compra_productos_cantidad = $datos["compra_productos_cantidad"];
            $compra_productos_precio = $datos["compra_productos_precio"];
            $compra_productos_id_usuario = $datos["compra_productos_id_usuario"];

       

            $registrar = $this->insertarCompra($id_compra,$id_producto,$compra_productos_cantidad,$compra_productos_precio,$compra_productos_id_usuario);
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

    private function insertarCompra($id_compra,$id_producto,$compra_productos_cantidad,$compra_productos_precio,$compra_productos_id_usuario){
        
        $fecha= date("Y-m-d");
        $hora= date("H:m:s");
        $compra_productos_subtotal=$compra_productos_cantidad*$compra_productos_precio;
        $query = "INSERT INTO compra_productos (id_compra,id_producto,compra_productos_cantidad,compra_productos_precio,compra_productos_subtotal,compra_productos_fecha,compra_productos_hora,compra_productos_id_usuario)
        values('$id_compra','$id_producto','$compra_productos_cantidad','$compra_productos_precio','$compra_productos_subtotal','$fecha','$hora','$compra_productos_id_usuario')";
        $resp = parent::nonQueryId($query);
        if($resp){
            $this->sumarUnidades($id_producto,$compra_productos_cantidad);
            return $resp;
        }
        else{
            return 0;
        }

    }

    public function formularioBorrarCompra($json){// DELETE toma los datos para eliminar un usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        
        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_del'])){
                    return $_respuestas->error_400();
                }else{
                    $id_eliminar=$datos['id_del'];
                    $traerdatoscompraeliminar= "SELECT id_producto,compra_productos_cantidad FROM " . $this->table2 . " WHERE id_compra_productos =$id_eliminar";
                    $datoseliminar= parent::listar($traerdatoscompraeliminar);
                        $id_producto=$datoseliminar[0]["id_producto"];
                        $compra_productos_cantidad=$datoseliminar[0]["compra_productos_cantidad"];

                    $resp = $this->borrarCompra($id_eliminar,$id_producto,$compra_productos_cantidad);
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

    private function borrarCompra($id_eliminar,$id_producto,$compra_productos_cantidad){
        $query = "DELETE FROM " . $this->table2 . " WHERE id_compra_productos='" .$id_eliminar. "'";
        $resp = parent::nonQuery($query);// non query devuelve es las filas afectadas, por eso la condicion es si es mayor a 1
        if($resp >= 1){
            $this->restarUnidades($id_producto,$compra_productos_cantidad);
            return $resp;
        }
        else{
            return 0;
        }
    }
      
    public function terminarCompra($json){// PUT toma los datos del formulario para editar un usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_compra']) || !isset($datos['id_proveedor']) || !isset($datos["compra_neto_suma"])|| !isset($datos["compra_iva"])|| !isset($datos["compra_total_iva"])|| !isset($datos["compra_total"])){
                    return $_respuestas->error_400();
                }else{
                    if(isset($datos["id_compra"])) { $this->id_compra = $datos['id_compra']; }
                    if(isset($datos["id_proveedor"])) { $this->id_proveedor = $datos['id_proveedor']; }
                    if(isset($datos["compra_neto_suma"])) { $this->compra_neto_suma = $datos['compra_neto_suma']; }
                    if(isset($datos["compra_iva"])) { $this->compra_iva = $datos['compra_iva']; }
                    if(isset($datos["compra_total_iva"])) { $this->compra_total_iva = $datos['compra_total_iva']; }
                    if(isset($datos["compra_total"])) { $this->compra_total = $datos['compra_total']; }
                  
                    $resp = $this->editarCompra();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_compra" => $this->id_compra
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

    private function editarCompra(){// modelo para editar los datos del usuario
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');
        $estado=0;

        $query= "UPDATE ".$this->table." SET 
        `id_proveedor`='" .$this->id_proveedor."', 
        `compra_neto_suma`='" .$this->compra_neto_suma."', 
        `compra_iva`='" .$this->compra_iva."', 
        `compra_total_iva`='" .$this->compra_total_iva."' ,
        `compra_total`='" .$this->compra_total."',
        `compra_fecha`='" .$fecha."',
        `compra_hora`='" .$hora."',
        `compra_estado`='" .$estado."'
        WHERE `id_compra`='" . $this->id_compra . "'"; 
  
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            $respestado = $this->actualizarEstadoProducto();
            return $resp;
        }
        else{
            return 0;
        }
    }

    private function actualizarEstadoProducto(){// cambia el estado de los productos en la tabla compras productos a 0
        $query= "UPDATE ".$this->table2." SET 
        `compra_productos_estado`='0'
        WHERE `id_compra`='" . $this->id_compra . "'"; 
        $resp = parent::nonQuery($query);

    }



    private function sumarUnidades($id_producto,$compra_productos_cantidad){
        $buscarDisponibles= "SELECT id_producto,producto_disponibles FROM " . $this->table3 . " WHERE id_producto=$id_producto";
        $cantidad= parent::listar($buscarDisponibles);
        $nueva_cantidad=$cantidad[0]["producto_disponibles"]+$compra_productos_cantidad;
        
            $query= "UPDATE ".$this->table3." SET 
            `producto_disponibles`='".$nueva_cantidad."'
            WHERE `id_producto`='" . $id_producto . "'"; 
            $resp = parent::nonQuery($query);

    }

    private function restarUnidades($id_producto,$compra_productos_cantidad){
        $buscarDisponibles= "SELECT id_producto,producto_disponibles FROM " . $this->table3 . " WHERE id_producto=$id_producto";
        $cantidad= parent::listar($buscarDisponibles);
        $nueva_cantidad=$cantidad[0]["producto_disponibles"]-$compra_productos_cantidad;
        
            $query= "UPDATE ".$this->table3." SET 
            `producto_disponibles`='".$nueva_cantidad."'
            WHERE `id_producto`='" . $id_producto . "'"; 
            $resp = parent::nonQuery($query);

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