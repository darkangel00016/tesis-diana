<?php
class ModuleConfiguracionEntityZonaUsuario extends CoreORM
{
    public static $prefix = "";
    public $dbTable = "usuario_zona";
    public $primaryKey = "id";

    public $dbFields = Array(
        'id' => Array('text'),
        'zona' => Array('int', 'required'),
        'usuario' => Array('int', 'required')
    );

    public $relations = Array(
        'zona' => Array("hasOne", "ModuleConfiguracionEntityZona", 'zona'),
        'usuario' => Array("hasOne", "ModuleUserEntityUsuario", 'usuario')
    );

    /**
     * ModuleConfiguracionEntityZona constructor.
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

    public function getZona() {
        return $this->data["zona"];
    }

    public function getUsuario() {
        return $this->data["usuario"];
    }

    public function deleteBy($opciones = array())
    {
        $db = CoreDb::getInstance();
        // select all active users
        if(sizeof($opciones) > 0) {
            foreach ($opciones as $value) {
                $db->where($value["field"], $value["value"], $value["comparacion"]);
            }
        }
        return $db->delete($this->dbTable);
    }

}