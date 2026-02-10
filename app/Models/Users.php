<?php

namespace app\Models;

use app\Db;
use app\Model;
use app\Helpers;
use app\Models\Settings;
use app\Models\Users_class;

class Users extends Model
{
    public const TABLE = 'users';

    public static function checkUserData($login, $password)
    {
        $db = new Db();
        $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE login = :login';

        $user = $db->query(
            $sql,
            [':login' => $login],
            self::class
        );

        if ($user) {
            if (password_verify($password, $user[0]->password)) {
                return $user[0];
            }
            else {
                $_SESSION['notice'] = 'Неверный пароль';
            }
        }
        else {
            $_SESSION['notice'] = 'Логин не зарегистрирован';
        }

        return false;
    }

    public static function auth($user)
    {
        setcookie("user", $user->hash, time() + 7776000, "/", $_SERVER['SERVER_NAME'], "0");  //на 90 дней

        $class = Users_class::findById($user->class_id);

        $_SESSION['user']['id'] = $user->id;
        $_SESSION['user']['hash'] = $user->hash;
        $_SESSION['user']['login'] = $user->login;
        $_SESSION['user']['name'] = $user->name;
        $_SESSION['user']['image'] = $user->image;
        $_SESSION['user']['class'] = $class->id;
        $_SESSION['user']['className'] = $class->name;
    }

    public static function logout()
    {
        unset($_SESSION["user"]);
        setcookie("user", "", time() - 7776000, "/", $_SERVER['SERVER_NAME'], "0");
    }

    public static function getLogin()
    {
        return $_SESSION['user']['login'];
    }

    public static function isGuest()
    {
        if (isset($_SESSION['user'])) {
            return false;
        }
        return true;
    }

    public static function isUser()
    {
        if(!isset($_SESSION['user']))
        {
            if(isset($_COOKIE['user']))
            {
                $user = Users::findByHash($_COOKIE['user']);
                if(!empty($user)) {
                    $class = Users_class::findById($user->class_id);

                    $_SESSION['user']['id'] = $user->id;
                    $_SESSION['user']['hash'] = $user->hash;
                    $_SESSION['user']['login'] = $user->login;
                    $_SESSION['user']['name'] = $user->name;
                    $_SESSION['user']['image'] = $user->image;
                    $_SESSION['user']['class'] = $class->id;
                    $_SESSION['user']['className'] = $class->name;
                }
            }
        }

        return !self::isGuest();
    }

    public static function selectClass()
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.self::TABLE.'_class` ORDER BY id ASC';

        return $db->query(
            $sql,
            [],
            self::class
        );
    }

    public static function getClass($id)
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.self::TABLE.'_class` WHERE id='.$id.' LIMIT 1';

        $data = $db->query(
            $sql,
            [],
            self::class
        );

        return $data[0];
    }

    public static function getUserClass()
    {
        if(isset($_SESSION['user'])) return $_SESSION['user']['class'];
        else return '';
    }
}
