//created by wzq 导航条组件
//参数
//elems:一个用于表示导航条各项目名称的字符串数组
//on-change:导航标签页切换时触发的事件处理函数，该函数中会收到一个表示当前选中标签页索引位置的参数
//
//事件
//xyNavigator.setNavSelectedIndex:父scope派发该事件后本组件会根据事件中的参数来切换选中标签页
define(["ngcomponents/xy-componentModule","js/xy-utils"],function(module,xyUtils){
	return module.directive("xyNavigator", function(){
		return {
			restrict:"E",
			replace:true,
			scope:{elems:"=",
					onChange:"="},
			template:"<div class='xy-nav'>" +
						"<div ng-repeat='x in elems' ng-class='{selected:selectedIndex == $index}' ng-click='setNavSelectedIndex($index)'>{{x}}</div>" + 
					"</div>",
			link: function(scope, element, attrs){
				scope.selectedIndex = 0;
				scope.setNavSelectedIndex = function(selectedIndex)
				{
					if(scope.selectedIndex == selectedIndex)return;
					scope.selectedIndex = selectedIndex;
					xyUtils.changeViewStack(selectedIndex);
					scope.onChange && scope.onChange(selectedIndex);
				};
				scope.$on("xyNavigator.setNavSelectedIndex", function(e,i){scope.setNavSelectedIndex(i);});
			}
		};
	});
});