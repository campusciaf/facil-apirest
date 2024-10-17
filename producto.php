<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/producto.class.php';
header("Access-Control-Allow-Origin: *");// quita el bloqueo cros 
header("Access-Control-Allow-Methods: PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, autorizacion, X-Requested-With, Content-Type, Accept, Access-Control-Request-MethodAccess-Control-Allow-Headers,Authorization");
header('content-type: application/json');


$_respuestas =new respuestas;
$_producto =new producto;

if($_SERVER["REQUEST_METHOD"] == "GET"){

    if(isset($_GET['id'])){//metodo para obterner un dato por id
        $id_producto=$_GET["id"];
        $datoproducto = $_producto->listarProducto($id_producto);
        header('Content-Type: application/json');
        echo json_encode($datoproducto);
        http_response_code(200);
    }
    if(isset($_GET['edit'])){//metodo para obterner un dato por edit
        $id_producto=$_GET["edit"];
        $datoproducto = $_producto->obtenerProductoId($id_producto);
        header('Content-Type: application/json');
        echo json_encode($datoproducto);
        http_response_code(200);
    }
    if(isset($_GET['listar'])){//metodo para obterner un dato por edit
        $datoproducto = $_producto->listarProductos();
        header('Content-Type: application/json');
        echo json_encode($datoproducto);
        http_response_code(200);
    }

    if(isset($_GET['listarDispo'])){//metodo para obterner un dato por edit
        $datoproducto = $_producto->listarProductosDisponibles();
        header('Content-Type: application/json');
        echo json_encode($datoproducto);
        http_response_code(200);
    }

    if(isset($_GET['listarDispoVentas'])){//metodo para obterner un dato por edit
        $datoproducto = $_producto->listarProductosDisponiblesVentas();
        header('Content-Type: application/json');
        echo json_encode($datoproducto);
        http_response_code(200);
    }


}else if($_SERVER["REQUEST_METHOD"] == "POST"){

    // recibimos los datos enviados
    // recibir datos
    $postBody= file_get_contents("php://input");
    //envamos datos al manejador
    $datosArray=$_producto->registroproducto($postBody);

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
    $datosArray = $_producto->formularioEditar($postBody);// enviamos esto al manejador
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

}

else{
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);

}

 ?>