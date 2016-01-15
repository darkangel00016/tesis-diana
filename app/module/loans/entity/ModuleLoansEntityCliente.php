<?php
class ModuleLoansEntityCliente extends CoreORM
{
    public static $prefix = "";
    public $dbTable = "clientes";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('int'),
        'nombres' => Array('text', 'required'),
        'apellidos' => Array('text'),
        'telefono' => Array('text', 'required'),
        'direccion' => Array('text', 'required'),
        'barrio' => Array('text', 'required'),
        'ciudad' => Array('text', 'required'),
        'observaciones' => Array('text'),
        'foto' => Array('text'),
        'estado' => Array('int'),
        'zona' => Array('int', 'required'),
        'creado_por' => Array('int'),
        'fecha_creacion' => Array('int'),
        'modificado_por' => Array('int'),
        'fecha_modificacion' => Array('int'),
        'rate' => Array('int'),
        'pay_in_time' => Array('int'),
        'pay_out_time' => Array('int'),
        'no_pay' => Array('int'),
        'pay_before_time' => Array('int'),
        'pays' => Array('int')
    );

    public $relations = Array(
        'zona' => Array("hasOne", "ModuleConfiguracionEntityZona", 'zona')
    );

    /**
     * EntityCliente constructor.
     * @param string $primaryKey
     */
    public function __construct($primaryKey = "", $data = array())
    {
        if(is_array($primaryKey)) {
            $data =  $primaryKey;
        }
        parent::__construct($data);
        if(!is_array($primaryKey) && !empty($primaryKey)) {
            $this->find($primaryKey);
        }
    }

    // setup method to get all active users
    public function find($id)
    {
        $result = $this->byId($id);
        $isExist = false;
        if($result) {
            $this->isNew = false;
            $isExist = true;
        }
        return $isExist;
    }

    // setup method to get all active users
    public function findAll($opciones = array(), $pagina = 1, $items_by_page = 20)
    {
        $db = CoreDb::getInstance();
        $where = "";
        // select all active users
        if(sizeof($opciones) > 0) {
            $w = CoreRoute::getWhere($opciones);
            if(sizeof($w) > 0) {
                $where = " WHERE " . implode(" AND ", $w);
            }
        }
        $result = $db->query("SELECT count(c.id) as total FROM ".$this->dbTable . " c INNER JOIN zona z ON c.zona=z.id" . $where, 1);
        $total = $result[0]["total"];
        $result = $db->query("SELECT c.*, z.zona as zona_nombre FROM ".$this->dbTable . " c INNER JOIN zona z ON c.zona=z.id" . $where . (($items_by_page >0)?" LIMIT ".(($pagina -1) * $items_by_page)."," . $items_by_page:""));
        return array(
            "records" => $result,
            "total" => $total
        );
    }

    public function getId() {
        return @$this->data["id"];
    }

    public function getNombres() {
        return $this->data["nombres"];
    }

    public function getApellidos() {
        return @$this->data["apellidos"];
    }

    public function getTelefono() {
        return $this->data["telefono"];
    }

    public function getDireccion() {
        return $this->data["direccion"];
    }

    public function getBarrio() {
        return $this->data["barrio"];
    }

    public function getCiudad() {
        return $this->data["ciudad"];
    }

    public function getObservaciones() {
        return $this->data["observaciones"];
    }

    public function getFoto() {
        return $this->data["foto"];
    }

    public function getEstado() {
        return $this->data["estado"];
    }

    public function getZona() {
        return $this->data["zona"];
    }

    public function getCreadoPor() {
        return $this->data["creado_por"];
    }

    public function getFechaCreacion() {
        return $this->data["fecha_creacion"];
    }

    public function getModificadoPor() {
        return $this->data["modificado_por"];
    }

    public function getFechaModificacion() {
        return $this->data["fecha_modificacion"];
    }
}