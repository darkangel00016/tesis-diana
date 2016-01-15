<?php
class ModuleLoansEntityCuota extends CoreORM
{
    public static $prefix = "";
    public $dbTable = "cuotas";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('int'),
        'prestamo' => Array('int', 'required'),
        'fecha' => Array('text', 'required'),
        'monto' => Array('text', 'required'),
        'interes' => Array('text', 'required'),
        'estado' => Array('int')
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
        $result = $db->query("SELECT count(id) as total FROM ".$this->dbTable . $where, 1);
        $total = $result[0]["total"];
        $result = $db->query("SELECT * FROM ".$this->dbTable . $where . (($items_by_page >0)?" LIMIT ".(($pagina -1) * $items_by_page)."," . $items_by_page:""));
        return array(
            "records" => $result,
            "total" => $total
        );
    }

    public function getId() {
        return @$this->data["id"];
    }

    public function getPrestamo() {
        return $this->data["prestamo"];
    }

    public function getMonto() {
        return @$this->data["monto"];
    }

    public function getInteres() {
        return $this->data["interes"];
    }

    public function getFecha() {
        return $this->data["fecha"];
    }

    public function getEstado() {
        return $this->data["estado"];
    }

}