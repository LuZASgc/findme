define(["knockout","components/xy-tooltip","components/xy-emotion"],function(ko,xyTooltip,xyEmotionPicker){
	var Model = {};
	//为列表中每个数据增加一个仅供前端使用的，用来记录消息回复内容的属性
	Model.addReplyContentProperty = function(ary)
	{
		var len = ary.length, elem;
		for(var i=0;i<len;i++)
		{
			ary[i].replyContent = ko.observable("");
			ary[i].replyContainerShow = ko.observable(false);
		}
	};
	
	//该函数用于显示/隐藏消息回复区域
	Model.showReplyContainer = function(d,show)
	{
		d.replyContainerShow(show);
	};
	
	//获取新消息提示文字
	Model.getNewMsgAlertTxt = function(c)
	{
		return c+"条新消息";
	};
	
	Model.setUserInfo = function(d){
		if(!this.tooltip)this.tooltip = new xyTooltip();
		var c = "<div style='margin-bottom:18px'>用户详情</div>" +
						"<div style='margin-bottom:10px'>用户昵称："+d.uNickName+"</div>" +
						"<div style='margin-bottom:10px'>用户姓名："+d.uName+"</div>" +
						"<div style='margin-bottom:10px'>备注："+d.uPS+"</div>" +
						"<div style='margin-bottom:10px'>手机号码："+d.uPhoneNum+"</div>" +
						"<div style='margin-bottom:10px'>会员等级："+d.uLv+"</div>" +
						"<div style='margin-bottom:10px'>积分："+d.uScore+"</div>" +
						"<div>消费总额："+d.consume+"</div>";
		this.tooltip.setContent(c);
	};
	
	Model.showUserInfo = function(d,target){
		this.setUserInfo(d);
		this.tooltip.show(target);
	};
	
	Model.hideUserInfo = function(){
		if(this.tooltip)
			this.tooltip.hide();
	};
	
	/** 使用以下两个方法需要引入js/xy-emotion.js及css/xy-emotion.css **/
	Model.toggleEmotionPicker = function(target,bindValue){
			if(!this.emotionPicker)this.emotionPicker = new xyEmotionPicker();
			this.emotionPicker.toggle(target);
			if(bindValue)this.emotionPicker.bindValue = bindValue;
	};
	
	Model.quickReply = function(d){
			var v = d.replyContent();
			if(v.length > 0)
			{
				d.replyContent("");
				d.replyState(1);
				this.showReplyContainer(d,false);
			}
	};
	
	return Model;
});