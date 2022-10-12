<?php
define("MAX_HTML_SIZE",100000);
define("COOKIE_TIME",3600*24);


function changeEditValue($login,$password){
    echo "<script>let login = document.getElementById(\"loginEdit\"); login.value = \"{$login}\"
    let password = document.getElementById(\"passwordEdit\"); password.value = \"{$password}\"</script>";
}

function exitSctipt(string $message,$login="",$password=""){
    $logPage = fopen("../html/login.html","rt");
    $logPage_str = fread($logPage,MAX_HTML_SIZE);//чтение до .. байт
    echo $logPage_str;
    changeEditValue($login,$password);
    if($message!="") echo "<script>alert(\"{$message}\")</script>";
    exit();
}

if(@$_POST['login']=="" || @$_POST['password']==""){
    if(count($_COOKIE)==0) exitSctipt("Вы не ввели параметры входа");
    //если был сохранен пароль и логин
    exitSctipt("",$_COOKIE['login'],$_COOKIE['password']);
}

require_once("../class/UsersInfo.php");
require_once("../class/UserInfoFile.php");
require_once("../class/UserInfoMysql.php");
//$userPasswords = new UserInfoFile("../data/usersPasswords.txt");
$userPasswords = new UserInfoMysql("usersinformation_lb6");

$userLogin = $_POST['login'];
$userPassword = $_POST['password'];
if(!$userPasswords->checkName($userLogin) || !$userPasswords->checkName($userPassword)){
    exitSctipt("Некорректный ввод логина и пароля",$userLogin,$userPassword);
}

if(isset($_POST['createNewUser'])){//создание нового пользователя
    $isExist = $userPasswords->findUserPassword($userLogin);
    if($isExist!==false) exitSctipt("Пользователь с таким логином уже существует",$userLogin,$userPassword);
    try{
        $userPasswords->addNewUser($userLogin,$userPassword);
    }catch(Exception $e){
        //здесь исключение, что директория есть, игнорируем его
    }
}

$userInfo = $userPasswords->findUserPassword($userLogin);
if($userInfo===false) exitSctipt("Пользователь с таким логином не найден",$userLogin,$userPassword);
$filePassword = $userInfo['password'];
if($filePassword!=$userPassword) exitSctipt("Не верный пароль!",$userLogin,$userPassword);

if(isset($_POST['rememberMe'])){//запомнить пользователя
    setcookie("login",$userLogin,time()+COOKIE_TIME);
    setcookie("password",$userPassword,time()+COOKIE_TIME);
}
//пользователь авторизован, выполнить вход в систему:
$userIndex = $userInfo['index'];
echo `php mainPage.php {$userIndex}`;