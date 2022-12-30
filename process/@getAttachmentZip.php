<?php
/**
 * 이 파일은 기업교육모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 사업장등록신청 첨부파일을 가져온다.
 *
 * @file /modules/bizedu/process/@getWorkplaceApplicationDownload.php
 * @author oz
 * @license MIT License
 * @version 3.4.0
 * @modified 2022. 8. 4.
 */
if (defined('__IM__') == false) exit;

$mAttachment = $this->IM->getModule('attachment');

$aidxes = Param('aidxes');

$ary_aidxes = explode(',',$aidxes);
$o_application = $this->db()->select($this->table->business)->where('idx',$ary_aidxes,'IN')->get();

if (!$o_application) {
    $results->success = false;
    $results->message = $this->getErrorText('NOT_FOUND');
    return;
}


$ary_fidxes = array();
foreach ($o_application as $application) {
    $ary_fidxes[$application->business_name] = $application->files;
}



$attachments = array();

foreach ($ary_fidxes as $business_name => $item) {
    $fidxes = explode('|',$item);
    if (count($fidxes) > 0) {
        foreach ($fidxes as $fidx) {
            $file = $mAttachment->getFileInfo($fidx,true);
            if ($file && is_file($file->path)) $attachments[$business_name.'/'.$file->name] = $file->path;
        }
    }
}

$title = '사업장등록신청';


if (count($attachments) > 0) {
	$zip = $mAttachment->getTempFile(true);

	$mZip = new ZipArchive();
	$mZip->open($zip,ZipArchive::CREATE);
	foreach ($attachments as $path=>$file) {
        $mZip->addFile($file,$path);
    }
	$mZip->close();

	$mime = 'application/zip';
	$file = basename($zip);
} else {
    $results->success = false;
    $results->message = '첨부파일이 없습니다.';
    return;
}


$results->success = true;
$results->file = $file;
$results->mime = $mime;
$results->file_name = $title;

?>