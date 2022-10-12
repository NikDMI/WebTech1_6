<?php
require_once("../class/UsersInfo.php");

class UserInfoMysql extends UsersInfo{
    //хранение в БД
    private $mysqli;

    public function __construct(string $nameDB) {
            try{
                $this->mysqli=new mysqli("localhost","root","1234",$nameDB);
            }catch(Exception $e){
                exit($e->getMessage());
            }
            $this->mysqli->query("SET CHARSET 'UTF8'");   
    }

    public function __destruct(){
            $this->mysqli->close();
    }


    public function addNewUser(string $login, string $password){
            $result = $this->mysqli->query("SELECT * FROM `user` ORDER BY `userID` DESC;");
            $userCount = 0;//сколько пользователей зарегистировано
            if($result->num_rows!=0){
                $row = $result->fetch_assoc();//строка с максимальным индексом
                $userCount = $row['userID']+1;
            }
            $result->free();
            //добавление пользователя в БД
            $this->mysqli->query("INSERT INTO `user` VALUES(\"{$login}\",\"{$password}\",\"{$userCount}\"); ");
            //создание папки пользователя
            if(@mkdir("../data/userFolders/".$userCount)===false) throw new Exception("Не получилось создать папку пользователя");
    }

    public function findUserPassword(string $login):array|bool{
            $result = $this->mysqli->query("SELECT * FROM `user` WHERE `userLogin` LIKE \"%${login}%\";");
            if($result->num_rows==0){
                $result->free();
                return false;
            }
            $row = $result->fetch_assoc();
            $resArray = array('password'=>$row['userPassword'],'index'=>$row['userID']);
            $result->free();
            return $resArray;
    }
}