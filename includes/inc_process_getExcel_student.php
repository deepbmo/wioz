<?php

$pidx = Param('pidx');

$program = $this->getProgram($pidx);

if (!$program) {
    $results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}


$title = '학생별 조회';

$iidx = Request('iidx');

$lists = $this->db()->select($this->table->program);
if ($iidx) $lists->where('iidx',$iidx);
$lists = $lists->get();


// list 없으면 종료
if (count($lists) == 0) {
	header("X-Excel-File:");
	exit;
}


$fileName = md5(time());
$i_tmp_cnt = count($lists) + 5;
$mCoursemos->ozExtra->checkCountFlush('TOTAL', $i_tmp_cnt);
header("Content-Length:".$i_tmp_cnt);
header("X-Excel-File:".$fileName);

$mPHPExcel = new PHPExcel();
$mPHPExcelReader = new PHPExcelReader($this->getModule()->getPath().'/documents/form.xlsx');
$mPHPExcel = $mPHPExcelReader->GetExcel();

$mPHPExcel->setActiveSheetIndex(0);


// 컬럼
$columns = array('loopnum','institution','department','name','haksa','gender','grade','role','member_status');

// 컬럼 길이, 헤더 그리기
$columnLengths = array();
for ($i=0; $i<count($columns); $i++) {
	$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);

	if ($i > 0) {
		$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A1'),$column.'1');
		$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A2'),$column.'2');
	}

    $mPHPExcel->getActiveSheet()->setCellValue($column.'1',$mCoursemos->ozExtra->AnyToString($this->getText('excel/column/'.$columns[$i]))); // 특수기호 제거

    $columnLengths[$i] = strlen($this->getText('excel/column/'.$columns[$i]));

    // 정렬
    if (in_array($columns[$i],array('loopnum','institution','department','name','haksa','gender','grade','role','member_status'))) {
        $mPHPExcel->getActiveSheet()->getStyle($column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    } else {
        $mPHPExcel->getActiveSheet()->getStyle($column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    }
}


// 데이터 그리기
$loopnum = 1;
foreach($lists as $list) {
    $mCoursemos->ozExtra->checkCountFlush('ADD');

    // list 가공
    $list->role = '';


    for ($i=0; $i<count($columns); $i++) {
        $column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);

        if ($columns[$i] == 'reg_date') {
            $mPHPExcel->getActiveSheet()->setCellValue($column.($loopnum+1),PHPExcel_Shared_Date::PHPToExcel(new DateTime(date('Y-m-d H:i:s',round($list->reg_date / 1000)))));
            $mPHPExcel->getActiveSheet()->getStyle($column.($loopnum+1))->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
            $columnLengths[$i] = 18;

        } else {
            switch ($columns[$i]) {
                case 'loopnum' :
                    $value = $loopnum;
                    break;
                    
                case 'campus' :
                    $value = $mCoursemos->getCampus($list->campus);
                    break;

                case 'institution' :
                    $value = $mCoursemos->getInstitution($list->member_iidx);
                    break;

                case 'department' :
                    $value = $mCoursemos->getDepartment($list->member_didx);
                    break;
                
                case 'major' :
                    $value = $mCoursemos->getDepartment($list->member_jidx);
                    break;
                
                case 'name' :
                    $value = $list->name;
                    break;

                case 'grade' :
                    $value = $list->grade != 'NONE' ? ($list->member_role == 'STUDENT' && $list->grade > 0 ? $list->grade.'학년' :'') : '';
                    break;

                case 'role' :					
                    $member = $mMember->getMember($list->midx);
                    $value = $mCoursemos->getRoleTitle($member->coursemos->role, $member->coursemos->grade);
                    break;
                
                default:
                    $value = null;
                    break;
            }

            $mPHPExcel->getActiveSheet()->setCellValue($column.($loopnum+1),$value);
            $columnLengths[$i] = $columnLengths[$i] < strlen($value) ? strlen($value) : $columnLengths[$i];
        }
    }

    $loopnum++;
}

for ($i=0; $i<count($columnLengths); $i++) {
    $column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
    $length = $columnLengths[$i];
    $length = $length > 35 ? 35 : $length;
    $length = $length < 8 ? 8 : $length;
    $mPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($length * 1.25);
}

$mPHPExcel->getActiveSheet()->setAutoFilter('A1:G1');
$mPHPExcel->getActiveSheet()->freezePane('H2');


// 하단에 다운받은 시간과 이름 출력
$mPHPExcel->getActiveSheet()->mergeCells('A'.($loopnum+2).':'.$column.($loopnum+2));
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getFont()->setSize(9);
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getFont()->setColor(new PHPExcel_Style_Color('FF666666'));
$mPHPExcel->getActiveSheet()->setCellValue('A'.($loopnum+2),date('Y년 m월 d일 H시 m분').' / '.$mMember->getMember()->name);

?>