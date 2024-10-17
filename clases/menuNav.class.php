<?php
require_once 'conexion/crud.php';
require_once 'respuestas.class.php';

class menuNav extends ConexionCrud{


    private $table= "menu_nav";
    private $table2= "menu_nav_items";
    private $table3= "permiso";

    public function obtenerDatos($idnum){
        $data["data"] = "";
        $menu=array();
        $submenu=array();

        $query = "SELECT * FROM " . $this->table ;
        $resultado=parent::listar($query);

        for ($a = 0; $a < count($resultado) ; $a++) {

            $id_menu_nav=$resultado[$a]["id_menu_nav"];

            if($resultado[$a]["tipo"]=='1'){// quiere decir que es un boton sin submenu

                 $verificar = $this->verificarPermisoMenu($idnum,$resultado[$a]["id_menu_nav"]);
                
                if(count($verificar)>0){// si tiene permiso en el menu nav que agregue el boton

                    $nuevo_menu = array("label" => $resultado[$a]["label"], "icon" => $resultado[$a]["icon"], "expanded" => $resultado[$a]["expanded"],"routerLink" => $resultado[$a]["routerLink"]);
                    array_push($menu,$nuevo_menu);

                }

                    

            }else{// quiere decir que es un boton con submenu
                $submenu=array();
                $query2 = "SELECT * FROM " . $this->table2 ." WHERE id_menu_nav=$id_menu_nav";
                $resultado2=parent::listar($query2);

                

                for ($b = 0; $b < count($resultado2) ; $b++) {

                    $verificaritem = $this->verificarPermisoMenuItems($idnum,$resultado[$a]["id_menu_nav"],$resultado2[$b]["id_menu_nav_items"]);

                    if(count($verificaritem)>0){// si tiene permiso en el menu nav item para que agregue el boton
                        $nuevo_submenu = array("label" => $resultado2[$b]["label"], "icon" => $resultado2[$b]["icon"], "routerLink" => $resultado2[$b]["routerLink"]);
                        array_push($submenu,$nuevo_submenu);
                    }


                }

                $verificar = $this->verificarPermisoMenu($idnum,$resultado[$a]["id_menu_nav"]);
                
                if(count($verificar)>0){// si tiene permiso en el menu nav que agregue el boton

                    $nuevo_menu = array("label" => $resultado[$a]["label"], "icon" => $resultado[$a]["icon"], "expanded" => $resultado[$a]["expanded"], "items" => $submenu);
               
                    array_push($menu,$nuevo_menu);
                }
            }
            
        }

        return json_encode($menu);
       
    }

    private function verificarPermisoMenu($idnum,$id_menu_nav){
        $query = "SELECT * FROM " . $this->table3 ." WHERE id_menu_nav=$id_menu_nav AND id_usuario=$idnum";
        $resp = parent::listar($query);

            return $resp;
        
      
       
    }

    private function verificarPermisoMenuItems($idnum,$id_menu_nav,$id_menu_nav_items){

        $query = "SELECT * FROM " . $this->table3 ." WHERE id_menu_nav=$id_menu_nav AND id_usuario=$idnum AND id_menu_nav_items=$id_menu_nav_items ";
        $resp = parent::listar($query);
       
            return $resp;
        
     
    }





}
?>