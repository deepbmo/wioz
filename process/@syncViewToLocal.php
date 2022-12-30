<?php
/**
 * 이 파일은 부산대 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 포트폴리오 모듈의 교과 포트폴리오 (교수) 메뉴 동기화한다.
 *
 * @file /modules/pusan/process/@syncPortfolioCourseProf.php
 * @author OZ
 * @license MIT License
 * @version 3.0.0
 * @modified 2022. 8. 8.
 */

session_write_close();
header('Content-type:text/html; charset=utf-8',true);


$mCoursemos = $this->IM->getModule('coursemos');

$start = $this->IM->getMicroTime();


$year = Request('year');
$semester = Request('semester');
$type = Request('type');


$progressing = true;


if ($type == 'syllabus') {
	$lists = array();
	$c_qry = "
		SELECT * FROM API.dbo.VW_STC_CLASS_SYL 
		WHERE SYEAR = ".$year."
			AND TERM_GCD = 00".$semester."
	";
	$lists = $this->setHaksaData('app',$c_qry);

} elseif ($type == 'cqi') {
	$lists = array();
	$c_qry = "
		SELECT OPEN_YEAR, OPEN_TERM, COURSE_CODE, CLASS_NO, PROF_NO, COMPLETE_VALID
		FROM API.dbo.VW_STC_LECT_CQI_WRITE_FG
		WHERE OPEN_YEAR = ".$year."
			AND OPEN_TERM = 00".$semester."
	";
	$lists = $this->setHaksaData('app',$c_qry);

} elseif ($type == 'mark') {
	$lists = array();
	$c_qry = "
		SELECT OPEN_YEAR, OPEN_TERM, COURSE_CODE, BUNBAN_CODE, PROF_NO
		FROM API.DBO.VW_STC_SJ_GRADE_INSFIN_CONF_FG
		WHERE OPEN_YEAR = ".$year."
			AND OPEN_TERM = 00".$semester."
	";
	$lists = $this->setHaksaData('app',$c_qry);
}


if ($progressing == true) header("Content-Length:".count($lists));

$this->db()->startTransaction();

for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	if ($progressing == true) {
		echo '.';
		if ($i % 50 == 0) ForceFlush();
	}

	$insert = array();
	if ($type == 'syllabus') {
		$c_semester = substr($lists[$i]->TERM_GCD,2);
		$c_course_code = substr_replace($lists[$i]->SUBJ_NO,'',4,2);
		$c_haksa = $lists[$i]->PROF_NO;
		$c_bunban = $lists[$i]->CLASS_NO;

		$insert['year'] = $lists[$i]->SYEAR;
		$insert['semester'] = $c_semester;
		$insert['course_code'] = $c_course_code;
		$insert['bunban'] = $lists[$i]->CLASS_NO;
		$insert['haksa'] = $lists[$i]->PROF_NO;
		$insert['syllabus'] = $lists[$i]->INPUT_FG == 'Y' ? 'TRUE' : 'FALSE';

	} elseif ($type == 'cqi') {
		$c_semester = substr($lists[$i]->OPEN_TERM,2);
		$c_course_code = substr_replace($lists[$i]->COURSE_CODE,'',4,2);
		$c_haksa = $lists[$i]->PROF_NO;
		$c_bunban = $lists[$i]->CLASS_NO;

		$insert['year'] = $lists[$i]->OPEN_YEAR;
		$insert['semester'] = $c_semester;
		$insert['course_code'] = $c_course_code;
		$insert['bunban'] = $lists[$i]->CLASS_NO;
		$insert['haksa'] = $lists[$i]->PROF_NO;
		$insert['cqi'] = $lists[$i]->COMPLETE_VALID == 'Y' ? 'TRUE' : 'FALSE';

	} elseif ($type == 'mark') {
		$c_semester = substr($lists[$i]->OPEN_TERM,2);
		$c_course_code = substr_replace($lists[$i]->COURSE_CODE,'',4,2);
		$c_haksa = $lists[$i]->PROF_NO;
		$c_bunban = $lists[$i]->BUNBAN_CODE;

		$insert['year'] = $lists[$i]->OPEN_YEAR;
		$insert['semester'] = $c_semester;
		$insert['course_code'] = $c_course_code;
		$insert['bunban'] = $lists[$i]->BUNBAN_CODE;
		$insert['haksa'] = $lists[$i]->PROF_NO;
		$insert['mark'] = 'TRUE';  // 데이터가 있으면 성적처리결과표여부 true
	}


	$b_check = $this->db()->select($this->table->portfolio_course_professor);
	$b_check->where('year',$year);
	$b_check->where('semester',$semester);
	$b_check->where('course_code',$c_course_code);
	$b_check->where('haksa',$c_haksa);
	$b_check->where('bunban',$c_bunban);
	$b_check = $b_check->has();

	if ($b_check) {
		$insert['updated_date'] = time();

		$this->db()->update($this->table->portfolio_course_professor,$insert)->where('year',$year)->where('semester',$semester)->where('course_code',$c_course_code)->where('haksa',$c_haksa)->where('bunban',$c_bunban)->execute();
	} else {
		$insert['reg_date'] = time();

		$this->db()->insert($this->table->portfolio_course_professor,$insert)->execute();
	}
}

$this->db()->commit();
$this->db()->delete($mCoursemos->getTable('sync'))->where('type','pusan_portfolio_course_prof')->execute();

$this->db()->replace($mCoursemos->getTable('sync'),array('year'=>$year,'semester'=>$semester,'type'=>'pusan_portfolio_course_prof_'.$type,'sync'=>count($lists),'latest_date'=>time(),'latest_sync_time'=>round(($this->IM->getMicroTime() - $start) * 1000)))->execute();


if ($progressing == true) ForceFlush();