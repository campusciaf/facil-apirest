<?php

require_once 'clases/respuestas.class.php';
require_once 'clases/usuarios.class.php';
header("Access-Control-Allow-Origin: *");// quita el bloqueo cros 
header("Access-Control-Allow-Methods: PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, autorizacion, X-Requested-With, Content-Type, Accept, Access-Control-Request-MethodAccess-Control-Allow-Headers,Authorization");
header('content-type: application/json');


$_respuestas = new respuestas;
$_usuarios = new usuarios;

if($_SERVER["REQUEST_METHOD"] == "GET"){
  
    if(isset($_GET["page"])){//metodo para listar los datos
        $pagina=$_GET["page"];
        $datousuarios = $_usuarios->listarUsuarios($pagina);
        header('Content-Type: application/json');
        echo json_encode($datousuarios);
        http_response_code(200);
    }
    else if(isset($_GET['id'])){//metodo para obterner un dato por id
        $id_user=$_GET["id"];
        $datoUsuario = $_usuarios->obtenerUsuario($id_user);
        header('Content-Type: application/json');
        echo json_encode($datoUsuario);
        http_response_code(200);
    }

    

}else if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $postBody= file_get_contents("php://input");// recibimos los datos enviados del formulario
    $datosArray = $_usuarios->formularioUsuario($postBody);// enviamos esto al manejador

    header('Content-Type: application/json');// devolvemos una respuesta

        if(isset($datosArray["result"]["error_id"])){
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        }else{
            http_response_code(200);
        }
        echo json_encode($datosArray);

}else if($_SERVER["REQUEST_METHOD"] == "PUT"){

    $postBody= file_get_contents("php://input");// recibimos los datos enviados del formulario
    $datosArray = $_usuarios->formularioEditarUsuario($postBody);// enviamos esto al manejador
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

    
    $datosArray = $_usuarios->formularioBorrarUsuario($postBody);// enviamos esto al manejador
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