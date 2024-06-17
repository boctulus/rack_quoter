<?php

return [
    'db_connections' => // get_db_connections()

	[
		'main' => [
			'host'		=> '127.0.0.1',
			'port'		=> 3306,
			'driver' 	=> 'mysql',
			'db_name' 	=> DB_NAME,
			'user'		=> DB_USER, 
			'pass'		=> DB_PASSWORD,
			'charset'	=> DB_CHARSET,
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => true
			],
			'tb_prefix'  => tb_prefix(),
		],

		'robot' => [
			'host'		=> '127.0.0.1',
			'port'		=> 3306,
			'driver' 	=> 'mysql',
			'db_name' 	=> 'robot',
			'user'		=> DB_USER, 
			'pass'		=> DB_PASSWORD,
			'charset'	=> DB_CHARSET,
			'pdo_options' => [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => true
			],
			'tb_prefix'  => '',
		],

		// ...
	],

	'db_connection_default' => 'main',
];

   