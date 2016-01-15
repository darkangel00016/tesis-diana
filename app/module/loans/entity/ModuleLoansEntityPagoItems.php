<?php
class ModuleLoansEntityPagoItems extends CoreORM
{
    public static $prefix = "";
    public $dbTable = "pago_items";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('int'),
        'pago' => Array('int', 'required'),
        'origen' => Array('text'),
        'documento' => Array('text', 'required'),
        'monto' => Array('text', 'required')
    );

    /**
     * @var ModuleLoansEntityCuota
     */
    public $document_origin;

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
        if(is_array($primaryKey) && $this->origen == "cuota") {
            $this->document_origin = new ModuleLoansEntityCuota($this->documento);
        }
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
            if($this->origen == "cuota") {
                $this->document_origin = new ModuleLoansEntityCuota($this->documento);
            }
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

    public function getPago() {
        return $this->data["pago"];
    }

    public function getMonto() {
        return @$this->data["monto"];
    }

    public function getOrigen() {
        return $this->data["origen"];
    }

    public function getDocumento() {
        return $this->data["documento"];
    }

}