<?php
/**
 * Created by eric.
 * User: apple
 * Date: 2017/11/24
 * Time: AM11:39
 */

!defined('IN_HDWIKI') && exit('Access Denied');
class control extends base
{

    function control(& $get, & $post)
    {
        $this->base($get, $post);
        $this->load("river");
    }

    function dodefault(){
        $this->doview();
    }

    /**
     * 获取所有河流
     */
    function doview(){
        $allriver=$_ENV['river']->get_river_cache();
        $this->view->assign('allriver',$allriver);
        $this->view->assign('navtitle',"中国水系河流图");
        $this->isMobile() ? $_ENV['block']->view('wap-river') : $_ENV['block']->view('river');
        //$this->view->display("wap-river");
    }

    /**
     * 获取河流的河道信息
     */
    function dodetail(){
        $rid =  $this->get[2];
        $riverline=$_ENV['river']->get_river_detail($rid);
        $this->view->assign('riverline',$riverline);
        $river = $_ENV['river']->get_river($rid);
        $this->view->assign('navtitle',$river."河道轨迹");
        $this->view->assign('rivername',$river);
        //var_dump($river);
        $this->isMobile() ? $_ENV['block']->view('wap-riverline') : $_ENV['block']->view('riverline');
        //$this->view->display("wap-riverline");
    }

    /**
     * 获取流域的河道信息r
     */
    function dorivers(){
        $did =  $this->get[2];
        $rivers=$_ENV['river']->get_rrivers($did);
        $this->view->assign('rivers',$rivers);
        $this->view->assign('navtitle',"河道轨迹");
        //var_dump(sizeof($rivers));
        $this->view->display("rivers");
    }

    function dorivera(){
        $rivers=$_ENV['river']->get_rivera();
        $this->view->assign('rivers',$rivers);
        $this->view->assign('navtitle',"中国水系");
        $this->view->display("rivers");
    }

}
?>