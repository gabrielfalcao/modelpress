<?php
/*
 <ModelPress - object-relational mapper for php>
 Copyright (C) <2009>  Gabriel Falcão <gabriel@nacaolivre.org>

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once('simpletest/autorun.php');
require_once('wp-orm/models.php');
require_once('tests/tools.php');
require_once('tests/models.php');

$empty_model_declaration_was_called = false;
class EmptyModel extends Model {
    function declaration () {
        global $empty_model_declaration_was_called;
        $empty_model_declaration_was_called = true;
    }
}

class TestModelMeta extends UnitTestCase {
    function test_get_option() {
        $post = new Post();
        $this->assertEqual($post->options->get('pk_name'), 'ID');
    }
    function test_get_option_with_fallback() {
        $post = new Post();
        $this->assertEqual($post->options->get('something', 'fallback'),
                           'fallback');
    }

}
class TestModel extends UnitTestCase {
    function test_can_set_a_context () {
        $methods = get_class_methods(Model);
        $this->assertTrue(in_array('set_context', $methods),
                          "The Model should have the method set_context, ".
                          "so that I can put anything within it, for any later usage.");

    }
    function test_calls_declaration_method_on_construction() {
        global $empty_model_declaration_was_called;
        new EmptyModel();
        $this->assertTrue($empty_model_declaration_was_called,
                          "Expected to call declation once, but was not called at all");
        $empty_model_declaration_was_called = false;
    }

    function test_as_ddl_create_table() {
        global $_person_ddl;
        $person = new Person();
        $this->assertEqual($person->as_ddl(),
                           $_person_ddl);
    }
    function test_prepare_to_save() {
        $hl = new Person(
            array(
                  'url' => 'http://gnu.org'
            )
        );
        $this->assertEqual($hl->prepare_to_save(), "INSERT INTO `wp_person` (url) VALUES ('http://gnu.org');");
    }
}
?>