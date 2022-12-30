/**
 * 이 파일은 iModule bmo 모듈의 일부입니다.
 *
 * 모듈내 UI이벤트처리를 위한 자바스크립트
 *
 * @file /modules/bmo/scripts/script.js
 * @author oz11
 * @license MIT License
 * @version 3.0.0
 * @modified 2022.
 */

// link
// '/ko/module/모듈명/컨테이너명/view/idxes[0]/idxes[1]'
// '/ko/module/모듈명/@컨테이너명/view/idxes[0]/idxes[1]' @: defined('__IM_CONTAINER_POPUP__') == true
// 'baseUrl' + view (getView) 👉🏻  Bmo.getUrl('list');

var Bmo = {
	oDATA:{
		
	},
	getUrl:function(view,idx) {
		var url = $("div[data-module=bmo]").attr("data-base-url") ? $("div[data-module=bmo]").attr("data-base-url") : ENV.getUrl(null,null,false);
		if (!view || view == false) return url;
		url+= "/"+view;
		if (!idx || idx == false) return url;
		return url+"/"+idx;
	},
	init:function(id) {
		var $form = $("#"+id);

	},
	application:{
		init:function(id) {
			if (id == 'ModuleBmoApplicationForm') {
				// 버튼 action
				$("button[data-action]",$form).on("click",function() {
					var action = $(this).attr('data-action');
			
					if (action == 'certificate') {
						var pidx = $(this).attr('data-pidx');
						var tidx = $(this).attr('data-tidx');

						// 컨텍스트 이동 (window popup)
						// openPopup(url,width,height,scroll,name) scroll: 스크롤바 여부, name: 창이름
						iModule.openPopup(ENV.getModuleUrl("eco","@certificate","view",pidx+"/"+tidx),800,800,1,"certificate_"+pidx+"_"+tidx);  

						
					} else if (action == 'modify') {
						var aidx = $(this).attr('data-aidx');
			
						Bmo.application.modify(aidx);
					} else if (action == 'search') { // 검색 (쿼리스트링)
						var year = $("select[name=year]").val();
						var semester = $("select[name=semester]").val();
						var keyword = $("input[name=keyword]").val();

						var ary_queryString = [];
						var queryString = '';

						if (year) ary_queryString.push("year=" + year);
						if (semester) ary_queryString.push("semester=" + semester);
						if (keyword) ary_queryString.push("keyword=" + keyword);
						if (ary_queryString.length > 0) queryString = ary_queryString.join("&");

						location.href = ENV.getModuleUrl('bmo','application','list')+ "?" + queryString;
					} else if (action == 'save') {
						iModule.modal.show("안내",'<div data-role="message">제출 후에는 다시 수정할 수 없습니다.<br>제출를 하시겠습니까?.</div>', {},
							[
								{text:"취소",class:"cancel",click:"close"},
								{text:"제출",class:"submit",click:function(){
									$(this).status("loading");
									$("input[name=status]",$form).val('END');
									Bmo.application.submit($form);
								}}
							]
						);
					} else if (action == 'sms') {
						var midxes = [];
						$("input[type=checkbox][name='idxes[]']:checked",$form).each(function() {
							var value = parseInt($(this).closest('li').find('span.name').attr('data-midx'));
							midxes.push(value);
						});

						if (midxes.length == 0) {
							iModule.modal.alert("안내","SMS를 발송할 대상을 선택해 주십시오.");
						} else {
							Bmo.sendPopup(midxes);
						}
					}
				});

				// 검색 (GET 방식 폼 전송)
				$("div.toolbar > select.search",$form).on("change",function() {
					$form.attr("method","GET");
					$form.submit();
				});

				// 정렬
				$("span.btn-sort",$form).on("click",function(){
					var sortType = $(this).attr('data-sort');
					$("input[name=sort_type]").val(sortType);

					if ($("input[name=dir]").val() == 'asc') {
						$("input[name=dir]").val('desc');
					} else {
						$("input[name=dir]").val('asc');
					}

					$form.attr("method","GET");
					$form.submit();
				});

				// 엔터 검색
				$("input[name=keyword",$form).on("keydown",function(e) {
					if (e.keyCode === 13) {
						e.preventDefault();
						$(this).parent().next().trigger('click');
					}
				});

				$form.inits(Bmo.application.submit);
			}
		},
		modify:function(idx) {
			// process
			$.send(ENV.getProcessUrl("bmo","saveApplication"),{idx:idx},function(result) { // idx 값은 Param('idx') 으로 받음.
				if (result.success == true) {
				} else {
				}
			});

			// iModule.modal.confirm
			iModule.modal.confirm("안내","통학버스 탑승시간을 변경하시겠습니까?",function($button) {
				if ($button.attr("data-action") == "ok") {
					$button.status("loading");
					$.send(ENV.getProcessUrl("pusan","changeAppSchoolpass"),{
						term:term,
						chasu:chasu,
						kind:kind,
						haksa:haksa,
						location_cd:location_cd,
						time:time
					},function(result) {
						if (result.success == true) {
							iModule.modal.show("안내",'<div data-role="message">'+result.message+'</div>',{},[{text:"확인",class:"submit",click:function() {
								window.location.reload();
								iModule.modal.close();
							}}]);
						} else {
							iModule.modal.alert("안내",result.message);
						}
					});
				} else {
					iModule.modal.close();
				}
			});
		},
		submit:function ($form) {
			$form.send(ENV.getProcessUrl("bmo","saveApplication"),function(result) {
				if (result.success == true) {
					iModule.modal.show('안내','<div data-role="message">정상적으로 저장되었습니다.</div>',{},[{text:"확인",class:"submit",click:function(){
						// 링크 이동 (getUrl)
						location.href = Bmo.getUrl("list");
					}}]);
				}
			});
		}
	},
	certificate:{
		download:function($form) {
			var pidx = $("input[name=pidx]",$form).val();
			var aidx = $("input[name=aidx]",$form).val();
			var midx = $("input[name=midx]",$form).val();
			var start_time = $("input[name=start_time]",$form).val();

			$.send(ENV.getProcessUrl("bmo","getCertificate"),{pidx:pidx,aidx:aidx,start_time:start_time,midx:midx},function(result) {
				if (result.success == true) {
					window.open(ENV.getProcessUrl("bmo","downloadDocument") + "?file=" + result.file + "&mime=" + result.mime + "&file_name=" + result.file_name);
					iModule.modal.close();
				}
				return false;
			});
		}
	},
	diagnosis:{
		init:function(id) {
			var $form = $("#"+id);

			if (id == 'ModuleBmoDiagnosisResultForm') {

				$("button[data-action]",$form).on("click",function() {
					var action = $(this).attr('data-action');

					if (action == 'compare') {
					var type = $(this).attr('data-type');
					Bmo.diagnosis.chart.compare($form, type);
					}
				});
			}
		},
		chart:{
			compare:function($form, type) {
				var $chart = $("div[data-role=chart][data-chart=compare]",$form);

				$chart.empty();
				$chart.append($("<div>").addClass("loading").append($("<i>").addClass("mi mi-loading")));

				var idx = $("input[name=idx]",$form).val();
				var midx = $("input[name=midx]",$form).val();

				$.send(ENV.getProcessUrl("bmo","getDiagnosisResultChartData"),{idx:idx, midx:midx, mode:"compare", type:type},function(result) {
					if (result.success == true) {
						var legend = $("input[name=legend]",$form).val().split(",");
						var point = JSON.parse($("input[name=point]",$form).val());
						var $yAxis = {
							gridLineInterpolation:'polygon',
							lineWidth:0,
							tickInterval:20
						};
						if ($("input[name=type]",$form).val() == "A-POINT") {
							$yAxis = {
								gridLineInterpolation:'polygon',
								lineWidth:0,
								min:0,
								max:5
							};
						}
						$chart.empty();
						$chart.highcharts({
							chart:{polar:true,type:"area",backgroundColor:"transparent",style:{fontFamily:"inherit",fontSize:"12px", width:"100%"}},
							title:null,
							xAxis:{categories:legend,tickmarkPlacement:"on",lineWidth:0,labels:{distance:15,padding:0,reserveSpace:false}},
							yAxis:$yAxis,
							tooltip:{headerFormat:"",pointFormat:"<span style=\"font-family:inherit; font-size:12px;\">{series.name}: <b>{point.y:0f}점</b>"},
							legend:{},
							plotOptions:{
								area:{
									marker:{enabled:true},
									pointPlacement:"on",
									fillOpacity:0,
									lineWidth:1
								}
							},
							pane:{size:"80%"},
							series:[{name:result.title,data:result.points,color:"#F44336",pointPlacement:"on"},{name:"나의 진단결과",data:point,color:"#2196F3"}]
						});
					}
				});

			}
		}
	}
}

 // getModal - iModule.modal.get()