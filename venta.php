<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/venta.class.php';
header("Access-Control-Allow-Origin: *");// quita el bloqueo cros 
header("Access-Control-Allow-Methods: PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, autorizacion, X-Requested-With, Content-Type, Accept, Access-Control-Request-MethodAccess-Control-Allow-Headers,Authorization");
header('content-type: application/json');


$_respuestas =new respuestas;
$_venta =new venta;

if($_SERVER["REQUEST_METHOD"] == "GET"){

    if(isset($_GET["listar"])){//metodo para listar los datos
        $datoVenta= $_venta->listarVentas();
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }
    if(isset($_GET["listarEntrega"])){//metodo para listar los datos
        $id_venta=$_GET["listarEntrega"];
        $datoVenta= $_venta->listarEntrega($id_venta);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }

    else if(isset($_GET["verificar"])){//metodo para listar los datos
        $iduser=$_GET["verificar"];
        $datoVenta= $_venta->verificarVenta($iduser);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }

    else if(isset($_GET["crear"])){//metodo para listar los datos
        $iduser=$_GET["crear"];
        $datoVenta= $_venta->crearCompra($iduser);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }
    else if(isset($_GET["traer"])){//metodo para listar los datos
        $id_venta=$_GET["traer"];
        $datoVenta= $_venta->traerVenta($id_venta);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }

    if(isset($_GET["listarDetalle"])){//metodo para listar los datos
        $id_venta=$_GET["listarDetalle"];
        $datoVenta= $_venta->listarVentaDetalle($id_venta);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }

    if(isset($_GET["estadoAlistado"])){//metodo para listar los datos
        $id=$_GET["estadoAlistado"];
        $estado=$_GET["estado"];
        $id_venta=$_GET["id_venta"];

        $datoVenta= $_venta->estadoAlistado($id,$estado,$id_venta);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }
    if(isset($_GET["estadoEntrega"])){//metodo para listar los datos
        $id=$_GET["estadoEntrega"];

        $datoVenta= $_venta->estadoEntrega($id);
        header('Content-Type: application/json');
        echo json_encode($datoVenta);
        http_response_code(200);
    }

    



   

}else if($_SERVER["REQUEST_METHOD"] == "POST"){

    // recibimos los datos enviados
    // recibir datos
    $postBody= file_get_contents("php://input");
    //envamos datos al manejador
    $datosArray=$_venta->registroVenta($postBody);

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
    $datosArray = $_venta->terminarVenta($postBody);// enviamos esto al manejador
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

    
    $datosArray = $_venta->formularioBorrarVenta($postBody);// enviamos esto al manejador
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