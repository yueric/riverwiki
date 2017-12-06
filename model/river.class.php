<?php

!defined('IN_HDWIKI') && exit('Access Denied');

class rivermodel
{

    var $db;
    var $base;

    function rivermodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_all_river()
    {
        $riverlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "river ");
        while ($river = $this->db->fetch_array($query)) {
            $riverlist[] = $river;
        }
        return $riverlist;
    }

    function get_river_cache()
    {
        $riverlist = $this->base->cache->getcache('river', $this->base->setting['index_cache_time']);
        if (!is_array($riverlist)) {
            $riverlist = $_ENV['river']->get_all_river();
            $this->base->cache->writecache('river', $riverlist);
        }
        //var_dump ($riverlist);
        return $riverlist;
    }


    function get_river_detail(& $rid)
    {
        $riverline = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "riverlink WHERE rid=" . $rid);
        while ($river = $this->db->fetch_array($query)) {
            $riverline[] = $river;
        }
        return $riverline;
    }

    function get_river(& $rid)
    {
        $sql = "SELECT rname FROM " . DB_TABLEPRE . "river WHERE rid=" . $rid;
        return $this->db->result_first($sql);
    }

    /**
     * 获取所有流域
     */
    function get_rivera(){
        $query1 = $this->db->query("select cid from wiki_category where pid=94 ");
        $rivers = array();
        while ($cid = $this->db->fetch_array($query1)) {
           // var_dump($cid['cid']);
           $query = $this->db->query("select a.cid as cid,d.rname as rname,d.longitude as lon,d.latitude as lat,e.line as line from wiki_category a,wiki_categorylink b,wiki_doc c,wiki_river d,wiki_riverlink e where a.cid=b.cid and b.did=c.did and c.isriver=1 and c.did=d.did and d.rid=e. rid and a.pid=" . $cid['cid']);
           while ($river = $this->db->fetch_array($query)) {
               $rivers[] = $river;
           }
           $rivers[] = $this->get_recu_rivers($rivers);
        }
       // var_dump(sizeof($rivers));
        return $rivers;
    }

    function get_rivers(& $did)
    {
        $rivers = array();
        $query = $this->db->query("select * from wiki_riverlink ");
        while ($river = $this->db->fetch_array($query)) {
            $rivers[] = $river;
        }
        //var_dump ($rivers);
        return $rivers;
    }

    function get_rrivers(& $did){

        $rivers = array();
        $catas= array();
        //先查询所有的非叶子节点
       // $query1 = $this->db->query("select * from wiki_category where pid=".$did);
      //  while($cata = $this->db->fetch_array($query1)){
       //     $catas[] = $cata['cid'];
       // }
        //var_dump($catas);
        //查询叶节点
        $query = $this->db->query("select a.cid as cid,d.rname as rname,d.longitude as lon,d.latitude as lat,e.line as line from wiki_category a,wiki_categorylink b,wiki_doc c,wiki_river d,wiki_riverlink e where a.cid=b.cid and b.did=c.did and c.isriver=1 and c.did=d.did and d.rid=e. rid and a.pid=" . $did);
        while ($river = $this->db->fetch_array($query)) {
            $rivers[] = $river;
           // $catas[] = $river['cid'];
        }
        //var_dump($catas);
        //递归
        $rivers = $this->get_recu_rivers($rivers);
        return $rivers;
    }

    function get_recu_rivers(& $rivers)
    {
        $dids =  array();
        foreach($rivers as $river){
            $dids[] = $river['cid'];
        }
        $ndids = array_unique($dids);
        //var_dump($ndids);
        $rts = array();

        $recurivers = array();
        foreach($ndids as $did){

            $query = $this->db->query("select a.cid as cid,d.rname as rname,d.longitude as lon,d.latitude as lat,e.line as line from wiki_category a,wiki_categorylink b,wiki_doc c,wiki_river d,wiki_riverlink e where a.cid=b.cid and b.did=c.did and c.isriver=1 and c.did=d.did and d.rid=e. rid and a.pid=" . $did);
            while ($river = $this->db->fetch_array($query)) {
                $recurivers[] = $river;
            }
        }
        $rivers[] = $recurivers;
        if(sizeof($recurivers)>0){
            $rts[] = this.$this->get_recu_rivers($recurivers);
            $rivers[] = $rts;
        }
        return $rivers;
    }
}
?>