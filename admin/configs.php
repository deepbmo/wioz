<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * bmo 모듈의 모듈 환경설정 패널을 가져온다.
 * 
 * @file /modules/bmo/admin/configs.php
 * @author oz11
 * @license GPLv3
 * @version 3.1.0
 * @modified 2022. 7. 14.
 */
if (defined('__IM__') == false) exit;
?>
<script>
new Ext.form.Panel({
	id:"ModuleConfigForm",
	border:false,
	bodyPadding:10,
	width:600,
	fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:true},
	items:[
		new Ext.form.Hidden({
			name:"install_version"
		})
	],
	listeners:{
		actioncomplete:function(form,action) {
			if (action.type == "submit") {
				$.send(ENV.getProcessUrl("bmo","@checkMigrate"),{},function(result) {
					if (result.success == false) {

						Ext.Msg.show({title:Admin.getText("alert/info"),msg:"버전 변경으로 인한 데이터 갱신이 있습니다. 갱신하시겠습니까?"+result.version_msg,buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
							if (button == "ok") {

								new Ext.Window({
									id:"ModuleConfigsMigrateProgressWindow",
									title:"데이터 변환중 ...",
									width:500,
									modal:true,
									bodyPadding:5,
									closable:false,
									items:[
										new Ext.ProgressBar({
											id:"ModuleConfigsMigrateProgressBar"
										})
									],
									listeners:{
										show:function() {
											Ext.getCmp("ModuleConfigsMigrateProgressBar").updateProgress(0,"데이터 준비중입니다. 잠시만 기다려주십시오.");
											$.ajax({
												url:ENV.getProcessUrl("bmo","@migrateVersion"),
												method:"POST",
												timeout:0,
												data:{},
												xhr:function() {
													var xhr = $.ajaxSettings.xhr();
													xhr.addEventListener("progress",function(e) {
														if (e.lengthComputable) {
															Ext.getCmp("ModuleConfigsMigrateProgressBar").updateProgress(e.loaded/e.total,Ext.util.Format.number(e.loaded - 1,"0,000")+" / "+Ext.util.Format.number(e.total,"0,000")+" ("+(e.loaded / e.total * 100).toFixed(2)+"%)",true);
														}
													});
													return xhr;
												},
												success:function(result,b,xhr) {
													Ext.getCmp("ModuleConfigsMigrateProgressBar").updateProgress(1,"데이터 변경완료되었습니다.",true);
													Ext.getCmp("ModuleConfigsMigrateProgressWindow").close();
													Ext.Msg.show({title:Admin.getText("alert/info"),msg:"데이터 변경완료되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("ModuleList").getStore().reload();
													Ext.getCmp("ModuleConfigsWindow").close();
												},
												error:function() {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:"데이터 변경 중 에러가 발생하였습니다. 잠시후 다시 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													Ext.getCmp("ModuleConfigsMigrateProgressWindow").close();
												}
											});
										}
									}
								}).show();

							}
						}});
					}
				});
			}
		},
		beforeaction:function(form,action) {
			if (action.type == "submit") {
				Ext.getCmp("ModuleConfigForm").getForm().findField("install_version").setValue("<?php echo $this->getModule()->getPackage()->version;?>");
			}
		}
	}
});
</script>