<?php

class UsersInfo{//класс хранилища логинов и паролей пользователей
    public function checkName(string $name):bool{//проверка логина и пароля
        $res=preg_match("/[_a-zA-Z0-9]+/",$name,$matches);
        if($res===false) throw new Exception("Ошибка в системной функции preg_match");
        if(count($matches) && $matches[0]==$name) return true;
        return false;
    }
}