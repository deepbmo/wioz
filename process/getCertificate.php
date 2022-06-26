<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 이수증을 생성한다.
 *
 * @file /modules/bmo/process/getCertificate.php
 * @author oz11
 * @license MIT License
 * @version 3.2.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

$mMember = $this->IM->getModule('member');
$mAttachment = $this->IM->getModule('attachment');
$mCoursemos = $this->IM->getModule('coursemos');

$aidx = Param('aidx');

// 내역이 있는지 확인
$application = $this->getApplicationEx($aidx);
if (!$application) {
	$results->success = false;
	$results->error = '자격증 내역이 없습니다.';
	return;
}

// 해당 프로그램 정보가 있는지 확인
$program = $this->getProgramEx($application->pidx);
if ($program == null) {
	$results->success = false;
	$results->error = $this->getErrorText('NOT_FOUND');
	return;
}

// 해당 프로그램의 자격증 양식이 있는지 확인
if ($program->certificate_form == 0) {
	$results->success = false;
	$results->error = '이 프로그램은 자격증을 발행하지 않습니다.<br>출력 기능을 통해 교육확인증으로 활용하시기 바랍니다.';
	return;
}

// 수료증 발급 가능 여부 확인
if ($application->is_complete != 'TRUE') {
	$results->success = false;
	$results->error = '합격증 내역이 없습니다.';
	return;
}


$errors = array();
$input = new stdClass();
$category = $this->getCategory($program->category);
$topic = $this->getTopic($application->tidx);
$certificate_form = $program->certificate_form ? $this->getCertificateForm($program->certificate_form) : null;
$fields = json_decode($certificate_form->fields);

foreach ($fields as $field) {
	$input->{$field->name} = Request($field->name) ? Request($field->name) : $errors[$field->name] = $this->getErrorText('REQUIRED');
}


if (count($errors) == 0) {
  $ary_data = array();
  $ary_data['TITLE'] = '[프로그램명]';
  $ary_data['NAME'] = '홍길동';
  $ary_data['HAKSA'] = '[학번]';
  $ary_data['INSTITUTION'] = '[단과대학]';
  $ary_data['DEPARTMENT'] = '[학과]';
  $ary_data['GRADE'] = '[학년]';
  $ary_data['EMAIL'] = '[이메일]';
  $ary_data['CELLPHONE'] = '[휴대폰]';
  $ary_data['DATE_REG'] = '[YYYY-MM-DD]';
  $ary_data['BIRTHDAY'] = '[YYYY-MM-DD]';
  $ary_data['DATE_START'] = '[YYYY-MM-DD]';
  $ary_data['DATE_PRINT'] = 'YYYY년 MM월 DD일';
  $ary_data['DATE'] = 'YYYY.MM.DD';
  $ary_data['DATE_YEAR'] = 'YYYY';
  $ary_data['DATE_MONTH'] = 'MM';
  $ary_data['DATE_DAY'] = 'DD';
  $ary_data['STATUS'] = '[상태]';
  $ary_data['CERTIFICATE'] = $format;

  $html = '<style>'.file_get_contents( $this->getModule()->getPath().'/templets/styles/certificate.css' ).'</style>';
  $html .= $certificate_form->contents;

  foreach ($ary_data as $key => $value) {
    $html = str_replace('{'.$key.'}', $value, $html);
  }

} else {
	$results->success = false;
	$results->errors = $errors;
	return;
}


$default_font = 'nanummyeongjobi';
// PDF 클래스 호출 'P' 세로, 'L' 가로
$c_page_orientation = 'P';
if ($certificate_form->size == 'A4-L') {
	$c_page_orientation = 'L';
}

$pdf = new PDF($c_page_orientation,'mm','A4',true,'UTF-8',false);
$pdf->SetCreator($this->getModule()->getConfig('school_name'));
$pdf->SetMargins(15,5);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont($default_font);
$pdf->SetDefaultMonospacedFont($default_font); // 기본폰트
$pdf->AddPage();

if ($certificate_form->background) {
	$o_file = $mAttachment->getFileInfo($certificate_form->background, true);
	if ($o_file) {
		// Image(path, x, y, width, height, type(jpg, png, ...), link)
		if ($certificate_form->size == 'A4-L' || $certificate_form->size == 'A4-P') {
			$pdf->Image($o_file->path,'','',0,0,'','','',true,300,'C');
		} else {
			$pdf->Image($o_file->path, 0, 0);
		}
	}
}

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->SetFontSize(20);
// writeHTMLCell(w, h, x, y, html = '', border = 0, ln = 0, fill = 0, reseth = true, align = '', autopadding = true)
$pdf->writeHTMLCell(0, 0, 10, 250, "대한산업보건협회장", 0, 1, 0, true, "C", true);
$pdf->SetAutoPageBreak(TRUE, 10); //자동 페이지구분 하기




// 
// 밑 부분 코드 정리 필요
// 뷰어 일때의 코드도 추가
//

$document = $mAttachment->getTempFile(true);
// $hash = md5(time().rand(100000,999999)).'.pdf';

// Output(path, type); type: I(뷰어), F(저장)
// $pdf->Output($document.'/'.$hash, 'F');
$pdf->Output($document, 'F');

$file_name = '수료증';
$mime = 'application/pdf';
$file = basename($document);

$results->success = true;
$results->file = $file;
$results->mime = $mime;
$results->file_name = file_name;
?>