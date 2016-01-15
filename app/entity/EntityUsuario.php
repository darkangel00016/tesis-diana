<?php
class EntityUsuario extends CoreORM
{
    public static $prefix = "";
    protected $dbTable = "usuario";
    protected $primaryKey = "id";

    protected $dbFields = Array(
        'id' => Array('int'),
        'usuario' => Array('text', 'required'),
        'passusuario' => Array('text'),
        'tipo' => Array('text', 'required'),
        'nombre' => Array('text', 'required'),
        'ultimo_inicio_sesion' => Array('int'),
        'creado_por' => Array('int'),
        'fecha_creacion' => Array('int'),
        'modificado_por' => Array('int'),
        'fecha_modificacion' => Array('int'),
        'estado' => Array('int')
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


    public function login($opciones = array()) {
        $where = "";
        // select all active users
        if(sizeof($opciones) > 0) {
            $w = CoreRoute::getWhere($opciones);
            if(sizeof($w) > 0) {
                $where = " WHERE " . implode(" AND ", $w);
            }
        }
        $db = CoreDb::getInstance();
        $result = $db->query("SELECT * FROM ".$this->dbTable . $where, 1);
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
    public function find($id)
    {
        $db = CoreDb::getInstance();
        $result = $db->query("SELECT * FROM ".$this->dbTable . " WHERE id = '".$id."'", 1);
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

    public function getUsuario() {
        return $this->data["usuario"];
    }

    public function getPassusuario() {
        return @$this->data["passusuario"];
    }

    public function getTipo() {
        return $this->data["tipo"];
    }

    public function getNombre() {
        return $this->data["nombre"];
    }

    public function getUltimoInicioSesion() {
        return $this->data["ultimo_inicio_sesion"];
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

    public function getEstado() {
        return $this->data["estado"];
    }
}