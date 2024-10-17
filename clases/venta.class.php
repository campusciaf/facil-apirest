<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class venta extends ConexionCrud{


    private $table= "venta";
    private $table2= "venta_productos";
    private $table3= "producto";
    private $table4= "cliente";
    private $table5= "proveedor";
    private $token="";

    /* datos para terminar la compra */

    private $id_venta="";
    private $id_cliente="";
    private $venta_neto_suma="";
    private $venta_iva="";
    private $venta_total_iva="";
    private $venta_total="";
    
    /* ******************* */

    public function listarVentas(){
        $query = "SELECT * FROM " . $this->table . " tab1 INNER JOIN " . $this->table4 . " tab4 ON tab1.id_cliente=tab4.id_cliente WHERE tab1.venta_estado=0";
        return parent::listar($query);
        
    }

    public function listarEntrega($id_venta){
        $query = "SELECT * FROM " . $this->table . " tab1 INNER JOIN " . $this->table4 . " tab4 ON tab1.id_cliente=tab4.id_cliente WHERE tab1.venta_estado=0 and tab1.venta_despacho=0";
        return parent::listar($query);
        
    }

    public function listarVentaDetalle($id){
        $query = "SELECT * FROM " . $this->table2 . " tab2 INNER JOIN " . $this->table3 . " tab3 ON tab2.id_producto=tab3.id_producto INNER JOIN " . $this->table5. " tab5 ON tab3.id_proveedor=tab5.id_proveedor WHERE tab2.id_venta=$id";
        return parent::listar($query);
        
    }

    public function  estadoAlistado($id,$estado,$id_venta){

        $id = json_decode($id,true);
        $estado = json_decode($estado,true);

        $query= "UPDATE ".$this->table2." SET 
        `venta_producto_listo`='" .$estado."'
        WHERE `id_venta_productos`='" . $id . "'"; 
        $resp1 = parent::listar($query);

        if($resp1 >= 1){
            $query2 = "SELECT id_venta,venta_producto_listo FROM " . $this->table2 . " WHERE id_venta=$id_venta and venta_producto_listo=1"; 
            $resp2 = parent::listar($query2);

            
            if($resp2){
                $estado_cambio=1;
                    $query3= "UPDATE ".$this->table." SET 
                    `venta_despacho`='" .$estado_cambio."'
                    WHERE `id_venta`='" . $id_venta . "'"; 
                    return  parent::listar($query3);
            }else{
                $estado_cambio=0;
                $query3= "UPDATE ".$this->table." SET 
                    `venta_despacho`='" .$estado_cambio."'
                    WHERE `id_venta`='" . $id_venta . "'"; 
                    return  parent::listar($query3);
            }
        }
        
    }

    public function  estadoEntrega($id){

        $id = json_decode($id,true);
        $estado=0;
        $query= "UPDATE ".$this->table." SET 
        `venta_entregado`='" .$estado."'
        WHERE `id_venta`='" . $id . "'"; 
        return parent::listar($query);

        
    }

    public function verificarVenta($id){
        $query = "SELECT id_venta,id_cliente,venta_neto_suma,venta_iva,venta_total_iva,venta_total,venta_fecha,venta_hora,venta_estado,venta_id_usuario FROM " . $this->table . " WHERE venta_id_usuario=$id and venta_estado=1";
        return parent::listar($query);
        
    }

    public function crearCompra($iduser){
        $fecha= date("Y-m-d");
        $hora=date("H:m:s");
        $id_usuario=$iduser;
        $query = "INSERT INTO $this->table (venta_fecha,venta_hora,venta_id_usuario) values('$fecha','$hora','$id_usuario')";
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }
        else{
            return 0;
        }

    }

    public function traerVenta($id_venta){
        $query = "SELECT * FROM " . $this->table2 . " tb2 INNER JOIN  " . $this->table3 . " tb3 ON tb2.id_producto=tb3.id_producto WHERE tb2.id_venta= $id_venta";
        return parent::listar($query);
    }

    public function registroVenta($json){
        $_respuestas =new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos['id_venta']) || !isset($datos['id_producto']) || !isset($datos['venta_productos_cantidad']) || !isset($datos['venta_productos_precio']) || !isset($datos['venta_productos_usuario'])){
            // error con los campos
           
            return $_respuestas->error_400();

        }else{
            // todo esta bien //
            $id_venta= $datos["id_venta"];
            $id_producto = $datos["id_producto"];
            $venta_productos_cantidad = $datos["venta_productos_cantidad"];
            $venta_productos_precio = $datos["venta_productos_precio"];
            $venta_productos_usuario = $datos["venta_productos_usuario"];

            $registrar = $this->insertarVenta($id_venta,$id_producto,$venta_productos_cantidad,$venta_productos_precio,$venta_productos_usuario);
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


    private function insertarVenta($id_venta,$id_producto,$venta_productos_cantidad,$venta_productos_precio,$venta_productos_usuario){

        $fecha= date("Y-m-d");
        $hora= date("H:m:s");
        $venta_productos_subtotal=$venta_productos_cantidad*$venta_productos_precio;

        $query = "INSERT INTO venta_productos (id_venta,id_producto,venta_productos_cantidad,venta_productos_precio,venta_productos_subtotal,venta_productos_fecha,venta_productos_hora,venta_productos_usuario)
        values('$id_venta','$id_producto','$venta_productos_cantidad','$venta_productos_precio','$venta_productos_subtotal','$fecha','$hora','$venta_productos_usuario')";
        $resp = parent::nonQueryId($query);
        if($resp){
            $this->restarUnidades($id_producto,$venta_productos_cantidad);
            return $resp;
        }
        else{
            return 0;
        }

    }

    private function restarUnidades($id_producto,$venta_productos_cantidad){
        $buscarDisponibles= "SELECT id_producto,producto_disponibles FROM " . $this->table3 . " WHERE id_producto=$id_producto";
        $cantidad= parent::listar($buscarDisponibles);
        $nueva_cantidad=$cantidad[0]["producto_disponibles"]-$venta_productos_cantidad;
        
            $query= "UPDATE ".$this->table3." SET 
            `producto_disponibles`='".$nueva_cantidad."'
            WHERE `id_producto`='" . $id_producto . "'"; 
            $resp = parent::nonQuery($query);

    }

    public function formularioBorrarVenta($json){// DELETE toma los datos para eliminar un usuario
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
                    $traerdatosventaeliminar= "SELECT id_producto,venta_productos_cantidad FROM " . $this->table2 . " WHERE id_venta_productos =$id_eliminar";
                    $datoseliminar= parent::listar($traerdatosventaeliminar);
                        $id_producto=$datoseliminar[0]["id_producto"];
                        $venta_productos_cantidad=$datoseliminar[0]["venta_productos_cantidad"];

                    $resp = $this->borrarVenta($id_eliminar,$id_producto,$venta_productos_cantidad);

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

    private function borrarVenta($id_eliminar,$id_producto,$venta_productos_cantidad){
        $query = "DELETE FROM " . $this->table2 . " WHERE id_venta_productos='" .$id_eliminar. "'";
        $resp = parent::nonQuery($query);// non query devuelve es las filas afectadas, por eso la condicion es si es mayor a 1
        if($resp >= 1){
            $this->sumarUnidades($id_producto,$venta_productos_cantidad);
            return $resp;
        }
        else{
            return 0;
        }
    }

    private function sumarUnidades($id_producto,$venta_productos_cantidad){
        $buscarDisponibles= "SELECT id_producto,producto_disponibles FROM " . $this->table3 . " WHERE id_producto=$id_producto";
        $cantidad= parent::listar($buscarDisponibles);
        $nueva_cantidad=$cantidad[0]["producto_disponibles"]+$venta_productos_cantidad;
        
            $query= "UPDATE ".$this->table3." SET 
            `producto_disponibles`='".$nueva_cantidad."'
            WHERE `id_producto`='" . $id_producto . "'"; 
            $resp = parent::nonQuery($query);

    }
      
    public function terminarVenta($json){// PUT toma los datos del formulario para editar un usuario
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos["token"])){
            return $_respuestas->error_401();
        }
        else{
            $this->token=$datos["token"];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_venta']) || !isset($datos["venta_neto_suma"]) || !isset($datos["venta_iva"])|| !isset($datos["venta_total_iva"])|| !isset($datos["venta_total"])){
                    return $_respuestas->error_400();
                }else{
                    if(isset($datos["id_venta"])) { $this->id_venta = $datos['id_venta']; }
                    if(isset($datos["id_cliente"])) { $this->id_cliente = $datos['id_cliente']; }
                    if(isset($datos["venta_neto_suma"])) { $this->venta_neto_suma = $datos['venta_neto_suma']; }
                    if(isset($datos["venta_iva"])) { $this->venta_iva = $datos['venta_iva']; }
                    if(isset($datos["venta_total_iva"])) { $this->venta_total_iva = $datos['venta_total_iva']; }
                    if(isset($datos["venta_total"])) { $this->venta_total = $datos['venta_total']; }
                  
                    $resp = $this->editarVenta();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_venta" => $this->id_venta
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

    private function editarVenta(){// modelo para editar los datos del usuario
        date_default_timezone_set("America/Bogota");		
        $fecha = date('Y-m-d');
        $hora = date('h:i:s');
        $estado=0;

        $query= "UPDATE ".$this->table." SET 
        `id_cliente`='" .$this->id_cliente."',
        `venta_neto_suma`='" .$this->venta_neto_suma."', 
        `venta_iva`='" .$this->venta_iva."', 
        `venta_total_iva`='" .$this->venta_total_iva."' ,
        `venta_total`='" .$this->venta_total."',
        `venta_fecha`='" .$fecha."',
        `venta_hora`='" .$hora."',
        `venta_estado`='" .$estado."'
        WHERE `id_venta`='" . $this->id_venta . "'"; 
  
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            $respestado = $this->actualizarEstadoProducto();
            return $resp;
        }
        else{
            return 0;
        }
    }


    private function actualizarEstadoProducto(){// cambia el estado de los productos en la tabla ventas_producto a 0
        $query= "UPDATE ".$this->table2." SET 
        `venta_productos_estado`='0'
        WHERE `id_venta`='" . $this->id_venta . "'"; 
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