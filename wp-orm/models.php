<?php
require_once dirname(__FILE__) . '/fields.php';
require_once 'wp-load.php';

class ModelManager {
   public $model;
   function __construct($model) {
       $this->model = $model;
   }
   function filter($pre_filters) {
       $filters = "";
       foreach($pre_filters as $key => $value) {
           $field_object = $this->model->_fields_by_attr[$key];
           $column_name = $field_object->get_column_name($key);
           $filters .= "_WP_MODEL_.`{$column_name}` = '$value', ";
       }
       $filters = substr($filters, 0, -2);
       $query = "SELECT * FROM `{$this->model->get_db_table()}` _WP_MODEL_ WHERE {$filters}";

       $rows = ModelManager::fetch_results($query);
       return $this->_results_to_models($rows);
   }
   function all() {
       $rows = ModelManager::fetch_results("SELECT * FROM `{$this->model->get_db_table()}`;");
       return $this->_results_to_models($rows);
   }
   function fetch_by_sqlend($sqlend) {
       $rows = ModelManager::fetch_results("SELECT * FROM `{$this->model->get_db_table()}` {$sqlend};");
       return $this->_results_to_models($rows);
   }

   public static function fetch_results ($query) {
       global $wpdb;
       $results = $wpdb->get_results($query, 'ARRAY_A');
       if (!$results) {
           $results = array();
       }
       return $results;
   }
   protected function _results_to_models ($rows) {
       $models = array();

       if (!$rows) {
           return $models;
       }
       $valid = false;
       foreach($rows as $index => $data) {
           $klass = get_class($this->model);
           $m = new $klass();
           foreach ($m->_field_names as $colname => $attrname) {
               foreach($data as $key => $value) {
                   if ($colname == $key) {
                       $m->$attrname = $value;
                       $valid = true;
                   }
               }
           }
           if ($valid) {
               $models[$index] = $m;

           }
       }

       return $models;
   }
}

class Model {
    public $objects;
    protected $_context = null;

    protected $db;
    protected $data;

    public $_fields = array();
    public $_field_names = array();
    public $_filled_fields = array();

    function __construct($data=null) {
        global $wpdb;
        $this->declaration();
        foreach(get_object_vars($this) as $key => $value) {
            if (is_object($value) && $value instanceof Field) {
                $name = $value->get_column_name($key);
                $value->set_model($this);
                $this->_fields[$name] = $value;
                $this->_fields_by_attr[$key] = $value;
                $this->_field_names[$name] = $key;
            }
        }
        $this->db = $wpdb;
        $this->data = $data;

        if ($data."" == "Array") {
            foreach($data as $key => $value) {
                if (in_array($key, $this->_field_names)) {
                    $this->$key = $value;
                    $field = $this->_fields[$key];
                    $field = $field ? $field : $this->_fields_by_attr[$key];
                    if (!$field) {
                        continue;
                    }
                    $this->_filled_fields[$key] = array(
                                                        'field_name' => $key,
                                                        'column_name' => $field->get_column_name($key),
                                                        'field' => $field,
                                                        'value' => $value,
                                                        );
                }
            }
        }

        $this->objects = new ModelManager($this);

    }

    public function prepare_to_save(){
        $fields = "";
        $values = "";
        foreach ($this->_filled_fields as $key => $field_data) {
            $fields .= "{$field_data['column_name']}, ";
            $values .= "'{$field_data['value']}', ";
        }
        $fields = substr($fields, 0, -2);
        $values = substr($values, 0, -2);

        $stmt = "INSERT INTO `{$this->get_db_table()}` ({$fields}) VALUES ($values);";
        return $stmt;
    }

    public function save() {
        global $wpdb;
        $query = $this->prepare_to_save();
        return $wpdb->query($query);
    }

    function set_context ($context) {
        $this->_context = $context;
    }

    public function describe () {
        $desc = '';
        foreach(get_object_vars($this) as $key => $value) {
            $desc .= $key.' is '.get_class($value).', ';
        }
        return substr($desc, 0, -2);
    }

    function get_db_table() {
        global $wpdb;
        $prefix = strtolower($wpdb->prefix);
        $model_name = $prefix . strtolower(get_class($this));
        if ($this->options) {
            $model_name = $this->options->get('db_table', $model_name);
        }
        return $model_name;
    }

    public function as_ddl() {
        global $wpdb;
        $model_name = $this->get_db_table();
        $table = "\nCREATE TABLE `{$model_name}`(\n";

        foreach(get_object_vars($this) as $key => $value) {
            if ($value instanceof Field) {
                $table .= '    `'.$value->get_column_name($key).'` '.$value->get_db_declaration().",\n";
            }
        }
        $table = substr($table, 0, -2);
        $table .= "\n);\n";

        foreach(get_object_vars($this) as $key => $value) {
            if ($value instanceof ForeignKey) {
                $oid = $value->to_model->options->get('pk_name');
                $otable = $value->to_model->get_db_table();
                $constraint_name = $key.'_refs_'.$otable.'_'.$oid;
                $key = $value->get_column_name($key);
                $table .= "ALTER TABLE `{$model_name}` ADD CONSTRAINT `{$constraint_name}` FOREIGN KEY (`{$key}`) REFERENCES `{$otable}` (`{$oid}`);\n";
            }
        }

        return $table;
    }

    public function create_table($drop_first=true) {
        $tablename = $this->get_db_table();

        if ($drop_first) {
            $this->db->query("DROP TABLE `$tablename`;");
        }

        $ddl = $this->as_ddl();
        $statements = explode(";", $ddl);
        foreach($statements as $stmt) {
            if (strlen($stmt) > 1) {
                $this->db->query($stmt.";");
            }
        }
    }
}

class IntrospectedModel extends Model {
}

class ModelMeta {
    private $data;
    function __construct($data){
        $this->data = $data;
    }

    function get($key, $other='') {
        $ret = $this->data[$key];
        return $ret ? $ret : $other;
    }

}
?>
