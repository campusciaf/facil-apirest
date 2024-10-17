<?php

require_once 'clases/respuestas.class.php';
require_once 'clases/datosselects.class.php';
header("Access-Control-Allow-Origin: *");// quita el bloqueo cros 
header("Access-Control-Allow-Methods: PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, autorizacion, X-Requested-With, Content-Type, Accept, Access-Control-Request-MethodAccess-Control-Allow-Headers,Authorization");
header('content-type: application/json');


$_respuestas = new respuestas;
$_datosselects = new datosselects;

if($_SERVER["REQUEST_METHOD"] == "GET"){

   if(isset($_GET['id'])){//metodo para obterner un dato por id
        $id_user=$_GET["id"];
        $datoUsuario = $_datosselects->obtenerDatos($id_user);
        header('Content-Type: application/json');
        echo json_encode($datoUsuario);
        http_response_code(200);
    }

    

}else if($_SERVER["REQUEST_METHOD"] == "POST"){
    


}else if($_SERVER["REQUEST_METHOD"] == "PUT"){

  
}

else if($_SERVER["REQUEST_METHOD"] == "DELETE"){



}

else{
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);

}




?>