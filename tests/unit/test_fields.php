<?php
require_once('simpletest/autorun.php');
require_once('wp-orm/models.php');

/*
 Field
*/

class TestField extends UnitTestCase {
    function test_construction_primary_key_must_be_boolean() {
        $this->expectException(
            new TypeError(
                'Field construction parameter "primary_key" should '.
                'be boolean, got "10" (integer)'
            )
        );
        new Field(array('primary_key' => 10));
    }
    function test_construction_primary_key() {
        new Field(array('primary_key' => true));
    }
    function test_construction_not_primary_key() {
        new Field(array('primary_key' => false));
    }
    function test_construction_null_must_be_boolean() {
        $this->expectException(
            new TypeError(
                'Field construction parameter "null" should '.
                'be boolean, got "10" (integer)'
            )
        );
        new Field(array('null' => 10));
    }
    function test_construction_null() {
        new Field(array('null' => true));
    }
    function test_construction_not_null() {
        new Field(array('null' => false));
    }
    function test_has_set_model () {
        $this->assertTrue(in_array('set_model', get_class_methods(Field)),
                          'Field should have the method set_model');
    }
}

/*
 Integer Field
*/

class TestIntegerField extends UnitTestCase {
    function setUp() {
        $this->my_field = new IntegerField();
    }
    function test_construction_array_as_parameter(){
        $this->expectException(
            new TypeError(
                'IntegerField takes a array or null as parameter, ' .
                'got the string "foo"'
            )
        );
        new IntegerField('foo');
    }
    function test_can_resolve_from_string() {
        $this->assertIdentical(10, $this->my_field->resolve('10'));
    }
    function test_can_resolve_from_boolean_1() {
        $this->assertIdentical(1, $this->my_field->resolve(true));
    }
    function test_can_not_resolve_from_boolean_0() {
        $this->expectException(
            new InvalidTypeConversion(
                'The boolean "" could not be converted to integer'
            )
        );

        $this->assertIdentical(0, $this->my_field->resolve(false));
    }
    function test_can_not_resolve_from_array() {
        $this->expectException(
            new InvalidTypeConversion(
                'The array "Array" could not be converted to integer'
            )
        );

        $this->my_field->resolve(Array());
    }

}

/*
 Char Field
*/

class TestCharField extends UnitTestCase {
    function test_construction_takes_max_length() {
        $this->expectException(
            new KeyError(
                'CharField needs the integer "max_length" argument, ' .
                'which was not passed'
            )
        );
        new CharField();
    }
    function test_construction_max_length_must_be_integer() {
        $this->expectException(
            new TypeError(
                'CharField construction parameter "max_length" should '.
                'be integer, got "10" (string)'
            )
        );
        new CharField(array('max_length' => '10'));
    }
    function test_construction() {
        $my_char = new CharField(array('max_length' => 200));
    }
}
?>