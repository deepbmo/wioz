<?php
/**
 * 이 파일은 포트폴리오모듈의 일부입니다. (http://www.coursemos.kr)
 * 
 * 워크숍활동내역 첨부파일을 가져온다.
 *
 * @file /modules/portfolio/process/getWorkshopFiles.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.3.0
 * @modified 2019. 12. 14.
 */
if (defined('__IM__') == false) exit;

$idx = Request('idx') ? Decoder(Request('idx')) : false;
if ($idx == false) {
	$results->success = false;
} else {
	$files = array();
	$module = Request('module');
	$target = Request('target');
	$lists = $this->db()->select($this->table->workshop_file)->where('pidx',$idx)->where('midx',$this->IM->getModule('member')->getLogged())->get();
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$file = $this->IM->getModule('attachment')->getFileInfo($lists[$i]->idx);
		if ($file != null && ($module == null || $module == $file->module) && ($target == null || $target == $file->target)) {
			$files[] = $file;
		}
	}
	
	$results->success = true;
	$results->files = $files;
}
?>