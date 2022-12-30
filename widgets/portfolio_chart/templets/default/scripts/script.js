$(document).ready(function() {
	var $widget = $("div[data-widget=pusan-portfolio_chart][data-templet=default]");
	var $chart = $("div[data-role=chart]",$widget);

	$chart.each(function() {
		var $target_chart = $(this);

		// 나의 역량
		var c_mypoint = $target_chart.data("mypoint");
		var replaced_point = c_mypoint.replace(/[\[\]\"]/g,"");
		var ary_point = replaced_point.split(',');
		var mypoint = ary_point.map(function(item) {
			return parseFloat(item);
		});

		// 학과 역량
		var dept_point = $target_chart.data("dept");
		var replaced_dept_point = dept_point.replace(/[\[\]\"]/g,"");
		var ary_dept_point = replaced_dept_point.split(',');
		var dept = ary_dept_point.map(function(item) {
			return parseFloat(item);
		});

		// 동일학년 역량
		var grade_point = $target_chart.data("grade");
		var replaced_grade_point = grade_point.replace(/[\[\]\"]/g,"");
		var ary_grade_point = replaced_grade_point.split(',');
		var grade = ary_grade_point.map(function(item) {
			return parseFloat(item);
		});

		// 동일학년 학과평균
		var grade_dept_point = $target_chart.data("grade_dept");
		var replaced_grade_dept_point = grade_dept_point.replace(/[\[\]\"]/g,"");
		var ary_grade_dept_point = replaced_grade_dept_point.split(',');
		var grade_dept = ary_grade_dept_point.map(function(item) {
			return parseFloat(item);
		});



		$target_chart.highcharts({
			chart:{
				type:"column",
				backgroundColor:"transparent"
			},
			colors:["#4caf50","#d7433c","#1e88e5","#f1b163"],
			title:{text:null},
			xAxis:{
				categories:$target_chart.data("categories").split(","),
				lineWidth: 0,
				labels:{style:{fontSize:"12px",fontFamily:"NanumSquare",fontWeight:700}}
			},
			yAxis:{
				lineWidth:0,
				min:0,
				// tickInterval:30,
				title:{text:null},
				tickPositions:0
			},
			credits:{enabled:false},
			tooltip:{headerFormat:"",pointFormat:"<span style=\"font-family:NanumBarunGothic; font-size:12px;\">{series.name}: <b>{point.y:0f}점</b>"},
			legend:{enabled:true},
			series:[
				{name:'나의역량',data:mypoint},
				{name:'학과평균',data:dept},
				{name:'동일학년 전체평균',data:grade},
				{name:'동일학년 학과평균',data:grade_dept}
			]
		},function(chart){
			chart.setSize($chart[0].clientWidth);
		});

	});

});