<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 문서를 생성하고 가져온다.
 *
 * @file /modules/bmo/process/@getDocument.php
 * @author oz11
 * @license MIT License
 * @version 3.4.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

$mCoursemos = $this->IM->getModule('coursemos');
$mAttachment = $this->IM->getModule('attachment');
$mMember = $this->IM->getModule('member');

$document = Param('document');


if ($document == 'exam') {
	REQUIRE_ONCE $this->getModule()->getPath().'/includes/inc_process_getDocument_exam.php';

} elseif ($document == 'ip') {
	REQUIRE_ONCE $this->getModule()->getPath().'/includes/inc_process_getDocument_ip.php';
  
} elseif ($document == 'timetable_pdf') {
	REQUIRE_ONCE $this->getModule()->getPath().'/includes/inc_process_getDocument_timetable_pdf.php';
  
}

?>