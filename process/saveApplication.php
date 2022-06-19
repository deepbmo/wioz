<?php
/**
 * 이 파일은 iModule bmo 모듈 일부입니다. (http://www.coursemos.kr)
 *
 * 신청서를 저장한다.
 *
 * @file /modules/bmo/process/saveApplication.php
 * @author oz11
 * @license MIT License
 * @version 3.0.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

$errors = array();

$idx = Param('idx');
$midx = Param('midx');

$check = $this->db()->select($this->table->application);
$check->where('idx',$idx);
$check->where('midx',$midx);
$check = $check->has();

if ($check) {
	$results->success = false;
	$results->message = $this->getErrorText('DUPLICATED');
	return;
}

$cellphone = RequestXss('cellphone') && CheckPhoneNumber(RequestXss('cellphone')) == true ? GetPhoneNumber(RequestXss('cellphone')) : $errors['cellphone'] = $this->getErrorText('REQUIRED');
$email = RequestXss('email') ? RequestXss('email') : $errors['email'] = $this->getErrorText('REQUIRED');


if (count($errors) == 0) {
  $extra = new stdClass();
  $extra->foreigner = $foreigner;
  $extra->minority = $minority;
  $extra_json = json_encode($extra, JSON_UNESCAPED_UNICODE); // 숫자가 있을 경우: json_encode($extra, SON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

  $insert = new stdClass();
	$insert['pidx'] = $idx;
	$insert['midx'] = $midx;
	$insert['cellphone'] = $cellphone;
	$insert['email'] = $email;
	$insert['is_delete'] = 'FALSE';
	$insert['extras'] = $extra_json;
	$insert['reg_date'] = time();

	$this->db()->insert($this->table->application,$insert)->execute();
	$results->success = true;

} else {
	$results->success = false;
	$results->errors = $errors;
}

?>