//created by wzq 页码导航组件
//参数：
//total:页码总数
//onChange:页码发生变化时的回调函数
define(["knockout"],function(ko){
	
	function Model(params)
	{
		var self = this;
		this.crtPage = params.crtPage;
		this.totalPage = params.total;
		this.jumpValue = ko.observable("");
		this.jumpToPage = function(d,e)
		{
			var v = e ? parseInt(self.jumpValue()) : d, t = self.totalPage();
			if(v >= 1 && v <= t)
			{
				self.crtPage(v);
				params.onChange && params.onChange(v);
			}
			else
			{
				alert("没有指定的页码");
			}
		};
		this.prePage = function(){
			var v = self.crtPage();
			if(v > 1)
			{
				self.jumpToPage(v-1);
			}
		};
		this.nextPage = function(){
			var v = self.crtPage();
			if(v < self.totalPage())
			{
				self.jumpToPage(v+1);
			}
		};
	};

	var temp = '<div class="xy-pageNavigator">' +
  	'<img src="/pulbic/Admin/img/prePageBtn.png" class="xy-imgButton" data-bind="click:prePage"/>' +
    '<span style="margin:0 10px" data-bind="text:crtPage()' + "+'/'+" + 'totalPage()"></span>' +
    '<img src="/pulbic/Admin/img/nextPageBtn.png" class="xy-imgButton" data-bind="click:nextPage"/>' +
    '<input type="number" style="width:75px;margin:0 10px" min="1" data-bind="attr:{max:totalPage()},textInput:jumpValue"/>' +
    '<a class="xy-button themeWhite"  data-bind="click:jumpToPage">跳转</a>' +
  '</div>';
	
	return { viewModel:Model, template:temp };
});