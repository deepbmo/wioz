<?php
if (defined('__IM__') == false) exit;
$IM->addHeadResource('style',$Templet->getDir().'/styles/tel.css');

$mCoursemos = $IM->getModule('coursemos');

$keyword = Request('keyword');
$lists = array();
$page = 1;


if ($keyword) {
    // 프로시저 검색
    // 검색키워드|@|페이지사이즈|@|선택페이지
    // 페이지사이즈: 전체 몇 페이지, 선택페이지: 그 중 몇 번째 페이지를 보여줄 것인지
	$c_qry = "
	EXEC APP_검색_교직원검색_조회 '%{$keyword}%|@|15|@|{$page}'
	";
	$lists = $mCoursemos->setHaksaData($c_qry);
}
$is_more = count($lists) > 0 ? $lists[0]->page_count != $lists[0]->page_no : false;
?>

<div class="sub_cnts faculty">
	<form method="post"> <!-- post 방식으로 전송 -->
		<div class="searchbox">
			<div class="search_inner" data-role="input">
				<input type="search" name="keyword" value="<?php echo $keyword; ?>" placeholder="교직원명을 입력하세요.">
				<button type="submit" name="search" class="btn_search"><i class="xi xi-magnifier"></i></button>
			</div>
		</div>
		<div class="faculty_list">
			<ul class="list">
				<?php
				if (count($lists) > 0) {
					foreach ($lists as $item) {
						echo '
						<li>
							<div class="box_img" style=""></div>
							<div class="box_text">
								<strong class="title">'.$item->emp_nm.'</strong>
								<ul class="contact_list">
									<li class="item"><a href="#" class="call"><i class="fa fa-phone" aria-hidden="true"></i></a></li>
									<li class="item"><a href="#" class="tel"><i class="fa fa-mobile" aria-hidden="true"></i></a></li>
									<li class="item"><a href="#" class="messenger"><i class="fa fa-comments-o" aria-hidden="true"></i></a></li>
									<li class="item"><a href="#" class="message"><i class="fa fa-envelope-o" aria-hidden="true"></i></a></li>
								</ul>
								<p>
									<span class="info">'.$item->dept_nm.'</span>
									<span class="info">'.$item->dispaly_jk_nm.'</span>
								</p>
							</div>
						</li>';
					}
				} else {
					if ($keyword) {
						echo '
						<li class="empty">
							검색결과가 없습니다.
						</li>';
					}
				}
				?>
			</ul>
			<?php
				if ($is_more) {
					echo '
					<button type="button" data-action="more">더보기</button>
					<input type="hidden" name="page" value="'.$page.'">';
				} 
			?>
			<a href="#" class="top" data-action="top">맨위로 <i class="fa fa-caret-up" aria-hidden="true"></i></a>
		</div>
	</form>
</div>

<script>
	$("button[data-action=more]").on("click",function() {

		var keyword = $("input[name=keyword]").val();
		var page = $("input[name=page]").val();

        var nextPage = parseInt(page) + 1;
        $.ajax({
            url : ENV.getProcessUrl("pusan","getSearchEmployee"),
            type : 'POST',
            data : {keyword:keyword, page:nextPage} ,
            dataType : 'json',
            success : function(result) {
                if (result.success == true) {
                    var $list = $("ul.list");
                    var result_lists = result.lists;

                    if (result_lists.length > 0) {
                        for (var i=0; i < result_lists.length; i++) {
                            var $list_row = '<li>'+result_lists[i].emp_nm+'</li>';
                            var $list_html = '<li>';
                                $list_html += '<div class="box_img" style=""></div>';
                                $list_html += '<div class="box_text">';
                                $list_html += '<strong class="title">'+result_lists[i].emp_nm+'</strong>';
                                $list_html += '<ul class="contact_list">';
                                $list_html += '<li class="item"><a href="#" class="call"><i class="fa fa-phone" aria-hidden="true"></i></a></li>';
                                $list_html += '<li class="item"><a href="#" class="tel"><i class="fa fa-mobile" aria-hidden="true"></i></a></li>';
                                $list_html += '<li class="item"><a href="#" class="messenger"><i class="fa fa-comments-o" aria-hidden="true"></i></a></li>';
                                $list_html += '<li class="item"><a href="#" class="message"><i class="fa fa-envelope-o" aria-hidden="true"></i></a></li>';
                                $list_html += '</ul>';
                                $list_html += '<p>';
                                $list_html += '<span class="info">'+result_lists[i].dept_nm+'</span>';
                                $list_html += '<span class="info">'+result_lists[i].dispaly_jk_nm+'</span>';
                                $list_html += '</p>';
                                $list_html += '</div>';
                                $list_html += '</li>';
                            $list.append($list_html);
                        }

                        $("input[name=page]").val(nextPage);

                        if (result.is_more) {
                            $("button[data-action=more]").show();
                        } else {
                            $("button[data-action=more]").hide();
                        }
                    }
                }
            }
        });
	});

</script>