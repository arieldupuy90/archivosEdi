<?php
// namespace Models;
class Entidad extends Model{


  public static $prefijo_tabla='ho_';
  public static $id_tabla='id_entidad';
  public static $secuencia=GENERAR_ID_MAXIMO;

  
  const ENTIDAD_CUENTA = 1;
  const ENTIDAD_TOKEN= 2;
  const ENTIDAD_TRANSACCION = 3;
  const ENTIDAD_SALDO = 4;
  const ENTIDAD_DEBIN_CD = 5;
  const ENTIDAD_TRANSFERENCIA_CVU_RECIBIDA = 6;
  const ENTIDAD_TRANSFERENCIA_CVU_ENVIADA= 8;
  const ENTIDAD_DESTINATARIO = 7;
  const ENTIDAD_TARJETA =20;
  private $id_entidad;
  private $entidad;
  private $dentidad;
  private $descripcion;
  private $is_productable;
  private $accept_wse;
  private $modelised;
  private $colorsplit;
  private $ikona;
  private $contenido;
  private $unfiltro;
  private $cannotdup;
  private $groupedorden;

  public function get_id_entidad(){ return $this->id_entidad;}
  public function get_entidad(){ return $this->entidad;}
  public function get_dentidad(){ return $this->dentidad;}
  public function get_descripcion(){ return $this->descripcion;}
  public function get_is_productable(){ return $this->is_productable;}
  public function get_accept_wse(){ return $this->accept_wse;}
  public function get_modelised(){ return $this->modelised;}
  public function get_colorsplit(){ return $this->colorsplit;}
  public function get_ikona(){ return $this->ikona;}
  public function get_contenido(){ return $this->contenido;}
  public function get_unfiltro(){ return $this->unfiltro;}
  public function get_cannotdup(){ return $this->cannotdup;}
  public function get_groupedorden(){ return $this->groupedorden;}

  public function set_id_entidad($variable){ $this->id_entidad=$variable; return $this->id_entidad;}
  public function set_entidad($variable){ $this->entidad=$variable; return $this->entidad;}
  public function set_dentidad($variable){ $this->dentidad=$variable; return $this->dentidad;}
  public function set_descripcion($variable){ $this->descripcion=$variable; return $this->descripcion;}
  public function set_is_productable($variable){ $this->is_productable=$variable; return $this->is_productable;}
  public function set_accept_wse($variable){ $this->accept_wse=$variable; return $this->accept_wse;}
  public function set_modelised($variable){ $this->modelised=$variable; return $this->modelised;}
  public function set_colorsplit($variable){ $this->colorsplit=$variable; return $this->colorsplit;}
  public function set_ikona($variable){ $this->ikona=$variable; return $this->ikona;}
  public function set_contenido($variable){ $this->contenido=$variable; return $this->contenido;}
  public function set_unfiltro($variable){ $this->unfiltro=$variable; return $this->unfiltro;}
  public function set_cannotdup($variable){ $this->cannotdup=$variable; return $this->cannotdup;}
  public function set_groupedorden($variable){ $this->groupedorden=$variable; return $this->groupedorden;}
  public static function obtener_class($id_entidad){
      $entidad = new self();
      $entidad->get($id_entidad);
      $class='\\'. ucfirst(substr($entidad->get_entidad(), strlen(self::$prefijo_tabla), strlen($entidad->get_entidad())));
      $object= new $class();
      return $object;
  }
}







