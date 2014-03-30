<?php
/**
 * AccountFixture
 *
 */
class PermAccountFixture extends CakeTestFixture {

/**
 * Schema
 * 
 * @var string
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		//Admin
		array(
			'id' => 1,
			'created' => '2014-02-01 16:38:28',
			'email' => 'Lorem ipsum dolor sit amet',
			'password' => '24faf07fb2e2e2e1b88d62741157b82ca9ae792b',//pietpiraat
			'modified' => '2014-02-01 16:38:28'
		),
		//User
		array(
			'id' => 2,
			'created' => '2014-02-01 16:38:28',
			'email' => 'foo@bar.com',
			'password' => '24faf07fb2e2e2e1b88d62741157b82ca9ae792b',//pietpiraat
			'modified' => '2014-02-01 16:38:28'
		),
	);

}
