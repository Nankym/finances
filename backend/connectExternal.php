<?php
	
class connectExternal {

	function __construct () {
		$this->index();
	}

	public function index($codeBvmf = false) {
		$json = array();
		if(!$codeBvmf) {
			$codeBvmf = end(explode("/", $_SERVER['REQUEST_URI']));
		}
		$link = "http://www.bmfbovespa.com.br/Pregao-Online/ExecutaAcaoAjax.asp?CodigoPapel=" . $codeBvmf; 
		$xml = simplexml_load_file($link); 
		foreach ($xml as $papel => $value) {
			$code = utf8_decode($value['Codigo']);
			$json[$code]['nome'] = utf8_decode($value['Nome']);
			$json[$code]['data'] = utf8_decode($value['Data']);
			$json[$code]['abertura'] = utf8_decode($value['Abertura']);
			$json[$code]['minimo'] = utf8_decode($value['Minimo']);
			$json[$code]['maximo'] = utf8_decode($value['Maximo']);
			$json[$code]['medio'] = utf8_decode($value['Medio']);
			$json[$code]['ultimo'] = utf8_decode($value['Ultimo']);
			$json[$code]['oscilacao'] = utf8_decode($value['Oscilacao']);
		}
		return $json;
	}
}

$obj = new connectExternal();
?>