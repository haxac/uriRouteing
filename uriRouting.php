<?php

/* Debug */
// HTTP request
$uri = 'http://192.168.10.109/api/v1/oauth/token';
$method = 'POST';

$route = new uriRouting();
$t = $route->resolv($method, $uri);
$param = $route->param;

// Class name for request destination and require
echo("   destination:'".$t['{destination}']."'\n");
echo("   className:'".$t['{className}']."'\n");
// echo Parameter
foreach($param as $key => $val) {
	echo("     ".$key." => ".$val."\n");
}

exit;
/* Debug */

/**
 * 
 */
class uriRouting
{
	/** Specify the offset to start parsing when URI is separated by "/". */
	public $offset=3;
	/* When the URI is parsed, the included parameters are set as an associative array. */
	public $param;

	/*
	 * Define URIs of all APIs as associative array.
	 * Refer to the existing settings for the definition method.
	 */
	 private $uriTree = array(
		'POST' => array(
			'api' => array(
				'v1' => array(
					'oauth' => array(
						'token' => array(
							// POST http://192.168.10.109/api/v1/oauth/token
							'{destination}' => './v1/OauthController.php',
							'{className}' => '\\v1\\OauthController.php'
						)
					)
				)
			)
		),
		'GET' => array(
			'api' => array(
				'v1' => array(
					'cash-inventories' => array(
						'{hasParam}' => array(
							// GET http://192.168.10.109/api/v1/cash-inventories/yesterday
							// GET http://192.168.10.109/api/v1/cash-inventories/20190201
							'{paramName}' => 'date',
							'{destination}' => './v1/CashInventoryController.php',
							'{className}' => '\\v1\\CashInventoryController.php'
							/*
							 * -- hoge parameter sample--
							 * -- uri:http://192.168.10.109/api/v1/cash-inventories/20190201/hoge/hogehoge
							 * ,
							 * 'hoge' => array(
							 * 	'{hasParam}' => array(
							 * 		'{paramName}' => 'hoge',
							 * 		'{destination}' => './v1/CashInventoryController.php',
							 * 		'{className}' => '\\v1\\CashInventoryController.php'
							 * 	)
							 * )
							*/
						)
					),
					'unpaid' => array(
						// GET http://192.168.10.109/api/v1/unpaid
						'{destination}' => './v1/UnpaidController.php',
						'{className}' => '\\v1\\UnpaidController.php'
						/*
						 * -- patientid parameter sample--
						 * -- uri:http://192.168.10.109/api/v1/unpaid/patientid
						 * ,
						 * '{hasParam}' => array(
						 * 	'{paramName}' => 'patientid',
						 * 	'{destination}' => './v1/UnpaidController.php',
						 * 	'{className}' => '\\v1\\UnpaidController.php'
						 * )
						 * --sample--
						**/
					)
				)
			)
		),
		'PUT' => array(
		),
		'DELETE' => array(
		),
		'PATCH' => array(
		),
		'HEAD' => array(
		)
	);

	/*
	 * This information can be used to require and instantiate an API controller at the caller.
	 * Based on the request information from the client, the corresponding controller path and class name and parameters are returned.
	 * @param $method Request method
	 * @param $URI Requested URI（e.g.: http://192.168.10.109/api/v1/oauth/token）
	 */
	public function resolv($method, $URI) {
		$param = array();
		// Convert URI to slash delimited array and offset
		$URI = array_slice(explode('/', $URI), $this->offset);

		$r = $this->search($URI, $this->uriTree[$method], 0, $param);
		$this->param = $param;
		return $r;
	}
	
	/*
	 * Searches the field $ uriTree recursively from the beginning of the request URI and returns the corresponding controller.
	 * $URI Array of request URI divided by slash
	 * $uriTree URI tree to investigate. It recurs while removing the investigated part.
	 * $offset Offset value of searched part of request URI
	 * &$param Holds the parameter part obtained from URI.
	 */
	private function search($URI, $uriTree, $offset, &$param) {
		if(array_key_exists($URI[$offset], $uriTree)) {
			// URI exists
			$uriTree = $uriTree[$URI[$offset]];
			$offset++;
	
			if(array_key_exists('{hasParam}', $uriTree) && $offset < count($URI)) {
				// The requested API can have parameters, which are included in the URI.
				$uriTree = $uriTree['{hasParam}'];
				$param[$uriTree['{paramName}']] = $URI[$offset];
				$offset++;
			}
		
		} else {
			return -1;
		}

		if($offset < count($URI)) {
			// Search next part of URI
			return $this->search($URI, $uriTree, $offset, $param);
		} else {
			// End of search
			return $uriTree;
		}
	}
}
?>