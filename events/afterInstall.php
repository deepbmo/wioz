<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * afterInstall 이벤트를 처리한다.
 *
 * @file /modules/bmo/events/afterInstall.php
 * @author oz
 * @license MIT License
 * @version 3.0.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

// 모듈을 설치 또는 업데이트 했을 경우 실행
if ($mode == 'install' || $mode == 'update') {
	$mBmo = $IM->getModule('bmo');
	
	// 모듈 설치 및 업데이트시에 회원테이블의 auto_increment 가 100,000,000 이하인 경우, 100,000,000 으로 설정한다.
	$auto_increment = $IM->db()->rawQuery('SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?',array($Target->db()->db()->database,$Target->db()->getTable($mBmo->getTable('member'))));
	$auto_increment = $auto_increment[0]->AUTO_INCREMENT;
	if ($auto_increment < 100000000) {
		$mBmo->db()->rawQuery('ALTER TABLE '.$mBmo->db()->getTable($mBmo->getTable('member')).' AUTO_INCREMENT=100000000');
	}
}
?>