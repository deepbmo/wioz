<?php

$mCoursemos = $this->IM->getModule('coursemos');
$mMember = $this->IM->getModule('member');


$iidx = Request('iidx');
$didx = Request('didx');
$keyword = Request('keyword');
$type = Request('type');
$category = Request('category');
$is_attachment = Request('is_attachment') !== 'FALSE';


$attachments = array();
$title = '융합학습활동';
$fileName = '융합학습활동';


$lists = $this->db()->select($this->table->board_post.' p','p.*, i.title as institution');
$lists->join($mCoursemos->getTable('member').' cm','cm.idx = p.midx','LEFT');
$lists->join($mCoursemos->getTable('institution').' i','i.idx = cm.iidx','LEFT');
$lists->where('bid','CONVERGENCE');
if ($category && $category != 'ALL') $lists->where('p.category',strtolower($category));
if ($type) $lists->where('i.type',$type);
if ($iidx && $iidx != '*') $lists->where('cm.iidx',$iidx);
if ($didx && $didx != '*') $lists->where('cm.didx',$didx);
if ($keyword) $lists->where('(cm.haksa = ? or cm.name like ?)',array($keyword,'%'.$keyword.'%'));
$lists->orderBy('p.reg_date','DESC');
$lists = $lists->get();


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
$mPHPExcelReader = new PHPExcelReader($this->getModule()->getPath().'/documents/style.xlsx');
$mPHPExcel = $mPHPExcelReader->GetExcel();

$mPHPExcel->setActiveSheetIndex(0);


// 컬럼
$columns = array('loopnum','category','title','term','activity','institution','department','name','haksa','status','grade','cellphone','email','reg_date');

// 컬럼 길이, 헤더 그리기
$columnLengths = array();
for ($i=0; $i<count($columns); $i++) {
	$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);

	if ($i > 0) {
		$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A1'),$column.'1');
		$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A2'),$column.'2');
	}

    if ($columns[$i] == 'loopnum') $column_title = '순번';
    elseif ($columns[$i] == 'category') $column_title = '카테고리';
    elseif ($columns[$i] == 'title') $column_title = '제목';
    elseif ($columns[$i] == 'term') $column_title = '활동기간';
    elseif ($columns[$i] == 'activity') $column_title = '활동자료';
    elseif ($columns[$i] == 'institution') $column_title = '단과대학';
    elseif ($columns[$i] == 'department') $column_title = '학과';
    elseif ($columns[$i] == 'name') $column_title = '이름';
    elseif ($columns[$i] == 'haksa') $column_title = '학번';
    elseif ($columns[$i] == 'status') $column_title = '재적';
    elseif ($columns[$i] == 'grade') $column_title = '학년';
    elseif ($columns[$i] == 'cellphone') $column_title = '핸드폰';
    elseif ($columns[$i] == 'email') $column_title = '이메일';
    elseif ($columns[$i] == 'reg_date') $column_title = '등록일자';

    $mPHPExcel->getActiveSheet()->setCellValue($column.'1',$mCoursemos->ozExtra->AnyToString($column_title));

    $columnLengths[$i] = strlen($column_title);

    // 정렬
    if (in_array($columns[$i],array('loopnum','category','term','institution','department','name','haksa','status','grade','cellphone','email','reg_date'))) {
        $mPHPExcel->getActiveSheet()->getStyle($column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    } else {
        $mPHPExcel->getActiveSheet()->getStyle($column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    }
}


// 데이터 그리기
$loopnum = 1;
foreach($lists as $list) {
    $mCoursemos->ozExtra->checkCountFlush('ADD');

    $coursemos_member = $mCoursemos->getCoursemosMember($list->midx);
    $member = $mMember->getMember($list->midx);

    // 활동기간
    $json_info = json_decode($list->info);
    $tmp_start_date = isset($json_info->start_date) && $json_info->start_date ? date('Y-m-d',$json_info->start_date) : '';
    $tmp_end_date = isset($json_info->end_date) && $json_info->end_date ? date('Y-m-d',$json_info->end_date) : '';
    $list->activity_term = $tmp_start_date && $tmp_end_date ? $tmp_start_date.' ~ '.$tmp_end_date : '';


    // 활동자료
    $board_files = $this->db()->select($this->table->attachment)->where('parent',$list->idx)->where('type','POST')->get();
    $files = array();
    if ($board_files) {
        foreach ($board_files as $file) {
            $file_info = $this->IM->getModule('attachment')->getFileInfo($file->idx,true);
            if ($file_info == null) continue;
            $path = $list->title.'/'.$file_info->name;
            if ($is_attachment === true) $attachments[$path] = $file_info->path;
        }
    }
    $list->board_files = $files;


    for ($i=0; $i<count($columns); $i++) {
        $column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);

        if ($loopnum > 1) {
            $mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle($column.'2'),$column.($loopnum+1));
        }

        if ($columns[$i] == 'reg_date') {
            $mPHPExcel->getActiveSheet()->setCellValue($column.($loopnum+1),PHPExcel_Shared_Date::PHPToExcel(new DateTime(date('Y-m-d',$list->reg_date))));
            $mPHPExcel->getActiveSheet()->getStyle($column.($loopnum+1))->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            $columnLengths[$i] = 16;

        } else {
            switch ($columns[$i]) {
                case 'loopnum' :
                    $value = $loopnum;
                    break;

                case 'category' :
                    $value = $this->getText('convergence/'.$list->category);
                    break;

                case 'title' :
                    $value = $list->title;
                    break;

                case 'term' :
                    $value = $list->activity_term;
                    break;

                case 'activity' :
                    $files = $list->board_files;
                    $ry_file = array();

                    if (count($files) > 0) {
                        foreach ($files as $file) {
                            $ry_file[] = $file->name;
                        }
                    }

                    if (count($ry_file) > 0) {
                        $value = implode(',',$ry_file);
                    } else {
                        $value = '';
                    }
                    break;
                    
                case 'institution' :
                    $value = $coursemos_member ? $mCoursemos->getInstitution($coursemos_member->iidx) : '';
                    break;

                case 'department' :
                    $value = $coursemos_member ? $mCoursemos->getDepartment($coursemos_member->didx) : '';
                    break;
                
                case 'name' :
                    $value = $coursemos_member ? $coursemos_member->name : '';
                    break;
                
                case 'haksa' :
                    $value = $coursemos_member ? $coursemos_member->haksa : '';
                    break;
                
                case 'status' :
                    $value = $coursemos_member && $coursemos_member->status ? $mCoursemos->getText('status/'.$coursemos_member->status) : '';
                    break;

                case 'grade' :
                    $value = $coursemos_member && $coursemos_member->grade ? ($coursemos_member->role == 'STUDENT' && $coursemos_member->grade > 0 ? $coursemos_member->grade.'학년' :'') : '';
                    break;

                case 'cellphone' :
                    $value = $member->cellphone ? $member->cellphone : '';
                    break;

                case 'email' :					
                    $value = $member->email ? $member->email : '';
                    break;
                
                default:
                    $value = null;
                    break;
            }

            $mPHPExcel->getActiveSheet()->getStyle('H'.$loopnum)->getNumberFormat()->setFormatCode('0');
            $mPHPExcel->getActiveSheet()->getStyle('I'.$loopnum)->getNumberFormat()->setFormatCode('#,##0');  // 숫자 형식

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

$mPHPExcel->setActiveSheetIndex(0);


// 하단에 다운받은 시간과 이름 출력
$mPHPExcel->getActiveSheet()->mergeCells('A'.($loopnum+2).':'.$column.($loopnum+2));
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getFont()->setSize(9);
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getFont()->setColor(new PHPExcel_Style_Color('FF666666'));
$mPHPExcel->getActiveSheet()->setCellValue('A'.($loopnum+2),date('Y년 m월 d일 H시 m분').' / '.$mMember->getMember()->name);