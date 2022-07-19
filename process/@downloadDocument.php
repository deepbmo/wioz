<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 문서를 생성하고 가져온다.
 *
 * @file /modules/bmo/process/@downloadDocument.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.4.0
 * @modified 2019. 3. 13.
 */
if (defined('__IM__') == false) exit;

$mAttachment = $this->IM->getModule('attachment');

$file = Param('file');
$type = Request('type');
$mime = Request('mime');
$file_name = Request('file_name');

$title = $file_name.'_'.date('Y-m-d',time());


if ($mime == 'application/zip') {

	$mAttachment->tempFileDownload($file,true,$title.'.zip');

} else {

	if ($type == 'word') { // word
		$mAttachment->tempFileDownload($file,true,$title.'.docx');
	} else { // pdf
		$mAttachment->tempFileDownload($file,true,$title.'.pdf');
	}

}

exit;

?>