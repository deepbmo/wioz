<?php
if (defined('__IM__') == false) exit;
$IM->addHeadResource('style',$Templet->getDir().'/styles/tel.css');

$mCoursemos = $IM->getModule('coursemos');

$search_dept = Request('dept');
$lists = array();


if ($search_dept) {
    // mssql 프로시저 검색
	$c_qry = "
	EXEC APP_검색_기관검색_조회 '{$search_dept}'
	";
	$lists = $mCoursemos->setHaksaData($c_qry);
}
?>

<div class="sub_cnts institution">
	<form class="searchbox" method="post"> <!-- post 방식으로 전송함 -->
		<div class="search_inner" data-role="input">
			<input type="search" name="dept" placeholder="기관명을 입력하세요 (초성검색 가능, ㅈ ㅂ ㅎ)">
			<button type="button" name="search" class="btn_search" data-action="search"><i class="xi xi-magnifier"></i></button>
		</div>
	</form>
	<div class="institution_list">
		<ul data-role="table" class="inner">
			<?php
			if (count($lists) > 0) {
				foreach($lists as $list) {
					$tel = $list->dept_tel_no ? '전화' : '';
					echo '
					<li class="tbody">
						<span class="title">'.$list->dept_nm.'</span>
						<span class="tel">'.$tel.'</span>
					</li>'; 
				}
			} else {
				if ($search_dept) {
					echo '
					<li class="empty">
						검색결과가 없습니다.
					</li>';
				}
			}
			?>
		</ul>
		<a href="#" class="top" data-action="top">맨위로 <i class="fa fa-caret-up" aria-hidden="true"></i></a>
	</div>
</div>