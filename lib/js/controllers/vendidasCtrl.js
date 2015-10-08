angular.module("vendidas").controller("vendidasCtrl", function ($scope, $http, uppercaseFilter, currencyFilter) {
	$scope.app = "Ações Vendida$";

	var carregarTraders = function () {
		$http.get("/backend/data/getTradersVendidas").success( function ( data ) {
			$scope.traders = data;
		});
	}
	$scope.ordenaPor = function(campo) {
		$scope.search = campo;
		$scope.direcaoOrdenacao = !$scope.direcaoOrdenacao;
	};
	carregarTraders();
});