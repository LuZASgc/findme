//created by wzq 常用组件生成器
define(["jquery"],function($){
	
	return {
		//为一个DOM元素增加导航功能，该DOM元素的子元素会在点击时切换页面。document中每个使用"xy-nav-content"class的元素都表示一个页面，一次只有一个页面会显示出来
		//node:欲增加导航功能的DOM元素(或者jQuery筛选器)
		navigatorInit:function(node){
			var self = this, $node = $(node);
			function onNavElemClick(e){
				self.setNavSelectedIndex(node, e.data[0]);
			};
			$node.children().each(function(index, element) {
				$(element).click([index],onNavElemClick);
			});
			self.setNavSelectedIndex(node, 0);
		},
		
		setNavSelectedIndex:function(nav, selectedIndex)
		{
			$(nav).children().each(function(index, element) {
				index == selectedIndex ? $(element).addClass("selected") : $(element).removeClass("selected");
			});
			$(".xy-nav-content").each(function(index, element) {
				index == selectedIndex ? $(element).show() : $(element).hide();
			});
		}
	};
});