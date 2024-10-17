<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/menuNav.class.php';
header("Access-Control-Allow-Origin: *");// quita el bloqueo cros 
header("Access-Control-Allow-Methods: PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, autorizacion, X-Requested-With, Content-Type, Accept, Access-Control-Request-MethodAccess-Control-Allow-Headers,Authorization");
header('content-type: application/json');


$_respuestas =new respuestas;
$_sentencia =new menuNav;

if($_SERVER["REQUEST_METHOD"] == "GET"){

    if(isset($_GET['id']) && $_GET['valor']=='1'){//metodo para obterner un dato por id
        $id=$_GET["id"];
        $datoproveedores = $_sentencia->obtenerDatos($id);
        header('Content-Type: application/json');
        echo json_encode($datoproveedores);
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