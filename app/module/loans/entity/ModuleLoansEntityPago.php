<?php
class ModuleLoansEntityPago extends CoreORM
{
    public static $prefix = "";
    public static $table = "pago";
    public $dbTable = "pago";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('int'),
        'cliente' => Array('int', 'required'),
        'prestamo' => Array('int', 'required'),
        'cobrador' => Array('int', 'required'),
        'monto' => Array('text', 'required'),
        'fecha' => Array('text', 'required'),
        'metodo' => Array('int', 'required'),
        'observaciones' => Array('text'),
        'estado' => Array('int', 'required'),
        'creado_por' => Array('int'),
        'fecha_creacion' => Array('int'),
        'modificado_por' => Array('int'),
        'fecha_modificacion' => Array('int')
    );

    public $relations = Array(
        'cliente' => Array("hasOne", "ModuleLoansEntityCliente", 'cliente'),
        'prestamo' => Array("hasOne", "ModuleLoansEntityPrestamo", 'prestamo'),
        'metodo' => Array("hasOne", "ModuleConfiguracionEntityMetodo", 'metodo'),
        'cobrador' => Array("hasOne", "ModuleUserEntityUsuario", 'cobrador'),
        'items' => Array("hasmany", "ModuleLoansEntityPagoItems", 'pago')
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
        $result = $this->with("items")->byId($id);
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
        $result = $db->query("SELECT count(p.id) as total FROM ".$this->dbTable . " p INNER JOIN clientes c ON p.cliente = c.id INNER JOIN " . ModuleUserEntityUsuario::$table . " u ON p.cobrador = u.id" . $where, 1);
        $total = $result[0]["total"];
        $result = $db->query("SELECT p.*, c.nombres, c.apellidos, u.nombre FROM ".$this->dbTable . " p INNER JOIN clientes c ON p.cliente = c.id INNER JOIN ". ModuleUserEntityUsuario::$table . " u ON p.cobrador = u.id" . $where . (($items_by_page >0)?" LIMIT ".(($pagina -1) * $items_by_page)."," . $items_by_page:""));
        return array(
            "records" => $result,
            "total" => $total
        );
    }

    public function getId() {
        return @$this->data["id"];
    }

    public function getCliente() {
        return $this->data["cliente"];
    }

    public function getPrestamo() {
        return $this->data["prestamo"];
    }

    public function getCobrador() {
        return $this->data["cobrador"];
    }

    public function getMetodo() {
        return $this->data["metodo"];
    }

    public function getMonto() {
        return @$this->data["monto"];
    }

    public function getFecha() {
        return $this->data["fecha"];
    }

    public function getObservaciones() {
        return $this->data["observaciones"];
    }

    public function getEstado() {
        return $this->data["estado"];
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