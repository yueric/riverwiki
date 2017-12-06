<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class control extends base{

	function control(& $get,& $post){
		$this->base(  $get, $post);
		$this->view->setlang($this->setting['lang_name'],'back');
		$this->load("synonym");
		$this->load("doc");
		$this->load("user");
		$this->load("category");
	}

	function dodefault(){
		$this->dosearch();
	}

	function dosearch(){
		$cid=isset($this->post['qcattype'])?$this->post['qcattype']:$this->get[2];
		$title=isset($this->post['qtitle'])?trim($this->post['qtitle']):urldecode(trim($this->get[3]));
		$stop=0;
		$author=isset($this->post['qauthor'])?trim($this->post['qauthor']):trim($this->get[4]);

		if($stop===0){
			if($author){
				$authorid=$this->db->fetch_by_field('user','username',$author);
				if(!(bool)$authorid){
					$message.=$this->view->lang['attachAuthorNone'];
					$stop=1;
				}
			}
			$starttime=isset($this->post['qstarttime'])?strtotime($this->post['qstarttime']):(int)$this->get[5];
			$endtime=isset($this->post['qendtime'])&&$this->post['qendtime']?(strtotime($this->post['qendtime'])+24*3600):(int)$this->get[6];
			$page = max(1, intval(end($this->get)));
			$num = isset($this->setting['list_prepage'])?$this->setting['list_prepage']:20;
			$start_limit = ($page - 1) * $num;
			$count = $_ENV['synonym']->search_synonym_num($cid,$title,$author,$starttime,$endtime);
			$synonymlist=$_ENV['synonym']->search_synonym($start_limit,$num,$cid,$title,$author,$starttime,$endtime);
			$departstr=$this->multi($count, $num, $page,"admin_synonym-search-".urlencode("$cid-$title-$author-$starttime-$endtime"));
		}
		$all_category=$this->cache->getcache('category',$this->setting['index_cache_time']);
		$this->load("category");
		if(!(bool)$all_category){
			$all_category = $_ENV['category']->get_all_category();
			$this->cache->writecache('category',$all_category);
		}
		$catstr = $_ENV['category']->get_categrory_tree($all_category);
		if($stop===1){
			$this->view->assign("message",$message);
			$count=0;
		}
		$titles=stripslashes($title);
		$authors=stripslashes($author);
		$this->view->assign("catstr",$catstr);
		$this->view->assign("synonymsum",$count);
		$this->view->assign("qtitle",$titles);
		$this->view->assign("qauthor",$authors);
		$this->view->assign("qstarttime",$starttime?date("Y-m-d",$starttime):"");
		$this->view->assign("qendtime",$endtime?date("Y-m-d",$endtime-24*3600):"");
		$this->view->assign("departstr",$departstr);
		$this->view->assign("synonymlist",$synonymlist);
		$this->view->display('admin_synonym');
	}

	function dodelete(){
		@$ids=$this->post['id'];
		if(is_array($ids)){
			if($_ENV['synonym']->removesynonym($ids)){
				$this->message($this->view->lang['synonymSucess'],'index.php?admin_synonym');
			}else{
				$this->message($this->view->lang['synonymFaile'],'index.php?admin_synonym');
			}
		}else{
			$this->message($this->view->lang['docRemoveDocNull']);
		}
	}
	function dosave(){
		$synonym=str_replace(array("\n","\r"), ' ', string::hiconv(trim($this->post['synonym']), '', '', true));
		if(empty($synonym)){
			$this->message($_ENV["synonym"]->encode_data(array(-1,'')),'',2);
		}
		if(count(explode(',',$synonym))>10){
			$this->message($_ENV["synonym"]->encode_data(array(-8,'')),'',2);
		}
		$srctitles=array_unique(explode(',',$synonym));
		$returnsyn=stripslashes(implode(',',$srctitles));
		$i=false;
		if(isset($this->post['destdid'])){
			$destdid=$this->post['destdid'];
			if(!is_numeric($destdid)){
				$this->message($_ENV["synonym"]->encode_data(array(-1,'')),'',2);
			}
			$doc=$this->db->fetch_by_field('doc','did',$destdid);
			$desttitle=addslashes($doc['title']);
			$i=true;
		}elseif(isset($this->post['desttitle'])){
			$desttitle=string::hiconv(trim($this->post['desttitle']), '', '', true);
			if($doc=$this->db->fetch_by_field('doc','title',$desttitle))
				$destdid=$doc['did'];
			else
				$this->message($_ENV["synonym"]->encode_data(array(-7,$desttitle)),'',2);
		}else{
			$this->message($_ENV["synonym"]->encode_data(array(-1,'')),'',2);
		}
		$filter=$_ENV["synonym"]->is_filter($srctitles,$desttitle,!$i);
		if($filter[0]<0){
			$this->message($_ENV["synonym"]->encode_data($filter),'',2);
		}
		if($_ENV["synonym"]->savesynonym($destdid,$desttitle,$srctitles)){
			if($i)
				$this->message($_ENV["synonym"]->encode_data(array(1,$returnsyn)),'',2);
			$this->message($_ENV["synonym"]->encode_data(array(1,$desttitle,$doc['author'],$this->date($doc['time']),$destdid,$returnsyn)),'',2);
		}else{
			$this->message($_ENV["synonym"]->encode_data(array(-1,'')),'',2);
		}
	}

}
?>