//created by wzq 实用工具类
define([],function(){
	var Model = {};
	//将选中的图片显示在页面中
	Model.showSelectedImg = function(files,img){
		if (!window.FileReader) return;

		for (var i = 0, f; f = files[i]; i++){
			if (f.type.match('image.*'))
			{  
				var reader = new FileReader();  
				reader.onload = function(e) {$(img).attr("src",e.target.result);};  
				reader.readAsDataURL(f);  
				return;
			}
		}
	};
	
	//把Date对象转换成指定格式的时间字符串
	Model.dateToString = function(dateTime,formatStr)
	{
		var year = dateTime.getFullYear();
		var month = dateTime.getMonth() + 1;
		var date = dateTime.getDate();
		var houres = dateTime.getHours();
		var minutes = dateTime.getMinutes();
		var seconds = dateTime.getSeconds();
		return formatStr.replace(/yyyy|YYYY/, year).replace(/yy|YY/, (year % 100) > 9 ? (year % 100).toString() : ('0' + (year % 100))).replace(/MM/,
				month > 9 ? month.toString() : '0' + month).replace(/M/g, month).replace(/dd|DD/, date > 9 ? date.toString() : '0' + date).replace(
				/d|D/g, date).replace(/hh|HH/, houres > 9 ? houres.toString() : '0' + houres).replace(/h|H/g, houres).replace(/mm/,
				minutes > 9 ? minutes.toString() : '0' + minutes).replace(/m/g, minutes).replace(/ss|SS/,
				seconds > 9 ? seconds.toString() : '0' + seconds).replace(/s|S/g, seconds);
	};

	//生成日期区间选择器。该方法调用前需要先引入WdatePicker
	Model.generateDateIntervalPicker = function(start,end,dateFmt,disabledDays,showWeek)
	{
		if(!dateFmt)dateFmt = 'yyyy-MM-dd';
		$("#"+start).focus(function(){
			WdatePicker({
			skin:'whyGreen',dateFmt:dateFmt,
			onpicked:function(){
				$("#"+end).focus();
			},
			maxDate:'#F{$dp.$D('+end+')}',
			disabledDays:disabledDays,
			isShowWeek:showWeek
			});
		});
		$("#"+end).focus(function(){
			WdatePicker({skin:'whyGreen',dateFmt:dateFmt,
				minDate:'#F{$dp.$D('+start+')}',
				disabledDays:disabledDays,
				isShowWeek:showWeek
			});
		});
	};

	//切换页面中的标签页
	Model.changeViewStack = function(selectedIndex)
	{
		$(".xy-nav-content").each(function(index, element) {
			index == selectedIndex ? $(element).addClass("current") : $(element).removeClass("current");
		});
	};

	
	return Model;
});