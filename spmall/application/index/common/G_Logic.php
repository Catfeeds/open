<?php
namespace app\index\common;

use think\DB;

/**
* 	前台商品逻辑层 
*/
class G_Logic
{
	public static function goodsList($id, $data, $info = 0)
	{
		$p_sp = [];
		$p_sp_it = [];
		$p_br = [];
		$p_c_s = [];
		$ht1 = '';

		$in = Db('shop_goods_category')->where('cate_id',$id)->field('parent_id_path')->find();
		$i = array(array());
		if ($data != '' || count($data) == 0) {
			$data = explode('&',$data);
			foreach ($data as $key => $value) {
				$b = explode('=',$value);
	    		$i[$key][$b[0]] = $b[1];
			}
		}
		// var_dump($i);
		// die;
		$p = DB::query('call G_count("cat_id","'.$in['parent_id_path'].'","0","0")');
		foreach ($p as $va) {
			foreach ($va as $key => $value) {
				$p_sp[$key] = $value["speclist_name"]; // 品牌
				$p_sp_it[$key] = $value["speclist_name"]."_M_".$value["item_count"]."_M_".$value["item_id"]; // 品牌
				$p_br[$key] = $value["brand_name"]; // 品牌
			}
		}

		$p_sp = explode("_M_",implode("_M_",array_unique(array_filter($p_sp))));
		$p_br = array_unique(array_filter($p_br));
		for ($i=0; $i < count($p_sp); $i++) { 
			$ip = 0;
			$p_c = array();
			if ($p_sp[$i] == '') {
				break;
			}
			for ($e=0; $e < count($p_sp_it); $e++) {
				if (strstr('_'.$p_sp_it[$e], $p_sp[$i])) {
					$lp = explode("_M_",$p_sp_it[$e]);
					$p_c[$ip] = $lp[1].'_M_'.$lp[2];
					$ip++;
				}
			}
			$p_c_s[$p_sp[$i]] = $p_c;
		}
	
			// 添加品牌
			if (count($p_br)) {
					$ht1 = $ht1.'<li class="select-list">
											<dl id="select1">
												<dt class="am-badge am-round am-bc">品牌</dt>	
												 <div class="dd-conent">';
				foreach ($p_br as $br) {
						$ht1 .= '<dd><a href="#">'.$br.'</a></dd>';
					}
					$ht1 .=' </div></dl></li>';
			}
			foreach ($p_c_s as $key => $value) {
				$ht1.= '<li class="select-list">
									<dl id="select1">
										<dt class="am-badge am-round am-bc">'.$key.'</dt>	
										 <div class="dd-conent">';	
				for ($i=0; $i < count($value); $i++) { 
					$sp = explode("_M_",$value[$i]);
					$ht1 .= '<dd><a class="J_Ajax" data-key="ppath" data-value="'.$sp[1].'">'.$sp[0].'</a></dd>';
				}
				$ht1 .= '</div></dl></li>';
			}
		// 	if (!empty($lk[0][0])) {
		// 		foreach ($lk as $val) {
		// 			$ht1 = $ht1.'<li class="select-list">
		// 									<dl id="select1">
		// 										<dt class="am-badge am-round am-bc">'.$val[0][0]['speclist_name'].'</dt>	
		// 										 <div class="dd-conent">';
		// 			foreach ($val[0] as $va) {
		// 				$ht1 .= '<dd><a href="#">'.$va["item_count"].'</a></dd>';
		// 			}
		// 			$ht1 .=' </div></dl></li>';
		// 		}
		// 			$ht1 = '<ul class="select">'.$ht1.'</ul>';
		// 	}
		// }
		// var_dump($p[0]);
		// die;
		$data["ht1"] = $ht1 = '<ul class="select">'.$ht1.'</ul>';
		$data["d"] = $p[0];
		/*
		 						<li class="select-list">
									<dl id="select1">
										<dt class="am-badge am-round am-bc">品牌</dt>	
										 <div class="dd-conent">										
											<dd><a href="#">百草味</a></dd>
											<dd><a href="#">良品铺子</a></dd>
											<dd><a href="#">新农哥</a></dd>
											<dd><a href="#">楼兰蜜语</a></dd>
											<dd><a href="#">口水娃</a></dd>
											<dd><a href="#">考拉兄弟</a></dd>
										 </div>
						
									</dl>
								</li>
							*/
		return $data;
	}

	public static function navigation($in)
	{
		$q = 0;
		$w = 0;
		$htmlc = '';
		$data['one'] = array();
		$data['two'] = array();
		$in = Db('shop_goods_category')->where('cate_id',$in)->field('parent_id_path')->find();
		$l = explode("_",$in['parent_id_path']);
		$n = substr_count($in['parent_id_path'], "_");
		switch ($n) {
			case '1':
			case '2':
				$o = $l[0].'_'.$l[1];
				$b = Db('shop_goods_category')->where('parent_id_path','like','%'.$o.'%')->field('cate_id,cate_name,parent_id_path')->select();
				for ($i=0; $i < count($b); $i++) { 
					if (substr_count($b[$i]['parent_id_path'], "_") == 2) {
						$data['one'][$q] = $b[$i];
						$q++;
					}
				}
			$op = empty($l[2])?$b[0]['parent_id_path']:$l[0].'_'.$l[1].'_'.$l[2];
				break;
			
			case '3':
			$o = $l[0].'_'.$l[1];
			$b = Db('shop_goods_category')->where('parent_id_path','like','%'.$o.'%')->field('cate_id,cate_name,parent_id_path')->select();
			for ($i=0; $i < count($b); $i++) { 
					if (substr_count($b[$i]['parent_id_path'], "_") == 2) {
						$data['one'][$q] = $b[$i];
						$q++;
					}
					if (substr_count($b[$i]['parent_id_path'], "_") == 3) {
						$data['two'][$w] = $b[$i];
						$w++;
					}
				}
			$op = $l[0].'_'.$l[1].'_'.$l[2];
				break;
			
		}

		//  html  操作
		if (count($data['one']) != 0) {
			$html2 = '';
			$lp =Db('shop_goods_category')->where('parent_id_path',$op)->field('cate_id,cate_name,parent_id_path')->find();
			for ($i=0; $i < count($data['one']); $i++) { 
				$html2 = $html2.'<li><a href="/index/search/index.html/'.$data['one'][$i]['cate_id'].'">'.$data['one'][$i]['cate_name'].'</a></li>';
			}
				$html = '<div class="font-pale disenk">
								<div class="pale-dis">&nbsp;'.$lp['cate_name'].'&nbsp;&nbsp;>&nbsp;&nbsp;</div>
								<div class="hovshz">
									<ul>';
					$html1 = '</ul></div></div>';
					$htmlc = $html.$html2.$html1;
			}
		if (count($data['two']) != 0) {
			$html2 = '';

			$lp =Db('shop_goods_category')->where('parent_id_path',$in['parent_id_path'])->field('cate_id,cate_name,parent_id_path')->find();

			for ($i=0; $i < count($data['two']); $i++) { 
				$html2 = $html2.'<li><a href="/index/search/index.html/'.$data['two'][$i]['cate_id'].'">'.$data['two'][$i]['cate_name'].'</a></li>';
			}
				$html = '<div class="font-pale disenk">
								<div class="pale-dis">&nbsp;'.$lp['cate_name'].'&nbsp;&nbsp;>&nbsp;&nbsp;</div>
								<div class="hovshz">
									<ul>';
					$html1 = '</ul></div></div>';
					$htmlc = $htmlc.$html.$html2.$html1;
			}
			return $htmlc;
	}
}