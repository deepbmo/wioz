<?php
/**
 * 이 파일은 부산대모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 핵심역량 모듈별 핵심역량 차트
 *
 * @file /modules/pusan/widgets/essential_chart/index.php
 * @version 3.0.0
 * @modified 2022. 6. 29.
 */
if (defined('__IM__') == false) exit;

$IM->loadWebFont('FontAwesome');
$IM->loadWebFont('XEIcon');


$mMember = $this->IM->getModule('member');
$mCoursemos = $this->IM->getModule('coursemos');
$mEssential = $this->IM->getModule('essential');
$mCourse = $this->IM->getModule('course');


$type = $Widget->getValue('type');  // essential: 핵심역량, major: 전공역량


$midx = $mMember->getLogged();
$member = $mMember->getMember($midx);
$role = $member->type == 'ADMINISTRATOR' ? 'STUDENT' : ($member->coursemos ? $member->coursemos->role : 'STUDENT'); // 최고관리자일 경우 role 값을 STUDENT로 지정
$i_dept = isset($member->coursemos) && $member->coursemos->department ? $member->coursemos->department->idx : 0; // 로그인한 회원 학과 고유값
$grade = isset($member->coursemos) ? $member->coursemos->grade : 0;  // 로그인한 회원 학년



if ($type == 'essential') {
	$essentials = $mEssential->getEssentials($role); // 부산대 핵심역량


	$ary_points = array();
	for ($i=0; $i < count($essentials); $i++) {
		$ary_categories[$essentials[$i]->idx] = $essentials[$i]->title; // [핵심역량 고유값] => 핵심역량 이름
		
		$c_columns = 'round(avg(point),2) as avg, if(sum(point),round(sum(point),2),0) as point';
		$o_point = $mEssential->db()->select($mEssential->getTable('point_history').' ph',$c_columns);
		$o_point->join($mCoursemos->getTable('member').' cm','ph.midx=cm.idx','LEFT');
		$o_point->where('ph.essential',$essentials[$i]->idx);
		$o_dept = $o_point->copy()->where('cm.didx',$i_dept)->getOne();
		$o_grade = $o_point->copy()->where('cm.grade',$grade)->getOne();
		$o_grade_dept = $o_point->copy()->where('cm.didx',$i_dept)->where('cm.grade',$grade)->getOne();
		$o_point = $o_point->where('ph.midx',$midx)->getOne();

		$tmp_points = new stdClass();
		$tmp_points->mypoint = $o_point->point;
		$tmp_points->dept = $o_dept->avg;
		$tmp_points->grade = $o_grade->avg;
		$tmp_points->grade_dept = $o_grade_dept->avg;
		
		$ary_points[$essentials[$i]->idx] = $tmp_points;
	}

	$ary_mypoint = $ary_dept = $ary_grade = $ary_grade_dept = array();
	foreach ($ary_points as $point) {
		$ary_mypoint[] = $point->mypoint;
		$ary_dept[] = $point->dept;
		$ary_grade[] = $point->grade;
		$ary_grade_dept[] = $point->grade_dept;
	}

	$c_categories = implode(',',$ary_categories);
	$c_mypoint = implode(",",$ary_mypoint);
	$c_dept = implode(",",$ary_dept);
	$c_grade = implode(",",$ary_grade);
	$c_grade_dept = implode(",",$ary_grade_dept);


	$chart = '<div data-role="chart" data-type="essential" data-categories="'.$c_categories.'" data-mypoint="'.$c_mypoint.'" data-dept="'.$c_dept.'" data-grade="'.$c_grade.'" data-grade_dept="'.$c_grade_dept.'"></div>';

} elseif ($type == 'major') {
	$competencies = $mCourse->db()->select($mCourse->getTable('competency'))->where('didx',$i_dept)->get(); // 부산대 교과전공역량


	$ary_points = array();
	for ($i=0; $i < count($competencies); $i++) {
		$ary_categories[$competencies[$i]->idx] = $competencies[$i]->title; // [전공역량 고유값] => 전공역량 이름
		
		$c_columns = 'round(avg(point),2) as avg, if(sum(point),round(sum(point),2),0) as point';
		$o_point = $mCourse->db()->select($mCourse->getTable('competency_history').' ch',$c_columns);
		$o_point->join($mCoursemos->getTable('member').' cm','ch.midx=cm.idx','LEFT');
		$o_point->where('ch.competency',$competencies[$i]->idx);
		$o_dept = $o_point->copy()->where('cm.didx',$i_dept)->getOne();
		$o_grade = $o_point->copy()->where('cm.grade',$grade)->getOne();
		$o_grade_dept = $o_point->copy()->where('cm.didx',$i_dept)->where('cm.grade',$grade)->getOne();
		$o_point = $o_point->where('ch.midx',$midx)->getOne();

		$tmp_points = new stdClass();
		$tmp_points->mypoint = $o_point->point;
		$tmp_points->dept = $o_dept->avg;
		$tmp_points->grade = $o_grade->avg;
		$tmp_points->grade_dept = $o_grade_dept->avg;
		
		$ary_points[$competencies[$i]->idx] = $tmp_points;
	}

	$ary_mypoint = $ary_dept = $ary_grade = $ary_grade_dept = array();
	foreach ($ary_points as $point) {
		$ary_mypoint[] = $point->mypoint;
		$ary_dept[] = $point->dept;
		$ary_grade[] = $point->grade;
		$ary_grade_dept[] = $point->grade_dept;
	}

	$c_categories = implode(',',$ary_categories);
	$c_mypoint = implode(",",$ary_mypoint);
	$c_dept = implode(",",$ary_dept);
	$c_grade = implode(",",$ary_grade);
	$c_grade_dept = implode(",",$ary_grade_dept);


	$chart = '<div data-role="chart" data-type="major" data-categories="'.$c_categories.'" data-mypoint="'.$c_mypoint.'" data-dept="'.$c_dept.'" data-grade="'.$c_grade.'" data-grade_dept="'.$c_grade_dept.'"></div>';

}



return $Widget->getTemplet()->getContext('index',get_defined_vars());