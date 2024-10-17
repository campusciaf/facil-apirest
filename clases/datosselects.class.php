<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class datosselects extends ConexionCrud{

    public function obtenerDatos($id){
        $query = "SELECT * FROM  $id";
        return parent::listar($query);
    }


}

?>