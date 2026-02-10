<?php

namespace app\Models;

use app\Model;
use app\Models\Pages;

class News extends Model
{
	public const TABLE = 'news';

	public static function check($parameters)
	{
		$f = 1;
		$ids = 0;

		$url = array_pop($parameters);
		$item = News::findWhere('WHERE url="'.$url.'"');
		if(!empty($item) && !empty($parameters)) {
			foreach($parameters AS $item) {
				$obj = Pages::findWhere('WHERE ids IN ('.$ids.') AND url="'.$item.'"');
				if(!empty($obj)) {
					$ids = $obj[0]->id;
				}
				else {
					$f = 0;
					break;
				}
			}
			if($ids != 10) $f = 0;
		}
		else $f = 0;

		return $f;
	}

	public static function getUrl($id)
	{
		$ids = $url = '';

		$res = Pages::findById(10);
		while($res->id <> false) {
			if($ids <> '') $ids .= ',';
			$ids .= $res->id;
			if($res->ids <> 0) $res = Pages::findById($res->ids);
			else $res->id = false;
		}
		if($ids <> '')
		{
			$ids = array_reverse(explode(',',$ids));

			foreach($ids AS $v)
			{
				$res = Pages::findById($v);
				$url .= '/'.$res->url;
			}
		}

		$item = News::findById($id);
		$url .= '/'.$item->url;

		return $url;
	}

	public static function news($i) {

		$newsUrl = Pages::getUrl(10);

		$pnum = 3;

		if($i == 0) {
			$news = News::findWhere('WHERE `show`=1 ORDER BY dates DESC, id DESC');
			if(!empty($news)) $count = count($news);
			else $count = 0;
		}

		$news = News::findWhere('WHERE `show`=1 ORDER BY dates DESC, id DESC LIMIT '.$i.','.$pnum);
		if(!empty($news)) {
			foreach($news AS $item) {
				if(empty($item->image)) {
					$item->image = '/public/src/img/nonews.jpg';
				}
			}
		}

		$module = ROOT.'/public/views/blocks/newsBlock.php';
		if(is_file($module)){ob_start();include $module;$module=ob_get_contents();ob_clean();}else{$module=false;}

		return $module;
	}
}