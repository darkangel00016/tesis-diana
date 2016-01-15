<?php

/**
 * Class EntityEstudiante
 */
class EntityEstudiante extends CoreORM
{

    public static $prefix = "";
    protected $dbTable = "estudiante";
    protected $primaryKey = "id";

    protected $dbFields = Array(
        'id' => Array('int'),
        'nombres' => Array('text', 'required'),
        'apellidos' => Array('text'),
        'correo' => Array('text', 'required'),
        'cedula' => Array('text', 'required'),
        'estado' => Array('int'),
        'creado_por' => Array('int'),
        'fecha_creacion' => Array('int'),
        'modificado_por' => Array('int'),
        'fecha_modificacion' => Array('int')
    );

    /**
     * EntityUsuario constructor.
     * @param string $primaryKey
     */
    public function __construct($primaryKey = "", $data = array())
    {
        parent::__construct($data);
        if(!empty($primaryKey)) {
            $this->find($primaryKey);
        }
    }

    // setup method to get all active users
    public function find($id, $campo = "id")
    {
        $db = CoreDb::getInstance();
        $result = $db->query("SELECT * FROM ".$this->dbTable . " WHERE {$campo} = '".$id."'", 1);
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

    public function getId() {
        return @$this->data["id"];
    }

    /**
     * @return mixed
     */
    public function getNombres()
    {
        return $this->data["nombres"];
    }

    /**
     * @return mixed
     */
    public function getApellidos()
    {
        return $this->data["apellidos"];
    }

    /**
     * @return mixed
     */
    public function getCorreo()
    {
        return $this->data["correo"];
    }

    /**
     * @return mixed
     */
    public function getCedula()
    {
        return $this->data["cedula"];
    }

    /**
     * @return mixed
     */
    public function getEstado()
    {
        return $this->data["estado"];
    }

    /**
     * @return mixed
     */
    public function getCreadoPor()
    {
        return $this->data["creado_por"];
    }

    /**
     * @return mixed
     */
    public function getFechaCreacion()
    {
        return $this->data["fecha_creacion"];
    }

    /**
     * @return mixed
     */
    public function getModificadoPor()
    {
        return $this->data["modificado_por"];
    }

    /**
     * @return mixed
     */
    public function getFechaModificacion()
    {
        return $this->data["fecha_modificacion"];
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