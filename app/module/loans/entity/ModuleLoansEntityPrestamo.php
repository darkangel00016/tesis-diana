<?php
class ModuleLoansEntityPrestamo extends CoreORM
{
    public static $prefix = "";
    public $dbTable = "prestamo";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('int'),
        'cliente' => Array('int', 'required'),
        'monto' => Array('text', 'required'),
        'interes' => Array('text', 'required'),
        'tipo_intervalo' => Array('text', 'required'),
        'intervalo' => Array('text', 'required'),
        'fecha_inicio' => Array('text', 'required'),
        'fecha_fin' => Array('text', 'required'),
        'monto_interes' => Array('text', 'required'),
        'saldo' => Array('text', 'required'),
        'estado' => Array('int', 'required'),
        'observaciones' => Array('text'),
        'creado_por' => Array('int'),
        'fecha_creacion' => Array('int'),
        'modificado_por' => Array('int'),
        'fecha_modificacion' => Array('int')
    );

    public $relations = Array(
        'cliente' => Array("hasOne", "ModuleLoansEntityCliente", 'cliente'),
        'cuotas' => Array("hasMany", "ModuleLoansEntityCuota", 'prestamo')
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
        $result = $this->with("cuotas")->byId($id);
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
        $result = $db->query("SELECT count(p.id) as total FROM ".$this->dbTable . " p INNER JOIN clientes c ON p.cliente = c.id" . $where, 1);
        $total = $result[0]["total"];
        $result = $db->query("SELECT p.*, c.nombres, c.apellidos FROM ".$this->dbTable . " p INNER JOIN clientes c ON p.cliente = c.id" . $where . (($items_by_page >0)?" LIMIT ".(($pagina -1) * $items_by_page)."," . $items_by_page:""));
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

    public function getMonto() {
        return @$this->data["monto"];
    }

    public function getInteres() {
        return $this->data["interes"];
    }

    public function getTipoIntervalo() {
        return $this->data["tipo_intervalo"];
    }

    public function getIntervalo() {
        return $this->data["intervalo"];
    }

    public function getFechaInicio() {
        return $this->data["fecha_inicio"];
    }

    public function getFechaFin() {
        return $this->data["fecha_fin"];
    }

    public function getObservaciones() {
        return $this->data["observaciones"];
    }

    public function getMontoInteres() {
        return $this->data["monto_interes"];
    }

    public function getSaldo() {
        return $this->data["saldo"];
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