//created by wzq 导航条组件
//参数
//一个用于表示导航条各项目名称的字符串数组
define(["knockout","jquery","js/xy-utils"],function(ko,$,utils){
	
	function Model(params)
	{
		var self = this;
		this.navData = ko.observableArray(params.data ? params.data : params);
		this.selectedIndex = ko.observable(0);
		this.setNavSelectedIndex = function(selectedIndex)
		{
			if(self.selectedIndex() == selectedIndex)return;
			self.selectedIndex(selectedIndex);
			utils.changeViewStack(selectedIndex);
			params.onChange && params.onChange(selectedIndex);
		};
	};

	return { viewModel:Model, template: "<div class='xy-nav' data-bind='foreach:{data:navData}'><div data-bind='text:$data, css:{selected:$parent.selectedIndex() == $index()}, click:function(d,e){$parent.setNavSelectedIndex($index());}'></div></div>" };
});