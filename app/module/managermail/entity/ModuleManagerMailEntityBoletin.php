<?php

/**
 * Class ModuleManagerMailEntityBoletin
 */
class ModuleManagerMailEntityBoletin extends CoreORM
{

    public static $prefix = "";
    public $dbTable = "boletin";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('int'),
        'tipo' => Array('text', 'required'),
        'fecha_envio' => Array('text'),
        'contenido' => Array('text', 'required'),
        'from' => Array('text', 'required'),
        'subject' => Array('text', 'required'),
        'estado' => Array('int'),
        'creado_por' => Array('int'),
        'fecha_creacion' => Array('int'),
        'modificado_por' => Array('int'),
        'fecha_modificacion' => Array('int')
    );

    /**
     * ModuleManagerMailEntityBoletin constructor.
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

    public function findByField($value, $field)
    {
        $db = CoreDb::getInstance();
        $result = $db->query("SELECT * FROM ".$this->dbTable . " WHERE $field = '".$value."'", 1);
        $isExist = false;
        if($result) {
            foreach($this->dbFields as $key => $value) {
                $this->$key = $result[0][$key];
            }
            $isExist = true;
            $this->isNew = false;
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
        $result = $db->query("SELECT * FROM ".$this->dbTable . $where . " LIMIT ".(($pagina -1) * $items_by_page)."," . $items_by_page);
        return array(
            "records" => $result,
            "total" => $total
        );
    }

    public function id($data)
    {
        if(is_array($data) && sizeof($data) > 0) {
            foreach ($this->dbFields as $key => $value) {
                $this->$key = $data[$key];
            }
            $this->isNew = false;
        }
    }

}