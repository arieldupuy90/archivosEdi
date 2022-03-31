<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Http\Controllers\Edisoft;

/**
 * Description of UsuarioController
 *
 * @author adupuy
 */
class UsuarioController extends \App\Http\Controllers\Controller {
    //put your code here
    
    //put your code here
    public function obtener() {
        
        $usuarios = \Usuario::select();

        $respuesta = [];
        foreach ($usuarios as $row) {
//            var_dump($row);
            $usuario = new \Usuario($row);
            $linea["nombre"] = $usuario->get_nombre_completo();
            $linea["id"] = $usuario->get_id();
            $linea["username"] = $usuario->get_nombre_usuario();
            $linea["email"] = $usuario->get_email();
            $linea["activo"] = $usuario->get_id_authstat();
            $fecha = \DateTime::createFromFormat("Y-m-d H:i:s",$usuario->get_last_login());
            $linea["ultimo_login"] = $fecha->format("Y-m-d H:i:s");
            $respuesta[] = $linea;
        }

        return $this->retornar(self::RESPUESTA_CORRECTA, "", $respuesta);
    }

    public function cambiar_estado_post() {
        $usuario = new \Usuario();
        $usuario->get(self::$variables["id"]);
        if ($usuario->get_id_authstat() == \Authstat::ACTIVO) {
            $usuario->set_id_authstat(\Authstat::INACTIVO);
        } elseif ($usuario->get_id_authstat() == null OR $usuario->get_id_authstat() == \Authstat::INACTIVO) {
            $usuario->set_id_authstat(\Authstat::ACTIVO);
        }
        if ($usuario->set()) {
            return $this->retornar(self::RESPUESTA_CORRECTA, "", ["id" => $usuario->get_id_authstat()]);
        }
        return $this->retornar(self::RESPUESTA_INCORRECTA, "Error al cambiar estado", ["resultado" => "not-ok"]);
    }

    public function crear_usuario_post() {
        /* No me gusta mezclar controladores ya que son dos capaz iguales, seria mejor pasar la logica a un trait */
        $persona = new \App\Http\Controllers\Backoffice\PersonaJuridicaController();

        $params["id_cuenta"] = self::$CUENTA->get_id();
        $params["documento"] = self::$variables["documento"];
        $params["email"] = self::$variables["email"];
        $params["nombre"] = self::$variables["nombre"];
        $params["nombre_completo"] = self::$variables["nombre"];
        $params["titular"] = self::$variables["nombre"];
        $params["celular"] = self::$variables["telefono"];
        $params["cod_pais"] = self::$variables["cod_pais"];
        $params["codArea"] = $params["cod_pais"] . self::$variables["codArea"];
        $params["tipodoc"] = self::$variables["tipodoc"];
        if(self::$variables["tipodoc"]=="DNI"){
            $documento=self::$variables["documento"];
        }
        else{
            $documento= substr(self::$variables["documento"], 2,strlen(self::$variables["documento"])-3);
        }
        
        $rs_usuario = \Usuario::select_busqueda_cuenta($params["email"], $documento, self::$CUENTA->get_id());
        if ($rs_usuario and $rs_usuario->fetchRow() > 0) {
//            throw new \Exception("Ya existe este usuario");
            $response = $persona->asociar_usuario(new \Usuario($rs_usuario->fetchRow()),self::$CUENTA->get_id());
            $response = $response->getData(true);
            $id_cuenta_usuario = $response["id_cuenta_usuario"];
        }
        else{
            $response = $persona->crear_usuario_post($params);
            $resp = self::RESPUESTA_INCORRECTA;
            if ($response) {
                $response = $response->getData(true);
                $id_cuenta_usuario = $response["id_cuenta_usuario"];
                if ($response["resultado"]) {
                    $response = $persona->generar_url_post("usuario", $response["id"], true, true);
                    $resp = self::RESPUESTA_CORRECTA;
                }
            }
        }
        \Gestor_de_notificaciones::notificar_y_guardar(self::$CUENTA->get_id_cuenta(), self::$USUARIO->get_nombre_usuario()." Generó un nuevo usuario para la cuenta ".self::$CUENTA->get_titular(), "Nuevo usuario generado", "usuarios");
            $view=new \Vista();
            $view->cargar("views/mail_avisos.html");
            $usuario=$view->getElementById("usuario");
            $usuario->appendChild($view->createTextNode(self::$CUENTA->get_titular()));
            $mensaje=$view->getElementById("mensaje");
            $mensaje->appendChild($view->createTextNode(self::$USUARIO->get_nombre_usuario()." Generó un nuevo usuario para la cuenta ".self::$CUENTA->get_titular()));
            $usser = new \Usuario();
            $usser ->get(self::$CUENTA->get_id_usuario_titular());
            \Gestor_de_correo::enviar(\Gestor_de_correo::MAIL_COBRODIGITAL_ATENCION_AL_CLIENTE, $usser ->get_email(), "Nuevo usuario generado", $view->saveHTML());
        return $this->retornar($resp, $response["msg"], ["msg" => $response["msg"], "id_cuenta_usuario" => $id_cuenta_usuario]);
    }

    public function obtener_permisos() {

        $rs = \Usuario_menu::select_menu(self::$CUENTA_USUARIO->get_id());
        $respuesta = array();
        foreach ($rs as $row) {
            developer_log(\GuzzleHttp\json_encode($row));
            $elemento = new \Elemento_menu();
            $elemento->get($row["id_elemento_menu"]);
            $fila["nombre"] = $elemento->get_nombre();
            $fila["id"] = $elemento->get_id();
            $fila["grupo"] = $elemento->get_grupo();
            $fila["icono"] = $elemento->get_icono();
            $fila["ruta"] = $elemento->get_ruta();
            $respuesta[] = $fila;
        }
        if (count($respuesta) > 0)
            return $this->retornar(self::RESPUESTA_CORRECTA, "", $respuesta);
        return $this->retornar(self::RESPUESTA_INCORRECTA, "El usuario no tiene permisos", []);
    }

    public function obtener_submodulos_post() {
        $rs = \Elemento_menu::select(["id_padre" => self::$variables["id_elemento_menu"]]);
//        $rs = \Elemento_menu::select();

        $respuesta = array();
        foreach ($rs as $row) {
            $elemento = new \Elemento_menu();
            $elemento->get($row["id_elemento_menu"]);
            $fila["nombre"] = $elemento->get_nombre();
            $fila["id"] = $elemento->get_id();
            $fila["grupo"] = $elemento->get_grupo();
            $fila["icono"] = $elemento->get_icono();
            $fila["ruta"] = $elemento->get_ruta();
            $respuesta[] = $fila;
        }
        if (count($respuesta) > 0)
            return $this->retornar(self::RESPUESTA_CORRECTA, "", $respuesta);
        return $this->retornar(self::RESPUESTA_INCORRECTA, "El usuario no tiene permisos", []);
    }

    public function obtener_permisos_post() {
//        var_dump(self::$variables);
        $cuenta_usuario = new \Cuenta_usuario();
//        var_dump(self::$variables);

        if (!isset(self::$variables["id"])) {
            throw new \Exception("Falta el parametro id");
        }
        $cuenta_usuario->get(self::$variables["id"]);
        if (!$cuenta_usuario OR $cuenta_usuario->get_id() == null) {
            throw new \Exception("El usuario no esta asignado a la cuenta");
        }
        $rs = \Usuario_menu::select(["id_cuenta_usuario" => $cuenta_usuario->get_id(), "id_authstat" => \Authstat::ACTIVO]);
        $respuesta = array();
        foreach ($rs as $row) {
//            var_dump($row);
            $elemento = new \Elemento_menu();
            $elemento->get($row["id_elemento_menu"]);
            $fila["nombre"] = $elemento->get_nombre();
            $fila["id"] = $elemento->get_id();
            $fila["grupo"] = $elemento->get_grupo();
            $fila["icono"] = $elemento->get_icono();
            $fila["ruta"] = $elemento->get_ruta();
            $respuesta[] = $fila;
        }
        if (count($respuesta) > 0)
            return $this->retornar(self::RESPUESTA_CORRECTA, "", $respuesta);
        return $this->retornar(self::RESPUESTA_INCORRECTA, "El usuario no tiene permisos", []);
    }

    public function setear_permisos_post() {
        $cuenta_usuario = new \Cuenta_usuario();
        $cuenta_usuario->get(self::$variables["usuario"]["id"]);
        if ($cuenta_usuario->get_id_usuario() == self::$USUARIO->get_id()) {
            throw new \Exception("No puede editar sus propios permisos");
        }
        $options = self::$variables["options"];
        $submodulos = self::$variables["submodulos"];
        \Model::StartTrans();
        \Usuario_menu::update($cuenta_usuario->get_id());
//        var_dump(self::$variables["permisos"]);
        foreach (self::$variables["permisos"] as $row) {
            $permiso = new \Elemento_menu();
            $permiso->get($row["id_elemento_menu"]);
            $rs = \Usuario_menu::select(["id_cuenta_usuario" => $cuenta_usuario->get_id(), "id_elemento_menu" => $permiso->get_id()]);
            
            if ($options[$row["id_elemento_menu"]] OR $this->in_submodulos($submodulos, $row["id_elemento_menu"])) {
                /* activacion */
                if (!$rs or $rs->rowCount() == 0) {
                    developer_log("no existe");
                    $menu = new \Usuario_menu();
                    $menu->set_id_cuenta_usuario($cuenta_usuario->get_id());
                    $menu->set_id_elemento_menu($permiso->get_id());
                    $menu->set_id_authstat(\Authstat::ACTIVO);
                    if (!$menu->set()) {
                        developer_log("Error al setear");
                        \Model::FailTrans();
                    } else {
                        developer_log("Seteado");
                    }
                } else {
                    $menu = new \Usuario_menu($rs->fetchRow());
                    $menu->set_id_authstat(\Authstat::ACTIVO);
                    if (!$menu->set()) {
                        developer_log("Error al setear");
                        \Model::FailTrans();
                    } else {
                        developer_log("Seteado");
                    }
                }
            } else {
                /* desactivacion */
                if ($rs and $rs->rowCount() > 0) {
                    $r = $rs->fetchRow();
                    $menu = new \Usuario_menu($r);
                    $menu->set_id_authstat(\Authstat::INACTIVO);
                    if (!$menu->set()) {
                        developer_log("Error al setear");
                        \Model::FailTrans();
                    } else {
                        developer_log("Seteado");
                    }
                }
            }
        }
//        return setcookie($GLOBALS['COOKIE_NAME'], null, -1, '/');
        \Gestor_de_cookies::set_cookie("menu", null);
//        developer_log(\Model::HasFailedTrans());
        $usser = new \Usuario();
        $usser ->get($cuenta_usuario->get_id_usuario());
        if (!\Model::HasFailedTrans() and \Model::CompleteTrans()) {
            \Gestor_de_notificaciones::notificar_y_guardar($usser->get_id_cuenta(), "Se efectuaron cambios en tus permisos para el usuario.".$usser->get_nombre_usuario(), "Cambios en tu usuario", "usuarios", 1);
            $view=new \Vista();
            $view->cargar("views/mail_avisos.html");
            $usuario=$view->getElementById("usuario");
            $usuario->appendChild($view->createTextNode(self::$CUENTA->get_titular()));
            $mensaje=$view->getElementById("mensaje");
            $mensaje->appendChild($view->createTextNode("Se efectuaron cambios en tus permisos para el usuario: .".$usser->get_nombre_usuario()));
            $usser = new \Usuario();
            $usser ->get($cuenta_usuario->get_id_usuario());
            \Gestor_de_correo::enviar(\Gestor_de_correo::MAIL_COBRODIGITAL_ATENCION_AL_CLIENTE, $usser ->get_email(), "Cambios en tu usuario", $view->saveHTML());
            return $this->retornar(self::RESPUESTA_CORRECTA, "Permisos seteados", []);
        }
        return $this->retornar(self::RESPUESTA_INCORRECTA, "No se pudo setear el permiso", []);
    }

    private function in_submodulos($submodulos, $id_elemento_menu) {
        $modulos = [];
        foreach ($submodulos as $clave => $row) {
            if ($row != null) {
                foreach ($row as $c => $r)
                    if ($r != null) {
                        $modulos[$c] = $r;
                    }
            }
        }
        foreach ($modulos as $clave => $submodulo) {
            if ($clave == $id_elemento_menu) {
                return true;
            }
        }
        return false;
    }

    public function reenviar_url_post() {
//        var_dump(self::$variables);
        $usuario_cuenta = new \Cuenta_usuario();
        $usuario_cuenta->get(self::$variables["usuario"]["id"]);
        $persona = new \App\Http\Controllers\Backoffice\PersonaJuridicaController();
        $respuesta = $persona->generar_url_post("usuario", $usuario_cuenta->get_id_usuario(), true);
        return $this->retornar(self::RESPUESTA_CORRECTA, "Correo enviado", $respuesta);
    }
}