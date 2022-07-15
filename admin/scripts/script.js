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
	/**
	 * 교과관리
	 */
	 lecture:{
		/**
		 * 교과목 생성
		 */
		add:function() {
			new Ext.Window({
				id:"ModuleCourseLectureAddWindow",
				title:"교과목 생성",
				width:500,
				modal:true,
				border:false,
				layout:"fit",
				bodyPadding:"10 10 5 10",
				items:[
					new Ext.form.Panel({
						id:"ModuleCourseLectureAddForm",
						border:false,
						fieldDefaults:{labelAlign:"right",labelWidth:70,anchor:"100%",allowBlank:false},
						items:[
							new Ext.form.ComboBox({
								name:"year",
								fieldLabel:"학년도",
								store:new Ext.data.JsonStore({
									proxy:{
										type:"ajax",
										simpleSortMode:true,
										url:ENV.getProcessUrl("course","@getYears"),
										extraParams:{},
										reader:{type:"json"}
									},
									remoteSort:true,
									sorters:[{property:"title",direction:"ASC"}],
									autoLoad:false,
									fields:["display","value"],
									listeners:{
										load:function(store,records,success,e) {
											if (success == false) {
												if (e.getError()) {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												} else {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											}
										}
									}
								}),
								flex:1,
								editable:false,
								displayField:"display",
								valueField:"value",
								value:"",
								listeners:{
								}
							}),
							new Ext.form.ComboBox({
								name:"semester",
								fieldLabel:"학기",
								store:new Ext.data.ArrayStore({
									fields:["display","value"],
									data:(function() {
										var datas = [];
										for (var type in Coursemos.getText('semester')) {
											datas.push([Coursemos.getText('semester/'+type) ,type]);
										}
										return datas;
									})()
								}),
								flex:1,
								editable:false,
								displayField:"display",
								valueField:"value",
								listeners:{
								}
							}),
							new Ext.form.FieldContainer({
								layout:"hbox",
								items:[
									new Ext.form.Hidden({
										name:"iidx"
									}),
									new Ext.form.Hidden({
										name:"didx"
									}),
									new Ext.form.TextField({
										name:"department",
										fieldLabel:"학과",
										editable:false,
										flex:1,
										emptyText:"우측의 버튼을 클릭하여 학과를 검색하세요."
									}),
									new Ext.Button({
										iconCls:"xi xi-magnifier",
										text:"학과검색",
										style:{marginLeft:"10px"},
										width:100,
										handler:function() {
											Course.etc.search('department',function(result) {
												Ext.Msg.show({title:Admin.getText("alert/info"),msg:result.department+"을(를) 선택하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
													if (button == "ok") {
														var c_department = result.institution + '/' + result.department
														Ext.getCmp("ModuleCourseLectureAddForm").getForm().findField("iidx").setValue(result.iidx);
														Ext.getCmp("ModuleCourseLectureAddForm").getForm().findField("didx").setValue(result.didx);
														Ext.getCmp("ModuleCourseLectureAddForm").getForm().findField("department").setValue(c_department);
													}
												}});
											});
										}
									})
								],
							}),
							new Ext.form.FieldContainer({
								layout:"hbox",
								items:[
									new Ext.form.Hidden({
										name:"midx"
									}),
									new Ext.form.TextField({
										name:"haksa",
										fieldLabel:"교수",
										editable:false,
										flex:1,
										emptyText:"우측의 버튼을 클릭하여 회원을 검색하세요."
									}),
									new Ext.Button({
										iconCls:"xi xi-magnifier",
										text:"사번검색",
										style:{marginLeft:"10px"},
										width:100,
										handler:function() {
											Coursemos.member(function(member) {
												var value = member.name + '(' + member.haksa + ')';
												Ext.getCmp("ModuleCourseLectureAddForm").getForm().findField("midx").setValue(member.idx);
												Ext.getCmp("ModuleCourseLectureAddForm").getForm().findField("haksa").setValue(value);
											});
										}
									})
								],
							}),
							new Ext.form.TextField({
								fieldLabel:"교과명",
								name:"title"
							}),
							new Ext.form.TextField({
								fieldLabel:"교과코드",
								name:"code"
							}),
							new Ext.form.TextField({
								fieldLabel:"학점",
								name:"credit"
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:Admin.getText("button/save"),
						handler:function() {
							Ext.getCmp("ModuleCourseLectureAddForm").getForm().submit({
								url:ENV.getProcessUrl("course","@saveLecture"),
								submitEmptyText:false,
								waitTitle:Admin.getText("action/wait"),
								waitMsg:Admin.getText("action/saving"),
								success:function(form,action) {
									Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ModuleCourseLectureList").getStore().reload();
										Ext.getCmp("ModuleCourseLectureAddWindow").close();
									}});
								},
								failure:function(form,action) {
									if (action.result) {
										if (action.result.message) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								}
							});
						}
					}),
					new Ext.Button({
						text:Admin.getText("button/cancel"),
						handler:function() {
							Ext.getCmp("ModuleCourseLectureAddWindow").close();
						}
					})
				],
				listeners:{
					show:function() {

					}
				}
			}).show();
		}
	},
	etc:{
		search:function(type,callback) {
			if (type == 'department') {
				new Ext.Window({
					id:"ModuleCourseDepartmentSearchWindow",
					title:"학과검색",
					width:700,
					height:500,
					modal:true,
					autoScroll:true,
					border:false,
					layout:"fit",
					items:[
						new Ext.grid.Panel({
							id:"ModuleCourseDepartmentSearchResult",
							border:false,
							tbar:[
								new Ext.form.TextField({
									id:"MModuleCourseDepartmentSearchKeyword",
									width:140,
									emptyText:"학과명",
									enableKeyEvents:true,
									flex:1,
									listeners:{
										keyup:function(form,e) {
											if (e.keyCode == 13) {
												Ext.getCmp("ModuleCourseDepartmentSearchResult").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("MModuleCourseDepartmentSearchKeyword").getValue());
												Ext.getCmp("ModuleCourseDepartmentSearchResult").getStore().loadPage(1);
											}
										}
									}
								}),
								new Ext.Button({
									iconCls:"mi mi-search",
									handler:function() {
										Ext.getCmp("ModuleCourseDepartmentSearchResult").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("MModuleCourseDepartmentSearchKeyword").getValue());
										Ext.getCmp("ModuleCourseDepartmentSearchResult").getStore().loadPage(1);
									}
								})
							],
							store:new Ext.data.JsonStore({
								proxy:{
									type:"ajax",
									simpleSortMode:true,
									url:ENV.getProcessUrl("course","@getSearchDepartments"),
									reader:{type:"json"}
								},
								remoteSort:true,
								sorters:[{property:"idx",direction:"ASC"}],
								autoLoad:false,
								pageSize:50,
								fields:["idx","institution","department"],
								listeners:{
									load:function(store,records,success,e) {
										if (success == false) {
											if (e.getError()) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("LOAD_DATA_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										}
									}
								}
							}),
							columns:[{
								text:"단과대학",
								dataIndex:"institution",
								width:100,
								flex:1
							},{
								text:"학과",
								dataIndex:"department",
								width:100,
								flex:1
							}],
							selModel:new Ext.selection.CheckboxModel({mode:"SINGLE"}),
							bbar:new Ext.PagingToolbar({
								store:null,
								displayInfo:false,
								items:[
									"->",
									{xtype:"tbtext",text:"대상을 더블클릭하거나 선택 후 확인버튼을 클릭하여 주십시오."}
								],
								listeners:{
									beforerender:function(tool) {
										tool.bindStore(Ext.getCmp("ModuleCourseDepartmentSearchResult").getStore());
									}
								}
							}),
							listeners:{
								itemdblclick:function(grid,record) {
									callback(record.data);
									Ext.getCmp("ModuleCourseDepartmentSearchWindow").close();
								}
							}
						})
					],
					buttons:[
						new Ext.Button({
							text:"확인",
							handler:function() {
								if (Ext.getCmp("ModuleCourseDepartmentSearchResult").getSelectionModel().getSelection().length == 0) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:"선택된 학과가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									callback(Ext.getCmp("ModuleCourseDepartmentSearchResult").getSelectionModel().getSelection().pop().data);
									Ext.getCmp("ModuleCourseDepartmentSearchWindow").close();
								}
							}
						}),
						new Ext.Button({
							text:"취소",
							handler:function() {
								Ext.getCmp("ModuleCourseDepartmentSearchWindow").close();
							}
						})
					]
				}).show();
			}
		}
	}
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