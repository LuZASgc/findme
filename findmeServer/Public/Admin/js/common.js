$(function(){
	//悬浮窗
	$(document).on("mouseover",".overlay",function(){
		var value = $(this).data('value');
		var html = template(value.tem, value);
		$(".overlay-content").html(html);
		var clientHeight = $(this)[0].clientHeight;
		var clientWidth = $(this)[0].clientWidth;
		var offset = $(this).offset();
		var bt = $(".overlay-panel").innerWidth()-$(this).innerWidth();
		var left = offset.left-bt/2;
		var top = offset.top+clientHeight;
		var tt = top+$(".overlay-panel").height()+54;
		var winH = $(window).height();
		if(tt>winH){
			top = top-$(".overlay-panel").height()-87;
			$(".overlay-panel").addClass('arrowBottom');
		}else{
			top = top+1;
			$(".overlay-panel").removeClass('arrowBottom');
		}
		$(".overlay-panel").css({left:left,top:top}).show();
	});
	$(document).on('mouseout','.overlay',function(){
		$(".overlay-panel").hide();
	});
	
	//工作台个数随机
	var len = $(".fast-entry li").length;
	$(".fast-entry").addClass('num'+len+' show');
});

//提示框
var remind = function(options){
	var width = options.width || 230;
	var title = options.title || '提示';
	var d = dialog({
		skin: 'remind',
		title: title,
		width: width,
		content: options.content
	});
	d.show();
	setTimeout(function () {
	    d.close().remove();
	}, 2000);
}

/**
 * 日期联动
 * @param start
 * @param end
 */
function datePickter(start,end,isDate){
	if(isDate){
		dateFmt='yyyy-MM-dd';
	}else{
		dateFmt='yyyy-MM-dd HH:mm';
	}
	if( end !== undefined && end ) {
		$("#"+start).focus(function(){
			WdatePicker({
				onpicked:function(){
					$("#"+end).focus()
				},
				maxDate:'#F{$dp.$D('+end+')}',
				dateFmt:dateFmt
			})
		});
		$("#" + end).focus(function () {
			WdatePicker({
				dateFmt: dateFmt,
				minDate: '#F{$dp.$D(' + start + ')}'
			})
		});
	}else {
		$("#"+start).focus(function(){
			WdatePicker({dateFmt:dateFmt});
		});
	}
}

/**反序列化**/
function strToObj(str){    
    str = str.replace(/&/g,"','");    
    str = str.replace(/=/g,"':'");    
    str = "({'"+str +"'})";    
    obj = eval(str);     
    return obj;    
}


function ajaxGetRegion(paramObj,domID,func){
	$.getJSON("./api.php?m=Api&c=index&a=getRegion",paramObj, function(data){
		if(data.status!=0){
			alert("数据获取失败，请稍后重试或联系管理员解决！");
		}else{
			var $obj=$('#'+domID).empty();
			$obj.append("<option value=\"0\">未定</option>");

			$.each(data.data,function(i,item){
				$obj.append("<option value=\""+i+"\">"+item+"</option>");
			});
			if(func){func();}
		}
	});
}


//资讯获取关联
function ajaxGetRelated(obj,func,cObj){
	var type=$(obj).val();
	var paramObj={'type':type};
	$.getJSON("./api.php?m=Api&c=index&a=getRelate",paramObj, function(data){
		if(data.status!=0){
			alert("数据获取失败，请稍后重试或联系管理员解决！");
		}else{
			if(typeof cObj != 'undefined') var $obj=$(cObj).empty();
			else var $obj=$(obj).next().empty();
			$obj.append("<option value=\"0\">请选择</option>");
			if(data.data) {
				$.each(data.data, function (i, item) {
					$obj.append("<option value=\"" + i + "\">" + item + "</option>");
				});
			}
			if(func){func(obj);}
		}
	});
}

/**在新增卡券里 点击里 全选门店**/
function checkAllbox(obj,status){
	alert('1');
	// $(obj).on('ifClicked', function(event){  
	// 	var childer_input = $('#store-wrapper').children('input');
	// 	$.each(childer_input,function(event){
	// 		if(childer_input.prop("checked") == true){
	// 			$(this).iCheck('uncheck');
	// 		}else{
	// 			$(this).iCheck('check');
	// 		}
	// 	});
	// });
}





