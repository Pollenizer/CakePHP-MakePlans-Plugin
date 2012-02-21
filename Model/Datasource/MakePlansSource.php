<?php
/**
 * MakePlans Datasource
 *
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the below copyright notice.
 *
 * @author     Simon Males <simon@pollenizer.com>
 * @copyright  Copyright 2012, Pollenizer Pty. Ltd. (http://pollenizer.com)
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @since      CakePHP(tm) v 2.0.6
 */

App::uses('HttpSocket', 'Network/Http');


class MakePlansSource extends DataSource {

	public $startQuote = null;
	public $endQuote = null;

	public $columns = array(
		'time' => array('name' => 'time', 'format' => 'H:i', 'formatter' => 'date'),
	);
/**
 * Constructor
 */
	public function __construct($config) {
		$this->connection = new HttpSocket(
			"http://{$config['account']}.makeplans.net/"
		);
		// No password required
		$this->connection->configAuth('Basic', $config['apikey'], '');
		parent::__construct($config);
	}

/**
 * query
 *
 * @param string $name The name of the method being called.
 * @param array $arguments The arguments to pass to the method.
 * @return array Body of HttpResponse object or boolean false.
 */
	public function query($name = null, $arguments) {

		// This should be optimised with some sort of Set::merge of defaults
		$arguments	= $arguments[0];
		$id			= isset($arguments['id']) ? $arguments['id'] : null;
		$backend	= isset($arguments['backend']) ? $arguments['backend'] : false;
		$method		= isset($arguments['method']) ? $arguments['method'] : 'GET';
		$body		= isset($arguments['body']) ? json_encode($arguments['body']) : null;
		$query		= isset($arguments['query']) ? http_build_query($arguments['query']) : null;

		$uri = null;

		if ($backend) {
			$uri = '/manage';
		}

		$uri .= '/' . $name;

		if (!empty($id)) {
			$uri .= '/' . $id;
		}

		$uri .= '.json';

		$data = array (
			'method' => $method,
			'uri' => array (
				'path' => $uri,
				'query' => $query,
			),
			'header' => array (
				'Content-Type' => 'application/json'
			),
			'body' => $body
		);
		$response = $this->connection->request($data);

		if ($response->code == 200) {
			return json_decode($response->body, true);
		}

		return false;
	}
}
