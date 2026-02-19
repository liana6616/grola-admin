<?php

namespace app\Models;

use app\Db;
use app\Model;

class Files extends Model
{
    public const TABLE = 'files';

    public $type;
    public $ids;
    public $file;
    public $filename;
    public $ext;
    public $rate;

    public static function findFiles($type, $ids, $limit = 1000)
    {
        if(empty($ids)) $ids = 0;

        $db = new Db();
        $sql = 'SELECT * FROM `'.static::TABLE.'` WHERE `type`="'.$type.'" AND `ids`='.(int)$ids.' ORDER BY rate DESC LIMIT '.$limit;

        $data = $db->query(
            $sql,
            [],
            static::class
        );

        return $data ? $data : false;
    }

    public static function del($id)
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.static::TABLE.'` WHERE `id`='.$id.'';

        $data = $db->query(
            $sql,
            [],
            static::class
        );

        unlink(ROOT.$data[0]->image);
        unlink(ROOT.$data[0]->image_small);
        unlink(ROOT.$data[0]->image_origin);
        $data[0]->delete();
    }

    public static function delAll($type,$ids)
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.static::TABLE.'` WHERE `type`="'.$type.'" AND `ids`='.$ids.'';

        $data = $db->query(
            $sql,
            [],
            static::class
        );

        if(!empty($data))
        {
            foreach($data AS $item)
            {
                unlink(ROOT.$item->image);
                unlink(ROOT.$item->image_small);
                unlink(ROOT.$item->image_origin);
                $item->delete();
            }
        }
    }
}