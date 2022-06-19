<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 엑셀변환
 *
 * @file /modules/bmo/process/@getExcel.php
 * @author oz11
 * @license MIT License
 * @version 3.4.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

ini_set('memory_limit', -1);

$document = Param('document');

$mMember = $this->IM->getModule('member');
$mCoursemos = $this->IM->getModule('coursemos');
$mAttachment = $this->IM->getModule('attachment');

$attachments = array();


if ($document == 'student') {
  REQUIRE_ONCE $this->getModule()->getPath().'/includes/inc_process_getExcel_student.php';
} elseif ($document == 'mentoring') {
  REQUIRE_ONCE $this->getModule()->getPath().'/includes/inc_process_getExcel_mentoring.php';
}


if (count($attachments) > 0) {
	$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
	$excel = $mPHPExcelWriter->WriteExcel($fileName.'.xlxs');

	$mZip = new ZipArchive();
	$mZip->open($mAttachment->getTempPath(true).'/'.$fileName,ZipArchive::CREATE);
	foreach ($attachments as $path=>$file) {
		$mZip->addFile($file,$path);
	}

	$mZip->addFile($excel,$title.'.xlsx');
	$mZip->close();

	@unlink($excel);
} else {
	$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
	$excel = $mPHPExcelWriter->WriteExcel($fileName);
}

$mCoursemos->ozExtra->checkCountFlush('END');

?>