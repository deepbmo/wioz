<?php
/**
 * 이 파일은 iModule 기반으로 하는 bmo 모듈 입니다. (https://www.imodule.kr)
 *
 * 버전 변경에 따른 변경 처리
 *
 * @file /modules/bmo/process/@checkMigrate.php
 * @author oz11
 * @license MIT License
 * @version 3.0.0
 * @modified 2022. 7. 12.
 */
if (defined('__IM__') == false) exit;

$i_install_version = $this->getVersionInstall();

$results->success = true;


$i_last_ver = $this->getModule()->getConfig('last_check_version');
$results->last_ver = $i_last_ver;

if ($i_install_version >= 300) {
	if ($i_last_ver < 301) {
		$results->success = false;
		$results->version = $i_install_version;
	}
}

$results->version_msg = '';
if(!$results->success) {
	foreach ($this->getText('version') as $key => $value) {
		$tmp_version = $this->getVersionToInt($key);
		if( $i_install_version >= $tmp_version && $i_last_ver < $tmp_version) {
			$results->version_msg .= '<div>- '.$value.'[Ver '.$key.']</div>';
		}
	}
}

?>