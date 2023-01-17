<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class noticias extends ConexionCrud{

    private $table= "web_noticias";
    private $id_banner ="";
    private $nombre ="";
    private $nombre2 ="";
    private $ruta_url ="";
    private $estado ="";
    private $token ="";

    public function obtenerNoticiasActivos(){
        $_respuestas = new respuestas;
       

        if(!isset(getallheaders()["Autorizacion"]) || getallheaders()["Autorizacion"] != 'KFTDQFYvqbPLXkHTuXQJR4Qy3vUryK' ){
            return $_respuestas->error_401();
            
        }else{

        $query = "SELECT * FROM " . $this->table . " tb1 INNER JOIN web_categorias_noticias tb2 ON tb1.id_categoria_noticias=tb2.id_categoria_noticias  WHERE tb1.estado = '1'";
        return parent::listar($query);

        }

       
    }


}
?>