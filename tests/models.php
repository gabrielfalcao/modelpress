<?php
require_once('wp-orm/models.php');

class Post extends IntrospectedModel {
    function declaration () {
        $this->options = new ModelMeta(
            array(
                'db_table' => 'wp_posts',
                'pk_name' => 'ID',
                'pk_type' => 'integer',
            )
        );
    }
}
class Category extends IntrospectedModel {
    function declaration () {
        $this->options = new ModelMeta(
            array(
                'db_table' => 'wp_terms',
                'pk_name' => 'ID',
                'pk_type' => 'integer',
            )
        );
    }
}

class Person extends Model {
    function declaration() {
        $this->id = new AutoField(array('db_column' => 'ID', 'primary_key' => true));
        $this->name = new CharField(array('max_length' => 100));
        $this->surname = new TextField();
        $this->url = new URLField(array('max_length' => 300));
        $this->related_post = new ForeignKey(Post);
        $this->related_category = new ForeignKey(Category);
        $this->publicated_at = new DateTimeField();
        $this->modified_at = new DateTimeField(array('null' => true, 'auto_now_add' => true));
    }
}

$_person_ddl = "
CREATE TABLE `wp_person`(
    `ID` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `name` varchar(100) NOT NULL,
    `surname` longtext NOT NULL,
    `url` varchar(300) NOT NULL,
    `related_post_fk` integer NOT NULL,
    `related_category_fk` integer NOT NULL,
    `publicated_at` datetime NOT NULL,
    `modified_at` datetime
);
ALTER TABLE `wp_person` ADD CONSTRAINT `related_post_refs_wp_posts_ID` FOREIGN KEY (`related_post_fk`) REFERENCES `wp_posts` (`ID`);
ALTER TABLE `wp_person` ADD CONSTRAINT `related_category_refs_wp_terms_ID` FOREIGN KEY (`related_category_fk`) REFERENCES `wp_terms` (`ID`);
";