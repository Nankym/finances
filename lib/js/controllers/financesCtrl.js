angular.module("finances").controller("financesCtrl", function ($scope, $http, uppercaseFilter, currencyFilter) {
	$scope.app = "Ações Ativa$";

	var carregarTraders = function () {
		$http.get("http://localhost/finances/backend/data/getTraders").success( function ( data ) {
			$scope.traders = data;
		});
	}
	$scope.adicionarCompra = function (trader) {
		trader.ativo = true;
		$http.post("http://localhost/finances/backend/data/setTrader", trader).success( function ( data ) {
			delete $scope.trader;
			carregarTraders();
		});
	};	
	$scope.apagarAcao = function (traders) {
		$scope.traders = traders.filter(function(trader) {
			if(!trader.checked) {
				return trader;
			} else {
				$http.post("http://localhost/finances/backend/data/deleteTrader", trader).success( function ( data ) {
					
				});
			}
		});
	};
	$scope.venderAcao = function (traders) {
		$scope.traders = traders.filter(function(trader) {
			if(!trader.checked) {
				return trader;
			} else {
				$http.post("http://localhost/finances/backend/data/sellTrader", trader).success( function ( data ) {
					
				});
			}
		});
	};
	$scope.ifTraderChecked = function(traders) {
		return traders.some( function (trader) {
			return trader.checked;
		});
	};
	$scope.ordenaPor = function(campo) {
		$scope.search = campo;
		$scope.direcaoOrdenacao = !$scope.direcaoOrdenacao;
	};
	carregarTraders();
});