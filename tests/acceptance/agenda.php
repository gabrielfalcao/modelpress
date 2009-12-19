<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://192.168.0.23/");
  }

  function testMyTestCase()
  {
    $this->open("/funarte/wordpress/wp-admin/admin.php?page=admin_agenda");
    $this->click("//div[@id='agenda-calendario']/div/div/table/tbody/tr[1]/td[5]");
    $this->type("titulo", "titulo");
    $this->type("descricao", "alguma descricao");
    $this->type("horario", "12:20");
    $this->type("local", "Rua das acácias");
    $this->type("link", "http://globo.com");
    $this->type("email", "alexanmtz@gmail.com");
    $this->type("telefone", "23 4545222");
    $this->click("//button[2]");
  }
}
?>