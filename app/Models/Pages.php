<?php

namespace app\Models;

use app\Db;
use app\Model;
use app\Helpers;
use app\Models\Gallery;
use app\Models\Settings;

class Pages extends Model
{
	public const TABLE = 'pages';

	public static function breadCrumbs($id)
	{
		// Хлебные крошки
		$br = array();
		$p = Pages::findById($id);
		while ($p) {
			$br[] = array('name' => $p->name, 'id' => $p->id, 'url' => Pages::getUrl($p->id));
			$p = Pages::findById($p->parent);
		}
		$bread = array_reverse($br);
		// --- // --- //

		return $bread;
	}

	public static function adminBreadCrumbs($id)
	{
		$b = [];

		if(!empty($id)) {
			$bread = '';
			$res = Pages::findById($id);
			while($res->id) {
				if($bread <> '') $bread .= ",";
				$bread .= $res->id;
				if($res->parent <> 0 ) $res = Pages::findById($res->parent);
				else $res->id = false;
			}
			$bread = array_reverse(explode(',',$bread));

			foreach($bread AS $item) {
				$item = Pages::findById($item);
				$b[] = array('name' => $item->name_menu, 'id' => $item->id, 'url' => '/'.ADMIN_LINK.'/pages?parent='.$item->id);
			}
		}

		return $b;
	}

	public static function getUrl($id) {
		$ids = $url = '';

		$res = Pages::findById($id);
		if($id == 1){
			$url = '/';
		}else{
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
					if(!empty($res->url)){
						$url .= '/'.$res->url;
					}else{
						$url .= $res->url;
					}
				}
			}
		}

		return $url;
	}

	public static function checkUrl($id,$parameters) {
		$f = 1;
		$obj = Pages::findById($id);
		while($obj->ids <> 0) {
			$obj = Pages::findById($obj->ids);
			$url = array_pop($parameters);
			if($obj->url != $url) $f = 0;
		}

		return $f;
	}

	public static function check($parameters) {
		$f = 1;
		$ids = 1;
		foreach($parameters AS $item) {
			$obj = Pages::findWhere('WHERE ids="'.$ids.'" AND url="'.$item.'"');
			if(!empty($obj)) {
				$ids = $obj[0]->id;
			}
			else {
				$f = 0;
				break;
			}
		}

		return $f;
	}

	public static function getArray() {
		$pages = Pages::findAllShow();
		$array = [];
		foreach ($pages AS $page) {
			$id = $page->id;

			$childs = [];
			if (empty($page->parent)) {
				$page_childs = Pages::findWhere("WHERE parent='{$id}' AND `show` = 1 ORDER BY `rate` DESC, id ASC");
				if (!empty($page_childs)) {
					foreach ($page_childs AS $child) {
						$child_id = $child->id;
						$childs[$child_id] = $child;
					}
				}
			}
			$page->childs = $childs;

			$array[$id] = $page;
		}
		return $array;
	}
}
