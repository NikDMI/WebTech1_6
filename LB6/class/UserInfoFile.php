<?php
require_once("../class/UsersInfo.php");

class UserInfoFile extends UsersInfo{
    //хранение в файле
    private $fileName;
    private $fileHandle;
    private const FORMAT_OFFSET=3;//размер добавочных символов в пароле и индексе пользователя
    //хранение в БД

    public function __construct(string $data) {
            $this->fileName = $data;
            $this->fileHandle = fopen($data,"a+t");
            if($this->fileHandle===false) throw new Exception("Не удалось открыть файл");
            $fileInfo = fstat($this->fileHandle);
            if($fileInfo===false) throw new Exception("Ошибка чтения файла");
            if($fileInfo['size']===0){//если файл новый
                fwrite($this->fileHandle,"0\n");//запись количества пользователей
            }    
    }

    public function __destruct(){
            fclose($this->fileHandle);
    }


    public function addNewUser(string $login, string $password){
            $fileInfo = fstat($this->fileHandle);
            if($fileInfo===false) throw new Exception("Ошибка чтения файла");
            $fileSize = $fileInfo['size'];//размер в байтах
            if($fileSize==0) return false;
            fseek($this->fileHandle,0);

            $fileText = fread($this->fileHandle,$fileSize);
            $userCount = (int)$fileText;//сколько пользователей зарегистировано
            //создание папки пользователя
            if(@mkdir("../data/userFolders/".$userCount)===false) throw new Exception("Не получилось создать папку пользователя");
            //создание нового текста для файла
            $fileText .= "L " . $login . " P " . $password . " I " . $userCount ."\n";//новый пользователь
            $posStr = mb_strpos($fileText,"\n");//поиск переноса после инфы про кол-во пользователей
            $fileText = ++$userCount . mb_strcut($fileText,$posStr);//??
            fclose($this->fileHandle);//закрытие старого дескриптора
            $this->fileHandle = fopen($this->fileName,"w+t");
            fwrite($this->fileHandle,$fileText);
    }

    public function findUserPassword(string $login):array|bool{
            $fileInfo = fstat($this->fileHandle);
            if($fileInfo===false) throw new Exception("Ошибка чтения файла");
            $fileSize = $fileInfo['size'];//размер в байтах
            if($fileSize==0) return false;
            fseek($this->fileHandle,0);

            $fileText = fread($this->fileHandle,$fileSize);
            if($fileText===false) throw new Exception("Ошибка чтения файла");
            $loginLength = mb_strlen($login);
            $index = mb_strpos($fileText, $login,0);//поиск первого совпадения
            while($index!==false){
                if(mb_strpos($fileText," ",$index)-$index==$loginLength){//если это не открывок логина
                    $loginHeader = mb_strcut($fileText,$index-2,2);
                    if($loginHeader=="L "){//заголово начала нового логина(пароль не содержит пробелов)
                        break;
                    }
                }
                $index = mb_strpos($fileText, $login,$index+1);//поиск дальше
            }
            if ($index===false) return $index;//не нашли такого пользователя
            $passPos = mb_strpos($fileText, " ",$index+1)+UserInfoFile::FORMAT_OFFSET;//поиск пробела между логином и паролем
            $passLen = mb_strpos($fileText," ",$passPos)-$passPos;
            $password = mb_strcut($fileText,$passPos,$passLen);

            $indexPos = $passPos+$passLen+UserInfoFile::FORMAT_OFFSET;
            $indexLen = mb_strpos($fileText, "\n", $indexPos)-$indexPos;//конец индекса
            $indexFolder = mb_strcut($fileText,$indexPos,$indexLen);//индекс папки
            return array('password'=>$password,'index'=>$indexFolder);
    }
}