<?php
/**
 * 이 파일은 가천대모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 교과할동 대시보등의 이번주 데이터를 가져온다
 *
 * @file /modules/gachon/widgets/ems_course_weekly/index.php
 * @version 3.3.0
 * @modified 2019. 11. 12.
 */
if (defined('__IM__') == false) exit;

$IM->addHeadResource('style',$Module->getDir().'/styles/slick.min.css');
$IM->addHeadResource('script',$Module->getDir().'/scripts/slick.min.js');
$IM->loadWebFont('FontAwesome');
$IM->loadWebFont('XEIcon');

$mode = $Widget->getValue('mode');
$limit = $Widget->getValue('limit');
$target = $Widget->getValue('target');
$type = $Widget->getValue('type');

$mCoursemos = $IM->getModule('coursemos');
$mMember = $IM->getModule('member');
$current = $mCoursemos->getCurrentSemester();

$midx = $mMember->getLogged();

$Widget->setAttribute("data-mode",$mode);
$Widget->setAttribute("data-limit",$limit);
$Widget->setAttribute("data-target",$target);

if ($IM->cache()->check('widget','pusan.course_weekly','individual.'.$midx) < time() - 300) {
// if (1) {

	$lists = array();
	$mCourse = $IM->getModule('course');
	$mMember = $IM->getModule('member');
	$member = $IM->getModule('member')->getMember();

	$column = 'l.title, l.year, l.semester, l.idx, m.name';
	$lists = $mCourse->db()->select($mCourse->getTable('lecture').' l',$column);
	$lists->join($mCourse->getTable('member').' cm','cm.lidx = l.idx','LEFT');
	$lists->join($mMember->getTable('member').' m','m.idx = l.pidx','LEFT');
	$lists->where('cm.midx',$midx);
	$lists->where('l.year',$current->year);
	$lists->where('l.semester',$current->semester);
	$lists->orderBy('l.year','desc');
	$lists = $lists->get();

	$ary_years = array();
	for ($i=$current->year; $i>($current->year - 10); $i--) {
		$ary_years[] = $i;
	}

	$cache = new stdClass();
	$cache->lists = $lists;
	$cache->years = $ary_years;
	
	$IM->cache()->store('widget','pusan.course_weekly','individual.'.$midx,json_encode($cache,JSON_UNESCAPED_UNICODE));
} else {
	$cache = json_decode($IM->cache()->get('widget','pusan.course_weekly','individual.'.$midx));
	$lists = array();
	foreach ($cache->lists as $key => $value) {
		$one = $value;
		$lists[] = $one;
	}
	$ary_years = $cache->years;
}

return $Widget->getTemplet()->getContext('index',get_defined_vars());
?>