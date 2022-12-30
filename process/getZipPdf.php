<?php
$parent = Request('parent');
if (!$parent) return false;

$datas = $this->db()->select($this->table->application);
$datas->where('parent',$parent);
$datas->where('certificate_file',0,'>');
$datas->where('status','COMPLETE');
$datas = $datas->get('aidx');

$attachments = array();

foreach ($datas as $count => $data) {

    $pdf = new PDF('P','mm','A4',true,'UTF-8', false);
    $pdf->SetCreator($this->getModule()->getConfig('school_name'));
    $pdf->SetTitle("교육실시확인서");
    $pdf->SetMargins(15, 5);
    $pdf->SetDefaultMonospacedFont($default_font); //기본폰트
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);


    $application = $this->getApplication($data);
    $program = $this->getProgram($application->pidx);
    $category1 = $this->getCategory($program->category1);
    $category2 = $this->getCategory($program->category2);
    $c_category1 = isset($category1->title) && $category1->title ? $category1->title : '';
    $c_category2 = isset($category2->title) && $category2->title ? $category2->title : '';
    $member_metadata = $application->member_metadata;
    $start_date = date('Y-m-d',$program->start_date);
    $end_date = date('Y-m-d',$program->end_date);

    $o_member = $this->IM->getModule('member')->getMember($application->midx);

    $pdf->SetFont($default_font,'', 8);
    $pdf->AddPage();

    $html = '
        <style>
            h2.title {font-size:25px; text-align:center;}
            h3 {font-size:18px; text-align:center;}
            h4 {font-size:18px; text-align:center; line-height:24px;}
            h5 {font-size:16px;}
            p.date {font-size:16px; text-align:center;}
            p.mark {font-size:22px; text-align:center;}
            p.certificate_no {font-size:11px;}

            span {font-size:11px;}

            table, th, td {border:1px soild #000;}
            div.tb > table {width:100%; table-layout:fixed; font-size:12px; line-height:22px;}
            div.tb > table > thead > tr:first-child > th {border-top:0 none;}
            div.tb > table th {background-color:#f4f4f4; text-align:center; vertical-align:middle; padding:10px; color:#000; word-break:keep-all;}
            div.tb > table td {text-align:center; vertical-align:middle; padding:10px 20px; color:#333; font-size:11px; word-break:keep-all;}
        </style>
    ';
    $html .= '
        <div class="view">
            <p class="certificate_no">'.$application->certificate_no.'</p>
            <h2 class="title">교 육 실 시 확 인 서</h4>
            <h5>1. 소속사업장 개요</h5>
            <span>○ 사업자명:</span>
            <span>'.$application->business_name.'</span>
            <br />
            <br />
            <span>○ 대표자:</span>
            <span>'.$application->business_header.'</span>
            <br />
            <br />
            <br />
            <div class="program">
                <h5>2. 교육의 개요</h5>
                <div class="tb">
                    <table>
                        <tbody>
                            <tr>
                                <th style="width:140px">교육의 종류</th>
                                <th>교육일자</th>
                                <th style="width:60px">교육시간</th>
                                <th>교육내용</th>
                                <th>교육이수자(명)</th>
                            </tr>
                            <tr>
                                <td>
                                    '.$c_category1.'
                                    <br />
                                    - '.$c_category2.'
                                </td>
                                <td style="vertical-align:middle;">
                                    '.$start_date.' ~ '.$end_date.'
                                </td>
                                <td>
                                    '.$program->day.'일 '.$program->time.'시간
                                </td>
                                <td>
                                    '.$program->c_title.'
                                </td>
                                <td>
                                    '.($o_member ? $o_member->name : '').'
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <br />
            <br />


        </div>';

    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $content = '「안전보건교육규정」제11조에 따라 교육이수를 위와 같이 확인합니다.';
    $pdf->SetFontSize(18);
    $pdf->MultiCell(180, 10, '&nbsp;'.$content."\n", 0, 'J', 0, 0, '', '140',true,0,true,true,0);
    $pdf->SetFontSize(20);
    $c_date = date('Y년 m월 d일', time());
    if (isset($category1->edu_type) && $category1->edu_type == 'GROUP') {
        $c_date = date('Y년 m월 d일',$program->end_date);
    }

    $pdf->writeHTMLCell(0, 0, 10, 220, $c_date, 0, 1, 0, true, "C", true);
    $pdf->Image(__IM_PATH__.'/modules/bizedu/templets/default/images/img_popup02.png', 135, 245, 25, 25, 'PNG');
    $pdf->SetFontSize(25);
    $pdf->writeHTMLCell(0, 0, 10, 250, "대 한 산 업 보 건 협 회 장", 0, 1, 0, true, "C", true);
    $pdf->SetAutoPageBreak(TRUE, 10); //자동 페이지구분 하기

    $document = $this->IM->getModule('attachment')->getTempFile(true);
    $pdf->Output($document,'F');

    $attachments[] = array('title'=>'교육실시확인서_'.($o_member ? $o_member->name : $count),'document'=>$document);
}

if (count($attachments) > 0) {
    $zip = $this->IM->getModule('attachment')->getTempFile(true);
    $mZip = new ZipArchive();
    $mZip->open($zip,ZipArchive::CREATE);
    foreach ($attachments as $key => $attach) {
        $mZip->addFile($attach['document'],$attach['title'].'.pdf');
    }
    $mZip->close();

    foreach ($attachments as $attach) {
        unlink($attach['document']);
    }
    $mime = 'application/zip';
    $file = basename($zip);

} else {
    $document = $this->IM->getModule('attachment')->getTempFile(true);
    $pdf->Output($document,'F');

    $mime = 'application/pdf';
    $file = basename($document);
}

$results->success = true;
$results->file_name = $program->c_title.'_교육실시확인서';
$results->mime = $mime;
$results->file = $file;
