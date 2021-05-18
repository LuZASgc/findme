//created by wzq 提示框类
//需要：css/xy-tooltip.css
define(["jquery"],function($){
	function xyTooltip(){
		this.$dom = $("<div class='xy-tooltip'></div>");
		$('body').append(this.$dom);
		this.$dom.hide();
	};

	xyTooltip.prototype.setContent = function(v){
		this.$dom.html(v);
	};

	xyTooltip.prototype.show = function(targetDom){
		var $targetDom = $(targetDom);
		var pos = $targetDom.offset(), w = $targetDom.outerWidth();
		this.$dom.css({top:pos.top, left:pos.left+w});
		this.$dom.show();
	};
	
	xyTooltip.prototype.hide = function(){
		this.$dom.hide();
	};
	
	return xyTooltip;
});