<?php

function __autoload($class_name) {
	require_once $class_name . '.php';
}

class data {

	function __construct () {
		$action = end(explode("/", $_SERVER['REQUEST_URI']));
		$this->$action();
	}
	public function index() {
		
	}

	public function deleteTrader() {
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$db = $this->getConnection('traders');

		$remove = $db->remove( array( 'trader.codigo' => $request->codigo, 'trader.dataCompra' => $request->dataCompra, 'trader.valorCompra' => $request->valorCompra));
	}

	public function sellTrader() {
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$request = $this->requestAjustValue($request);

		$db = $this->getConnection('traders');
		$update = $db->update( array( 'trader.codigo' => $request->codigo, 'trader.dataCompra' => $request->dataCompra, 'trader.valorCompra' => $request->valorCompra), array( '$set' => array('trader.ativo'=>false, 'trader.dataVenda' => $request->dataVenda, 'trader.valorVenda' => $request->valorVenda)));
	}

	public function setTrader() {
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		
		$db = $this->getConnection('traders');

		$request = $this->requestAjustValue($request);

		$doc = array(
		    "trader" => array(
		    	"codigo" => $request->codigo,
		    	"dataCompra" => $request->dataCompra,
		    	"dataVenda" => "",
		    	"valorCompra" => $request->valorCompra,
		    	"quantidade" => $request->quantidade,
		    	"taxa" => $request->taxa,
		    	"stop" => $request->stop,
		    	"gain" => $request->gain,
		    	"ativo" => $request->ativo
		    )
		);

		$insert = $db->insert( $doc );
	}

	public function getTraders() {
		$db = $this->getConnection('traders');
		$document = $db->find( array('trader.ativo' => true) );

		foreach ($document as $key => $value) {
			$value = $this->manipulateData($value);
			
			$json[] = $value['trader'];		
		}

		$json = json_encode($json);
		echo $json;
	}

	public function getTradersVendidas() {
		$db = $this->getConnection('traders');
		$document = $db->find( array('trader.ativo' => false) );
		foreach ($document as $key => $value) {
			$value = $this->manipulateData($value, true);

			$json[] = $value['trader'];		
		}

		$json = json_encode($json);
		echo $json;
	}

	protected function manipulateData($value, $send = false) {
		
		/**
		 * Resgata os dados diretamente da Bovespa
		 */

		$connectExternal = new connectExternal();
		$valorAtual = $connectExternal->index($value['trader']['codigo']);

		/**
		 * Simplifica os dados e converte o que vem da bovespa para int
		 */
		$value['trader']['nome'] = $valorAtual[$value['trader']['codigo']]['nome'];
		$value['trader']['oscilacao'] = floatval(str_replace(',', '.', $valorAtual[$value['trader']['codigo']]['oscilacao']));
		$valorAtual = floatval(str_replace(',', '.', $valorAtual[$value['trader']['codigo']]['ultimo']));
		$quantidade = $value['trader']['quantidade'];
		$valorCompra = $value['trader']['valorCompra'];
		$taxa = $value['trader']['taxa'];
		$value['trader']['valorAtual'] = $valorAtual;
		
		/**
		 * Manipula os dados repassando o valor para o trader
		 */

		if($send) {
			$valorVenda = $value['trader']['valorVenda'];
			$value['trader']['rendaAtual'] = ( ( $valorVenda * $quantidade ) - ( ( $valorCompra * $quantidade ) + $taxa ));
			$value['trader']['percAtual']  = ( ( ($valorVenda * $quantidade ) * 100 ) / ( ($valorCompra * $quantidade ) + $taxa ) ) -100;
		} else {
			$value['trader']['rendaAtual'] = ( ( $valorAtual * $quantidade ) - ( ( $valorCompra * $quantidade ) + $taxa ));
			$value['trader']['percAtual']  = ( ( ($valorAtual * $quantidade ) * 100 ) / ( ($valorCompra * $quantidade ) + $taxa ) ) -100;
		}

		/**
		 * Retorna os dados no array de acordo com o que foi passado
		 */

		return $value;
	}

	protected function requestAjustValue($request) {
		$request->valorCompra = str_replace(",", ".", $request->valorCompra);
		$request->valorVenda = str_replace(",", ".", $request->valorVenda);
		$request->taxa = str_replace(",", ".", $request->taxa);
		$request->stop = str_replace(",", ".", $request->stop);
		$request->gain = str_replace(",", ".", $request->gain);

		return $request;
	}


	/** 
	 * Função para conectar ao banco de dados
	 */
	protected function getConnection( $trader ) {
		$mongo = new connectDB();
		$db = $mongo->getConnection($trader);
		return $db;
	}
}

$obj = new data();
?>