//created by wzq 柱状图组件
//参数
//data:欲展示的数据数组，其中元素都是Object
//xProperty:用于指定data数组中元素使用哪个字段的值来标注于图表X轴上
//yProperty:用于指定data数组中元素使用哪个字段的值来标注于图表Y轴上
//legend:柱状图标注
define(["knockout","jquery","echarts.min"],function(ko,$,echarts){
	
	function Model(params)
	{
		
	};
	function CreateViewModel(params, componentInfo)
	{
		var eChart = echarts.init($(componentInfo.element).children()[0]),
		xAry = [], yAry = [];
		for(var i=0;i<params.data.length;i++)
		{
			xAry[i] = params.data[i][params.xProperty];
			yAry[i] = params.data[i][params.yProperty];
		}
		
		// 指定图表的配置项和数据
		var option = {
				tooltip: {},
				legend: {
						data:[params.legend]
				},
				xAxis: {
						data: xAry,
						splitLine:{show:false},
						axisTick:{show:false}
				},
				yAxis: {
						axisTick:{show:false}
				},
				series: [{
						name:params.legend,
						type:'bar',
						data:yAry,
						barWidth: 40
				}],
				color: ['#fe7f30']
		};
	
		eChart.setOption(option);
		return new Model(params);
	}
	return { viewModel:{createViewModel: CreateViewModel}, template: "<div class='xy-chartContainer'></div>" };
});