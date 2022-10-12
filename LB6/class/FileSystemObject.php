<?php
class FileSystemObject{
    private string $fileName;
    private string $fullPath;
    private int $fileSize;
    private int $fileType;

    private const TYPE_FILE=33206;
    private const TYPE_DIR=16895;
    private const TYPE_OTHER=2;

    public const SIZE_B=0;
    public const SIZE_KB=1;
    public const SIZE_MB=2;
    public const SIZE_GB=3;

    public function __construct(string $fileName,string $dir="./"){
        if(file_exists($dir . $fileName)){
            $this->fileName=$fileName;
            $this->fullPath=$dir . $fileName;
            $fileInfo=stat($this->fullPath);
            if($fileInfo!==false){
                //тип файла
                $type=$fileInfo['mode'];
                if($type==self::TYPE_FILE || $type==self::TYPE_DIR){
                    $this->fileType=$type;
                }else $this->fileType=self::TYPE_OTHER;
                //размер
                $this->fileSize=$fileInfo['size'];
            }
        }else{
            throw new Exception("Can't find the file");
        }
    }

    public function getFileSize(int $sizeType=self::SIZE_B):string{
        switch($sizeType){
            case self::SIZE_KB:
                return round($this->fileSize/1024,2) . " KB";
            break;
            case self::SIZE_MB:
                return round($this->fileSize/1024/1024,2) . " MB";
            break;
            case self::SIZE_GB:
                return round($this->fileSize/1024/1024/1024,2) . " GB";
            break;
            default:
                 return $this->fileSize . " B";
            break;
        }
    }

    public function getFileSizeBytes():int{
        return $this->fileSize;
    }

    public function showFileInfo(int $sizeType=self::SIZE_B):string{
        $str;
        switch($this->fileType){
            case self::TYPE_FILE:
                $str="file";
            break;
            case self::TYPE_DIR:
                $str="dir";
            break;
            default:
                $str="??";
            break;
        }
        $str.=" ".$this->fileName;
        return $str;
    }

    public static function readCatalog(string $dir):array{
        if(file_exists($dir)){
            $files=scandir($dir);
            if($files!==false){
                $filesInfo=[];
                foreach($files as $file){
                    if($file!='.' && $file!='..'){
                        try{
                            $filesInfo[]=new FileSystemObject($file,$dir);
                        }catch(Exception $e){
                        }
                    }
                }
                return $filesInfo;
            }else{
                throw new Exception('Problems with scanning');
            }
        }else{
            throw new Exception($dir.' is not exists');
        }
    }

    public static function readCatalogRecursive(string $dir):array{
        $totalFiles=self::readCatalog($dir);
        foreach($totalFiles as $file){
            if($file->isDir()){
                $readFiles=self::readCatalogRecursive($file->getFullPath());
                foreach($readFiles as $addFile){
                    $totalFiles[]=$addFile;
                }
            }
        }
        return $totalFiles;
    }

    public function isDir():bool{
        return ($this->fileType==self::TYPE_DIR)?true:false;
    }

    public function isFile():bool{
        return ($this->fileType==self::TYPE_FILE)?true:false;
    }

    public function getFileName():string{
        return $this->fileName;
    }

    public function getFullPath():string{
        if($this->fileType!=self::TYPE_DIR){
            return $this->fullPath; 
        }else{
            return $this->fullPath . '/';
        }
    }
}