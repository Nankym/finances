angular.module("finances").controller("financesCtrl", function ($scope, $http, uppercaseFilter, currencyFilter) {
	$scope.app = "Ações Ativa$";

	var carregarTraders = function () {
		$http.get("/backend/data/getTraders").success( function ( data ) {
			$scope.traders = data;
		});
	}

	var reloadTraders = function() {
	   carregarTraders();
	   setTimeout(function() { reloadTraders(); }, 300000);
	}

	$scope.adicionarCompra = function (trader) {
		trader.ativo = true;
		$http.post("/backend/data/setTrader", trader).success( function ( data ) {
			delete $scope.trader;
			carregarTraders();
		});
	};	
	$scope.apagarAcao = function (trader) {
		$http.post("/backend/data/deleteTrader", trader).success( function ( data ) {
			delete $scope.trader;
			carregarTraders();
		});
	};
	$scope.venderAcao = function (trader) {
		$http.post("/backend/data/sellTrader", trader).success( function ( data ) {
			delete $scope.trader;
			carregarTraders();	
		});
	};
	$scope.ordenaPor = function(campo) {
		$scope.search = campo;
		$scope.direcaoOrdenacao = !$scope.direcaoOrdenacao;
	};

	carregarTraders();
	setTimeout(function() { reloadTraders(); }, 300000);
});