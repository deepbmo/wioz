<?php
/**
 * 이 파일은 iModule 기반으로 하는 bmo 모듈 입니다. (https://www.imodule.kr)
 *
 * 버전 변경에 따른 변경 처리
 *
 * @file /modules/bmo/process/@migrateVersion.php
 * @author oz11
 * @license MIT License
 * @version 3.0.0
 * @modified 2022. 7. 12.
 */
if (defined('__IM__') == false) exit;


$i_start = Request('start')?Request('start'):0;

$i_install_version = $this->getVersionInstall();
$i_now_version = $this->getModule()->getConfig('last_check_version');

if ($i_now_version < 301) $i_start = $i_start | 1;


$i_cnt_content = 1;
if ($i_start & 1) {
	$lists = $this->db()->select($this->table->type); // 수정해야 하는 테이블 (헤더 길이 설정하기 위해)
	$lists = $lists->count();
	$i_cnt_content += intval($lists);
}

$i_cnt_content += 10;
header("Content-Length:".$i_cnt_content);


if ($i_start & 1) {
    // INCLUDE $this->getModule()->getPath().'/includes/version_inc_0301.php';

	$lists = $this->db()->select($this->table->type);
	$lists = $lists->get();
	for ($i=0, $loop=count($lists); $i<$loop; $i++) {
        $update = array();
        $update['iidx'] = $lists[$i]->iidx ? $lists[$i]->iidx : '*';
        $update['didx'] = '*';
        $this->db()->update($this->table->type,$update)->where('idx',$lists[$i]->idx)->execute();

		echo '.';
	}
}

$this->getModule()->setConfig('last_check_version', $i_install_version);


echo '......................';
$results->success = true;

ForceFlush();

?>