<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/compra.class.php';
header("Access-Control-Allow-Origin: *");// quita el bloqueo cros 
header("Access-Control-Allow-Methods: PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, autorizacion, X-Requested-With, Content-Type, Accept, Access-Control-Request-MethodAccess-Control-Allow-Headers,Authorization");
header('content-type: application/json');


$_respuestas =new respuestas;
$_compra =new compra;

if($_SERVER["REQUEST_METHOD"] == "GET"){

    if(isset($_GET["verificar"])){//metodo para listar los datos
        $iduser=$_GET["verificar"];
        $datoCompra = $_compra->verificarCompra($iduser);
        header('Content-Type: application/json');
        echo json_encode($datoCompra);
        http_response_code(200);
    }

    else if(isset($_GET["crear"])){//metodo para listar los datos
        $iduser=$_GET["crear"];
        $datoCompra = $_compra->crearCompra($iduser);
        header('Content-Type: application/json');
        echo json_encode($datoCompra);
        http_response_code(200);
    }
    else if(isset($_GET["traer"])){//metodo para listar los datos
        $id_compra=$_GET["traer"];
        $datoCompra = $_compra->traerCompra($id_compra);
        header('Content-Type: application/json');
        echo json_encode($datoCompra);
        http_response_code(200);
    }


}else if($_SERVER["REQUEST_METHOD"] == "POST"){

    // recibimos los datos enviados
    // recibir datos
    $postBody= file_get_contents("php://input");
    //envamos datos al manejador
    $datosArray=$_compra->registroCompra($postBody);

    // devolvemos una respuesta
   
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);

}else if($_SERVER["REQUEST_METHOD"] == "PUT"){

    $postBody= file_get_contents("php://input");// recibimos los datos enviados del formulario
    $datosArray = $_compra->terminarCompra($postBody);// enviamos esto al manejador
    header('Content-Type: application/json');// devolvemos una respuesta

        if(isset($datosArray["result"]["error_id"])){
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        }else{
            http_response_code(200);
        }
        echo json_encode($datosArray);

}


else if($_SERVER["REQUEST_METHOD"] == "DELETE"){

    $headers = getallheaders();
    if(isset($headers["token"]) && isset($headers["id_user"])){// usamos esto en caso de que enviemos datos por header
        $send =[
            "token" => $headers["token"],
            "id_user" => $headers["id_user"]
        ];
        $postBody= json_encode($send);// recibimos los datos de los header
    }else{
        $postBody= file_get_contents("php://input");// recibimos los datos enviados del formulario
    }

    
    $datosArray = $_compra->formularioBorrarCOmpra($postBody);// enviamos esto al manejador
    header('Content-Type: application/json');// devolvemos una respuesta

        if(isset($datosArray["result"]["error_id"])){
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        }else{
            http_response_code(200);
        }
        echo json_encode($datosArray);

}

else{
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);

}

 ?>