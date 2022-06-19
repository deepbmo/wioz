<?php

$aidx = Param('aidx');

$application = $this->getApplication($type,$aidx);

if (!$application) {
  $results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}


$member = $mMember->getMember($application->midx);
$title = '[신청서]'.$member->name.'_'.date('Y-m-d',time());
$attachments = array();

$is_file = Request('is_file') ? true : false;


$PHPWord = new PHPWord();
$PHPWord->loadFile($this->getModule()->getPath().'/documents/exam.docx');


$PHPWord->replaceVariableByText('NAME',$application->name);
$PHPWord->replaceVariableByText('HAKSA',$application->haksa);

// 반복되는 부분
$costs = array();
$loopnum = 1;

foreach($lists as $list) {
  $costs[] = array(
		'NO'=>$loopnum,
		'NAME'=>$list->name,
		'ASSIGN'=>$list->assign,
		'BIRTH'=>$list->birth,
		'TYPE'=>$type,
		'TIME'=>$list->time,
		'COST'=>$list->cost.'원',
		'TRANSPORTATION_COST'=>$list->transportation_cost.'원',
		'SUM'=>$list->sum.'원',
		'WITHHOLDING'=>$list->withholding,
		'TOTAL'=>$list->total.'원',
		'BANK'=>$list->bank,
		'ACCOUNT'=>$list->account
		
	);
	$count++;
}

$PHPWord->replaceTableVariable($costs,array('parseLineBreaks'=>true));


$PHPWord->properties(array('creator'=>$application->name,'title'=>$title,'description'=>'','lastModifiedBy'=>'','Company'=>''));


$document = $mAttachment->getTempFile(true);
$PHPWord->saveFile($document);


if ($is_file) {
	$files = $this->db()->select($this->table->application_file);
  $files->where('type',strtoupper($type));
  $files->where('aidx',$aidx);
  $files = $files->get();
  
	for ($i=0; $i<count($files); $i++) {
		$file = $mAttachment->getFileInfo($files[$i]->idx,true);
		if ($file != null) {
			$attachments['증빙파일/'.$file->name] = $file->path;
		}
	}
}

if (count($attachments) > 0) {
	$docFile = '[신청서]'.$member->name.'_'.date('Y-m-d',time());
	$zip = $mAttachment->getTempFile(true);

	$mZip = new ZipArchive();
	$mZip->open($zip,ZipArchive::CREATE);
	$mZip->addFile($document,$docFile.'.docx');
	foreach ($attachments as $path=>$file) {
		$mZip->addFile($file,$path);
	}
	$mZip->close();

	unlink($document);
	$mime = 'application/zip';
	$file = basename($zip);

} else {
	$mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
	$file = basename($document);
}


$results->success = true;
$results->type = $type;
$results->aidx = $aidx;
$results->midx = $application->midx;
$results->file = $file;
$results->mime = $mime;

?>