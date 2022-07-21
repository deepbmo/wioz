<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 교직원 리스트를 불러온다.
 *
 * @file /modules/bmo/process/getSearchEmployee.php
 * @author oz
 * @license MIT License
 * @version 3.4.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

$mCoursemos = $this->IM->getModule('coursemos');

$keyword = Request('keyword');
$page = Request('page') ? Request('page') : 1;


$lists = array();
if ($keyword) {
	$c_qry = "
	EXEC APP_검색_교직원검색_조회 '%{$keyword}%|@|15|@|{$page}'
	";
	$lists = $mCoursemos->setHaksaData($c_qry);
}

$is_more = count($lists) > 0 ? $lists[0]->page_count != $lists[0]->page_no : false;


$results->success = true;
$results->lists = $lists;
$results->is_more = $is_more;
?>