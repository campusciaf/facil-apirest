<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class agregarDatos extends ConexionCrud{

    private $table= "on_interesados";
    private $table2= "on_periodo_actual";
    private $table3= "on_interesados_datos";
    
    private $id_banner ="";
   
    private $nombre ="";
    private $correo ="";
    private $celular ="";
    private $fo_programa ="";
    private $token ="";
    private $identificacion="";
    private $medio="Web";
    private $estado="Interesado";
    private $clave="";


    public function insertaragregarDatos($json){
        $_respuestas = new respuestas;

        if(!isset(getallheaders()["Autorizacion"]) || getallheaders()["Autorizacion"] != 'KFTDQFYvqbPLXkHTuXQJR4Qy3vUryK' ){
            return $_respuestas->error_401();
            
        }else{

            date_default_timezone_set("America/Bogota");		
            $fecha = date('Y-m-d');
            $hora = date('h:i:s');

            // algoritmo para generar una identificación
            $longitud=10;
            $key = '';
            $pattern = '1234567890';
            $max = strlen($pattern)-1;
            for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
            /* ******************************* */
       
            $this->identificacion="1".$key;
            $this->clave = md5($this->identificacion);

            $datos= json_decode($json,true);

            $query1 = "SELECT * FROM " . $this->table2 ;
            $resultado=parent::listar($query1);
            $periodo_actual=$resultado[0]["periodo_actual"];
            $periodo_campana=$resultado[0]["periodo_campana"];
            

            if(!isset($datos["nombre"]) || !isset($datos["correo"]) || !isset($datos["celular"]) || !isset($datos["fo_programa"])){
                    return $_respuestas->error_400();
            }else{
                   
                    
                    $this->nombre=$datos["nombre"];
                    $this->correo=$datos["correo"];
                    $this->celular=$datos["celular"];
                    $this->fo_programa=$datos["fo_programa"];

                    

                $query ="INSERT INTO " . $this->table . " (identificacion,fo_programa,nombre,celular,email,clave,periodo_ingreso,fecha_ingreso,hora_ingreso,medio,estado,periodo_campana) 
                values ('". $this->identificacion."','". $this->fo_programa."','". $this->nombre."','". $this->celular."','". $this->correo."','". $this->clave."','". $periodo_actual."','". $fecha."','". $hora."','". $this->medio."','". $this->estado."','". $periodo_campana."') ";
                
                $resp = parent::nonQueryId($query);
                if($resp){

                    $query3 ="INSERT INTO " . $this->table3 . " (id_estudiante) values ('". $resp."') ";
                    parent::nonQueryId($query3);

                    return $resp;
                }
                else{
                    return 0;
                }
            }

        }


       

    }

    

}
?>