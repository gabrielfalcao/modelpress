<?php
require_once dirname(__FILE__) . '/exceptions.php';

class Field {
    protected $optional_args = array(
        'primary_key' => 'boolean',
        'null' => 'boolean',
    );
    protected $model;
    protected $construction_args = array();

    function get_column_name($fallback) {
        $col = $this->params['db_column'];
        return $col ? $col : $fallback;
    }
    function get_db_type() {
        return $this->db_type;
    }
    function get_db_declaration () {
        $ret = $this->get_db_type();

        if ($this->params['null'] == false) {
            $ret .= ' NOT NULL';
        }

        if ($this->params['primary_key']) {
            $ret .= ' PRIMARY KEY';
        }
        return $ret;
    }
    function __construct($params=null) {
        if ($params == null) {
            $params = array();
        }

        if (!is_array($params)) {
            $classname = get_class($this).'';
            throw new TypeError($classname .
                                ' takes a array or null as parameter, got the ' .
                                gettype($params) . ' "' . $params . '"');
        }
        $this->validate_args($params);
        $this->validate_optional_args($params);
        $this->params = $params;
    }
    public function set_model($model) {
        $this->model = $model;
    }
    private function validate_args($current) {
        $classname = get_class($this).'';

        foreach ($this->construction_args as $key => $type) {
            if (!array_key_exists($key, $current)) {
                throw new KeyError($classname.
                                   ' needs the '.$type.' "'.$key.'" argument,'.
                                   ' which was not passed');
            }

            $value = $current[$key];
            $actual_type = gettype($value);
            if ($actual_type.'' != $type) {
                throw new TypeError($classname.
                                    ' construction parameter "'.$key.'" should'.
                                    ' be '.$type.', got "'.$value.'" ('.$actual_type.')');
            }
        }
    }

    private function validate_optional_args($current) {
        $classname = get_class($this).'';

        foreach ($current as $key => $value) {
            if (array_key_exists($key, $this->optional_args)) {
                $expected_type = $this->optional_args[$key];
                $actual_type = gettype($value);

                if ($expected_type != $actual_type) {
                    throw new TypeError($classname.
                                        ' construction parameter "'. $key.
                                        '" should be '.$expected_type.
                                        ', got "'.$value.'" ('.$actual_type.')');
                }

            }
        }
    }

}

class IntegerField extends Field {
    public $db_type = 'integer';
    public function resolve($value) {
        $new_value = (int) $value;
        if (strlen($new_value) != strlen($value))  {
            throw new InvalidTypeConversion(
                'The '.gettype($value).' "' . $value .
                '" could not be converted to integer'
            );
        }
        return $new_value;
    }
}

class CharField extends Field {
    public $db_type = 'varchar';
    protected $construction_args = array(
        'max_length' => 'integer',
    );
    function get_db_type() {
        $maxlength = '';
        if ($this->params['max_length']) {
            $maxlength = '('.$this->params['max_length'].')';
        }
        return parent::get_db_type().$maxlength;
    }
}

class URLField extends CharField {

}

class TextField extends CharField {
    public $db_type = 'longtext';
    protected $construction_args = array();
}

class AutoField extends IntegerField {
    function get_db_type() {
        return parent::get_db_type().' AUTO_INCREMENT';
    }

}

class ForeignKey extends Field {
    public $to_model;
    function __construct($model, $data=null) {
        $this->to_model = new $model();
        parent::__construct($data);
    }
    function get_db_declaration () {
        $ret = $this->get_db_type();
        return $ret;
    }

    function get_db_type() {
        return $this->to_model->options->get('pk_type').' NOT NULL';
    }
    function get_column_name($fallback) {
        return parent::get_column_name($fallback).'_fk';
    }
}
class DateTimeField extends TextField {
    public $db_type = 'datetime';
    protected $construction_args = array();
}

?>
