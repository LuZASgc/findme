//created by wzq 页码导航组件
//参数：
//crt-page:当前页码
//total-page:页码总数
//on-change:页码发生变化时的回调函数
define(["ngcomponents/xy-componentModule"],function(module){
	var temp = '<div class="xy-pageNavigator">' +
  	'<img src="./Public/Admin/img/prePageBtn.png" class="xy-imgButton" ng-click="prePage()"/>' +
    '<span style="margin:0 10px">{{crtPage+"/"+totalPage}}</span>' +
    '<img src="./Public/Admin/img/nextPageBtn.png" class="xy-imgButton" ng-click="nextPage()"/>' +
    '<input type="number" style="width:75px;margin:0 10px" min="1" ng-model="jumpValue"/>' +
    '<a class="xy-button themeWhite" ng-click="jumpToPage(jumpValue)">跳转</a>' +
  '</div>';
	
	return module.directive("xyPageNavigator", function(){
		
		return {
			restrict:"E",
			replace:true,
			scope:{crtPage:"=",
					totalPage:"=",
					onChange:"="},
			template:temp,
			link: function(scope, element, attrs){
				scope.jumpToPage = function(v)
				{
					if(v >= 1 && v <= scope.totalPage)
					{
						scope.crtPage = v;
						scope.onChange && scope.onChange(v);
					}
					else
					{
						alert("没有指定的页码");
					}
				};
				scope.prePage = function(){
					if(scope.crtPage > 1)
					{
						scope.jumpToPage(scope.crtPage-1);
					}
				};
				scope.nextPage = function(){
					if(scope.crtPage < scope.totalPage)
					{
						scope.jumpToPage(scope.crtPage+1);
					}
				};
			}
		};
	});
});