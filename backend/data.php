<?php
	
function __autoload($class_name) {
	require_once $class_name . '.php';
}

class data {

	function __construct () {
		$action = str_replace("/", "", $_SERVER['PATH_INFO']);
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

		$db = $this->getConnection('traders');
		$now = date("d/m/Y");
		$update = $db->update( array( 'trader.codigo' => $request->codigo, 'trader.dataCompra' => $request->dataCompra, 'trader.valorCompra' => $request->valorCompra), array( '$set' => array('trader.ativo'=>false, 'trader.dataVenda'=>$now)));
	}

	public function setTrader() {
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		
		$db = $this->getConnection('traders');

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
			$value = $this->manipulateData($value);

			$json[] = $value['trader'];		
		}

		$json = json_encode($json);
		echo $json;
	}

	protected function getConnection($database) {
		$connection = new MongoClient();
		$db = $connection->$database;

		$collection = $connection->database->$database;

		return $collection;
	}

	protected function manipulateData($value) {
		
		/**
		 * Resgata os dados diretamente da Bovespa
		 */

		$connectExternal = new connectExternal();
		$valorAtual = $connectExternal->index($value['trader']['codigo']);

		/**
		 * Simplifica os dados e converte o que vem da bovespa para int
		 */
		$valorAtual = floatval(str_replace(',', '.', $valorAtual[$value['trader']['codigo']]['ultimo']));
		$quantidade = $value['trader']['quantidade'];
		$valorCompra = $value['trader']['valorCompra'];
		$taxa = $value['trader']['taxa'];
		
		/**
		 * Manipula os dados repassando o valor para o trader
		 */

		$value['trader']['valorAtual'] = $valorAtual;
		$value['trader']['rendaAtual'] = ( ( $valorAtual * $quantidade ) - ( ( $valorCompra * $quantidade ) + $taxa ));
		$value['trader']['percAtual']  = ( ( ($valorAtual * $quantidade ) * 100 ) / ( ($valorCompra * $quantidade ) + $taxa ) ) -100;

		/**
		 * Retorna os dados no array de acordo com o que foi passado
		 */

		return $value;
	}
}

$obj = new data();
?>