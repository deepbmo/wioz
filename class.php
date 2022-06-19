<?php

// setUrl 함수 setting
// 1. 상단에 변수 baseUrl 선언

/**
 * 기본 URL (다른 모듈에서 호출되었을 경우에 사용된다.)
 */
private $baseUrl = null;

// 2. getUrl(), setUrl(), getView() 함수 수정
/**
 * URL 을 가져온다.
 *
 * @param string $view
 * @param string $idx
 * @return string $url
 */
function getUrl($view=null,$idx=null) {
  $url = $this->baseUrl ? $this->baseUrl : $this->IM->getUrl(null,null,false);

  $view = $view === null ? $this->getView($this->baseUrl) : $view;
  if ($view == null || $view == false) return $url;
  $url.= '/'.$view;

  $idx = $idx === null ? $this->getIdx($this->baseUrl) : $idx;
  if ($idx == null || $idx == false) return $url;

  return $url.'/'.$idx;
}

/**
 * 다른모듈에서 호출된 경우 baseUrl 을 설정한다.
 *
 * @param string $url
 * @return $this
 */
function setUrl($url) {
  $this->baseUrl = $this->IM->getUrl(null,null,$url,false);
  return $this;
}

/**
 * view 값을 가져온다.
 *
 * @return string $view
 */
function getView() {
  return $this->IM->getView($this->baseUrl);
}





// 관리자 패널에서 script 불러오기
/**
 * [사이트관리자] 모듈 관리자패널 구성한다.
 *
 * @return string $panel 관리자패널 HTML
 */
function getAdminPanel() {
  $this->IM->getModule('admin')->loadModule('sms'); // sms 모듈의 관리자 스크립트를 불러온다.
  $this->IM->getModule('admin')->loadModule('eco');

  /**
   * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
   */
  if ($this->iAdminLevel == 0) $this->isAdmin();

  $IM = $this->IM;
  $Module = $this;

  ob_start();
  INCLUDE $this->getModule()->getPath().'/admin/index.php';
  $panel = ob_get_contents();
  ob_end_clean();

  $this->IM->addHeadResource('script',$this->IM->getModule('coursemos')->getModule()->getDir().'/scripts/ozExtra.js');  // ozExtra.js 불러옴

  return $panel;
}





// @container '팝업'
/**
 * 모듈 외부컨테이너를 가져온다.
 *
 * @param string $container 컨테이너명
 * @return string $html 컨텍스트 HTML
 */
function getContainer($container) {
  switch ($container) {
    case 'mark_popup' :
      $html = $this->getMarkPopupContext($container);
      break;
    
    // 컨테이너에서 컨텍스트 호출
    case 'activity' :
      $midx = $this->getView() ? $this->getView() : null;
      $configs = new stdClass();
      $configs->midx = $midx;

      $html = $this->getContext($container,$configs);
      break;
  }
  
  $this->IM->removeTemplet();
  $footer = $this->IM->getFooter();
  $header = $this->IM->getHeader();
  
  return $header.$html.$footer;
}





// context 함수
/**
 * 회원검색 컨텍스트를 가져온다.
 * 
 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
 * @return string $html
 */
function getMypageContext($configs=null) {
  $mMember = $this->IM->getModule('member');
  if (!mMember->isLogged()) return $this->getError('REQUIRED_LOGIN');

  $role = $this->getView() ? $this->getView() : 'STUDENT';
  $idxes = $this->getIdx() ? explode('/',$this->getIdx()) : array();
  $p = count($idxes) > 0 && is_numeric($idxes[0]) && $idxes[0] > 0 ? $idxes[0] : 1;
  $limit = 50;
  $start = ($p - 1) * $limit;

  $keyword = Request('keyword');

  $columns = 'cm.idx, cm.haksa, cm.name, cm.grade, cm.status as cstatus';
  $columns.= ', i.title as institution';
  $columns.= ', d.title as department';

  $members = $this->db()->select($this->table->member.' cm',$columns);
  $members->join($this->table->institution.' i','cm.iidx=i.idx','LEFT');
  $members->join($this->table->department.' d','cm.didx=d.idx','LEFT');

  $members->where('cm.role',$role);
  if ($keyword) $members->where('(cm.name like ? or cm.haksa = ?)',array('%'.$keyword.'%',$keyword));

  $total = $members->copy()->count();
  $members->orderBy('cm.idx','DESC');
  $members->limit($start,$limit);
  $members = $members->get();

  $pagination = $this->getTemplet()->getPagination($p,ceil($total/$limit),10,$this->getUrl($role,'{PAGE}'));

  $header = PHP_EOL.'<form id="ModuleCoursemosSearchMemberForm">'.PHP_EOL;
  $header.= PHP_EOL.'<input type="hidden" name="role" value="'.$role.'"/>'.PHP_EOL;
  $footer = PHP_EOL.'</form>'.PHP_EOL.'<script>Coursemos.search.member.init("ModuleCoursemosSearchMemberForm");</script>'.PHP_EOL;

  return $this->getTemplet('default')->getContext('search.member',get_defined_vars(),$header,$footer);
}


// view에 따른 템플릿 반환
/**
 * 캘린더 컨텍스트를 가져온다.
 *
 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
 * @return string $html 컨텍스트 HTML
 */
function getCourseContext($configs=null) {
  $view = $this->getView() ? $this->getView() : 'list';

  if ($view == 'list') {
    $header = PHP_EOL.'<div id="ModuleCourseList">'.PHP_EOL;
    $footer = PHP_EOL.'</div>'.PHP_EOL.'<script>Course.init("ModuleCourseList");</script>'.PHP_EOL;

  } elseif ($view == 'write') {
    $header = PHP_EOL.'<form id="ModuleAdvisorPortfolioRequestForm">'.PHP_EOL;
    $footer = PHP_EOL.'</form>'.PHP_EOL.'<script>Advisor.request.init("ModuleAdvisorPortfolioRequestForm");</script>'.PHP_EOL;

  } elseif ($view == 'view') {
    $header = PHP_EOL.'<div id="ModuleCourseView">'.PHP_EOL;
		$footer = PHP_EOL.'</div>'.PHP_EOL.'<script>Course.init("ModuleCourseView");</script>'.PHP_EOL;
  }

  /**
   * 템플릿파일을 호출한다.
   */
  return $this->getTemplet($configs)->getContext('course.'.$view,get_defined_vars(),$header,$footer);
}



// 분기 컨텍스트
/**
 * 프로그램 목록/상세보기 컨텍스트를 가져온다.
 *
 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
 * @return string $html 컨텍스트 HTML
 */
function getProgramContext($configs=null) {
  $mCoursemos = $this->IM->getModule('coursemos');

  $view = $this->getView() ? $this->getView() : 'list';

  if ($view == 'list') {
    $header = PHP_EOL.'<form id="ModuleEcoProgramListForm">'.PHP_EOL;
    if ($category != null) $header.= '<input type="hidden" name="category" value="'.$category.'">'.PHP_EOL;
    $header.= '<input type="hidden" name="essential" value="'.$essential.'">'.PHP_EOL;
    $footer = PHP_EOL.'</form>'.PHP_EOL.'<script>Eco.program.init("ModuleEcoProgramListForm");</script>'.PHP_EOL;

    /**
     * 템플릿파일을 호출한다.
     */
    return $this->getTemplet($configs)->getContext('list',get_defined_vars(),$header,$footer);

  } elseif ($view == 'view') {
    return $this->getViewContext($configs);

  } elseif ($view == 'application') {
    return $this->getApplicationContext($configs);
  }

}





// 컴포넌트
/**
 * 마이페이지 컨텍스트를 가져온다.
 *
 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
 * @return string $html 컨텍스트 HTML
 */
function getMypageContext($configs=null) {
  $idxes = $this->getIdx() ? explode('/',$this->getIdx()) : array();
  $tab = count($idxes) > 0 ? $idxes[0] : 'note';

  switch ($tab) {
    case 'note':
      $context = $this->getNoteFormComponent($project,$idx);
      break;
      
    default:
      $context = $this->getBoardComponent($project->idx,$idx,$tab);
      break;
  }

  return $this->getTemplet($configs)->getContext('mypage', get_defined_vars(), $header, $footer);
}

/**
 * 활동노트 컴포넌트를 가져온다.
 * @param object $project 과제 정보
 * @param object $team 팀 정보
 * @param object $application 개인신청 정보
 * @param string $role 신분
 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
 * @return string $html
 */
function getNoteFormComponent($project,$team,$application,$role,$configs=null) {

  $header = PHP_EOL.'<form id="ModuleCapstoneNoteForm-'.$type.'" data-component="note">'.PHP_EOL;
  $header.= PHP_EOL.'<input type="hidden" name="pidx" value="'.$project->idx.'">'.PHP_EOL;
  $header.= PHP_EOL.'<input type="hidden" name="team" value="'.$team->idx.'">'.PHP_EOL;
  $header.= PHP_EOL.'<input type="hidden" name="type" value="'.$type.'">'.PHP_EOL;
  $header.= PHP_EOL.'<input type="hidden" name="midx" value="'.$midx.'">'.PHP_EOL;
  $footer = PHP_EOL.'</form><script>Capstone.note.init("ModuleCapstoneNoteForm-'.$type.'")</script>'.PHP_EOL;

  /**
   * 템플릿파일을 호출한다.
   */
  return $this->getTemplet($configs)->getContext('note.form',get_defined_vars(),$header,$footer);
}

?>