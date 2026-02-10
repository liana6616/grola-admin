<?php

namespace app\Models;

use app\Model;

class TelegramMessage extends Model
{
    public const TABLE = 'telegram_message';

    public static function newMess($id,$messId,$mess,$type,$keyboard)
    {
        if(!empty($messId)) {
            $m = json_decode($messId,true);

            $tm = new TelegramMessage;
            $tm->chat_id = $id;
            $tm->mess_id = $messId;
            $tm->date = mktime(date('H'),date('i'),date('s'),date('n'),date('j'),date('Y'));
            $tm->message = $mess;
            $tm->answer = '';
            $tm->answer_id = '';
            $tm->type_keyboard = $type;
            $tm->keyboard = $keyboard;
            $tm->save();
        }
    }
}

?>
