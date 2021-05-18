//created by wzq 表情选择框类
//需要：css/xy-emotion.css
define(["jquery"],function($){
	var availableEmotions = ["thumbsup","thumbsdown","angry","anguished","astonished","blush","confounded","confused","cry","fearful","flushed"];

	function xyEmotionPicker(){
		var $dom = $("<div class='emotionPicker'></div>");
		var self = this;
		
		$dom.click(function(e){
			e.stopPropagation();
			if(e.target !== $dom[0])//不等于自身表示点到的是表情
			{
				var appendTxt = " :" + $(e.target).data("emotion") + ": ";
				if(self.bindValue)//一个observable类型的变量
				{
					self.bindValue(self.bindValue() + appendTxt);
				}
				self.hide();
			}
		});
		
		$('body').append($dom);
		var len = availableEmotions.length, $eItem;
		for(var i=0;i<len;i++)
		{
			$eItem = $("<img src='img/emoji/" + availableEmotions[i] + ".png' class='emotionItem' data-emotion='" + availableEmotions[i] + "'/>");
			$dom.append($eItem);
		}
		
		
		self.$dom = $dom;
		self.click2Hide = function(){self.hide();};
		self.hide();
	}
	
	xyEmotionPicker.prototype.show = function(targetDom){
		var $targetDom = $(targetDom);
		var pos = $targetDom.offset(), w = $targetDom.outerWidth(), self = this;
		this.$dom.css({top:pos.top, left:pos.left+w});
		this.$dom.show();
		$("body").on("click",self.click2Hide);
		this.isHidden = false;
	};
	
	xyEmotionPicker.prototype.hide = function(){
		this.$dom.hide();
		$("body").off("click",this.click2Hide);
		this.isHidden = true;
	};
	
	xyEmotionPicker.prototype.toggle = function(targetDom){
		this.isHidden ? this.show(targetDom) : this.hide();
	};
	
	return xyEmotionPicker;
});
