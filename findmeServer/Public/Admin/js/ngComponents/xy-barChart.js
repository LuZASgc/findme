//created by wzq 柱状图组件
//参数
//data:欲展示的数据数组，其中元素都是Object
//key-x:用于指定data数组中元素使用哪个字段的值来标注于图表X轴上
//key-y:用于指定data数组中元素使用哪个字段的值来标注于图表Y轴上
//legend:柱状图标注
define(["ngcomponents/xy-componentModule","echarts.min"],function(module,echarts){
	return module.directive("xyBarChart", function(){
		return {
			restrict:"E",
			replace:true,
			scope:{keyX:"@",
				keyY:"@",
				legend:"@",
				data:"="},
			template:"<div class='xy-chartContainer'></div>",
			link: function(scope, element, attrs){
				var eChart = echarts.init(element[0]),
				xAry = [], yAry = [];
				for(var i=0;i<scope.data.length;i++)
				{
					xAry[i] = scope.data[i][scope.keyX];
					yAry[i] = scope.data[i][scope.keyY];
				}
				
				// 指定图表的配置项和数据
				var option = {
						tooltip: {},
						legend: {
								data:[scope.legend]
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
								name:scope.legend,
								type:'bar',
								data:yAry,
								barWidth: 40
						}],
						color: ['#fe7f30']
				};
			
				eChart.setOption(option);
			}
		};
	});
});