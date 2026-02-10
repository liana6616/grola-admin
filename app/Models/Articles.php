<?php

namespace app\Models;

use app\Model;
use app\Models\Pages;
use app\Models\ArticlesSections;

class Articles extends Model
{
	public const TABLE = 'articles';

	public static function check($parameters)
	{
		$f = 1;
		$ids = 0;

		$url = array_pop($parameters);
		$item = Articles::findWhere('WHERE url="'.$url.'"');
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
			if($ids != 12) $f = 0;
		}
		else $f = 0;

		return $f;
	}

	public static function getUrl($id)
	{
		$ids = $url = '';

		$res = Pages::findById(12);
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

		$item = Articles::findById($id);
		$url .= '/'.$item->url;

		return $url;
	}

	public static function articles($i) {

		$ArticlesUrl = Pages::getUrl(12);

		$pnum = 3;

		if($i == 0) {
			$Articles = Articles::findWhere('WHERE `show`=1 ORDER BY dates DESC, id DESC');
			if(!empty($Articles)) $count = count($Articles);
			else $count = 0;
		}

		$Articles = Articles::findWhere('WHERE `show`=1 ORDER BY dates DESC, id DESC LIMIT '.$i.','.$pnum);
		if(!empty($Articles)) {
			foreach($Articles AS $item) {
				if(empty($item->image)) {
					$item->image = '/public/src/img/noArticles.jpg';
				}
			}
		}

		$module = ROOT.'/public/views/blocks/ArticlesBlock.php';
		if(is_file($module)){ob_start();include $module;$module=ob_get_contents();ob_clean();}else{$module=false;}

		return $module;
	}
}