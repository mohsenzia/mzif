<?php

class User extends Model
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function getAuth()
    {
        if (!isset($_COOKIE['token']))
            return false;

        $cookieUID = $_COOKIE['token'];
        $user = User::model()->selectByField(array('token' => $cookieUID));
        if (isset($user->id)) {
            return $user;
        } else
            return false;

    }
}
