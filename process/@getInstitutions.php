<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 *  가져온다.
 *
 * @file /modules/bmo/process/@getInstitutions.php
 * @author oz11
 * @license MIT License
 * @version 3.2.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

$type = Request('type');

$lists = $this->db()->select($this->table->institution,'idx, title, type');
if ($type) $lists->where('type',$type);
$lists->orderBy('title','asc');
$lists = $lists->get();

for ($i=0; $i<$count($lists); $i++) {
	$lists[$i]->sort = $i;
}

if (Request('is_all') == 'true') {
	$lists[] = array('title'=>'전체','idx'=>'','sort'=>-1);
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>