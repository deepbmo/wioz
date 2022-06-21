/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * bmo 관리자 UI 이벤트를 처리한다.
 *
 * @file /modules/bmo/admin/scripts/script.js
 * @author oz11
 * @version 3.0.0
 * @modified 2022. 1. 1.
 */
 
 var Bmo = {
	data:{
		
	},
	form:{
		add:function(idx) {
			new Ext.Window({
				id:"",
				items:[
					new Ext.TabPanel({
						items:[
							new Ext.form.Panel({
								items:[
									new Ext.form.FieldSet({
										title:"기본정보",
										items:[
											// fieldLabel: radio_1 (radio_2 radio_2) radio_1
											new Ext.form.RadioGroup({
												id:"ModuleEtcformApplicationFormAddUseWriter",
												fieldLabel:"작성자 사용여부",
												flex:1,
												columns:2,
												hidden:true,
												items:[
													new Ext.form.FieldContainer({
														layout:"hbox",
														items:[
															new Ext.form.Radio({
																boxLabel:"사용",
																name:"use_writer",
																inputValue:'TRUE',
																checked:true
															}),
															new Ext.form.FieldContainer({
																id:"ModuleEtcformApplicationFormAddUseMasking",
																layout:"hbox",
																items:[
																	new Ext.form.DisplayField({
																		value:"(",
																		style:{marginRight:"4px",marginLeft:"8px"},
																	}),
																	new Ext.form.RadioGroup({
																		items:[
																			new Ext.form.Radio({
																				boxLabel:"마스킹 적용",
																				name:"use_masking",
																				inputValue:'TRUE'
																			}),
																			new Ext.form.Radio({
																				boxLabel:"마스킹 미적용",
																				name:"use_masking",
																				inputValue:'FALSE',
																				checked:true,
																				width:90,
																				style:{marginLeft:"8px"}
																			})
																		]
																	}),
																	new Ext.form.DisplayField({
																		value:")",
																		style:{marginLeft:"4px",marginRight:"8px"},
																	})
																]
															})
														]
													}),
													new Ext.form.Radio({
														boxLabel:"미사용",
										                name:"use_writer",
										                inputValue:'FALSE'
									                })
												],
												listeners:{
													change:function(form,value){
														if (value.use_writer === 'FALSE') {
															Ext.getCmp("ModuleEtcformApplicationFormAddUseMasking").setHidden(true).setDisabled(true);
														} else {
															Ext.getCmp("ModuleEtcformApplicationFormAddUseMasking").setHidden(false).setDisabled(false);
														}
													}
												}
											})
										]
									})
								]
							})
						]
					})
				]
			})
		}
	},
  download:{
    // 워드 다운로드
    document:function(document, aidx) {
			new Ext.Window({
				id:"ModuleBmoDownloadDocumentWindow",
				title:"신청서 다운로드",
				width:450,
				modal:true,
				border:false,
				items:[
					new Ext.form.Panel({
						id:"ModuleBmoDownloadDocumentForm",
						bodyPadding:"10 10 5 10",
						border:false,
						items:[
							new Ext.form.Hidden({
								name:"document",
								value:document
							}),
							new Ext.form.Hidden({
								name:"aidx",
								value:aidx
							}),
							new Ext.form.DisplayField({
								value:'현재 신청서를 MS워드 문서파일로 다운로드합니다.<br>문서 파일은 나눔바른고딕 및 나눔명조체에 최적화되어 있습니다. <a href="http://hangeul.naver.com/2016/nanum" style="text-decoration:none; color:#5a96c9;" target="_blank">[나눔글꼴 다운로드]</a>'
							}),
							new Ext.form.CheckboxGroup({
								columns:1,
								items:[
									new Ext.form.Checkbox({
										name:"is_file",
										boxLabel:"신청서에 포함된 첨부파일을 포함하여 다운로드 합니다."
									})
								]
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:"다운로드",
						handler:function() {
							Ext.getCmp("ModuleBmoDownloadDocumentForm").getForm().submit({
								url:ENV.getProcessUrl("bmo","@getDocument"),
								submitEmptyText:false,
								waitTitle:Admin.getText("action/wait"),
								waitMsg:"문서변환중입니다. 잠시만 기다려주십시오...",
								success:function(form,action) {
									Ext.Msg.show({title:Admin.getText("alert/info"),msg:"문서 변환이 완료되었습니다.<br>확인 버튼을 클릭하시면 문서를 다운로드 받을 수 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ModuleBmoDownloadDocumentWindow").close();
										location.href = ENV.getProcessUrl("bmo","@downloadDocument")+"?type=word&file="+action.result.file+"&mime="+action.result.mime+"&file_name="+action.result.file_name;
									}});
								},
								failure:function(form,action) {
									if (action.result.message) {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:"문서변환 중 오류가 발생하였습니다.<br>잠시 후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								}
							});
						}
					}),
					new Ext.Button({
						text:"취소",
						handler:function() {
							Ext.getCmp("ModuleBmoDownloadDocumentWindow").close();
						}
					})
				]
			}).show();
    },
    // pdf 다운
    downloadPdf:function(document, aidx) {
      $.send(ENV.getProcessUrl("bmo","@getDocument"),{document:"timetable",idx:idx},function(result) {
        if (result.success == true) {
          location.href = ENV.getProcessUrl("bmo","@downloadDocument") + "?type=pdf&file=" + result.file + "&mime=" + result.mime + "&file_name=" + result.file_name;
        }
      });
    }
  },
  preview:{

  }
 }