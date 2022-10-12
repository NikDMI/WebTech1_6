<?php
$userIndex;
if(isset($_POST['index'])){
    $userIndex = $_POST['index'];
}else if($argc>1){
    $userIndex = $argv[1];
}else {
    exit('Что-то пошло не так. Ошибка №1');
}
require_once("../class/FileSystemObject.php");

$userDirectory = __DIR__. "\\..\\data\\userFolders\\" . "{$userIndex}\\";
$files = FileSystemObject::readCatalog($userDirectory);//чтение каталога пользователя

$choosedFileName = "";
if(isset($_POST['choosedFileName'])) $choosedFileName = $_POST['choosedFileName'];

$currentFile=null;//выбранный файл для просмотра
foreach($files as $file){
    if($file->getFileName()===$choosedFileName){
        $currentFile = $file;
        break;
    }
}

if(isset($_POST['control'])){//если пользователь нажал на КНОПКУ
    switch($_POST['control']){
        case 'addFile':
            if(isset($_FILES['userfile'])){
                $fileName = $userDirectory . basename($_FILES['userfile']['name']);
                move_uploaded_file($_FILES['userfile']['tmp_name'], $fileName);
                $files = FileSystemObject::readCatalog($userDirectory);//обновление списка
            }
            break;

        case 'removeFile':
            if($currentFile!=null) unlink($currentFile->getFullPath());
            $files = FileSystemObject::readCatalog($userDirectory);//обновление списка
            break;

        case 'getFile':
            if($currentFile!=null){
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$choosedFileName.'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . (int)$currentFile->getFileSize());
                readfile($currentFile->getFullPath());
                exit;
            }
            break;
    }


}else if(isset($_POST['showPreview'])){//отправить скрипт показа ПРЕДПРОСМОТРА
    echo "let lastPreview = document.getElementById('previewItem');
    lastPreview?.remove();
    previewCheckbox.checked='';
    ";
    
    if($currentFile!=null && (int)$currentFile->getFileSize()>0){
        $dotPos = mb_strpos($choosedFileName,".");//позиция начала расширения
        $extension = mb_strcut($choosedFileName,$dotPos+1);
        if($extension=="png"){//пока выводим только такой формат изображений
            echo "let previewItem = document.createElement('img');
            previewItem.style.backgroundImage = 'url(../data/userFolders/{$userIndex}/{$choosedFileName})';
            previewItem.classList.add('preview_item');
            previewItem.classList.add('preview_item_img');
            previewItem.id = 'previewItem';
            previewList.append(previewItem);";
        }else if($extension=="txt"){//вывод текста
            $fileHandle = fopen($currentFile->getFullPath(),"rt");
            $fileText = fread($fileHandle,(int)$currentFile->getFileSize());
            $fileText = htmlspecialchars($fileText);
            $fileText = preg_replace("/\n/",'\n',$fileText);
            
            echo "let previewItem = document.createElement('pre');
            previewItem.classList.add('preview_item');
            previewItem.classList.add('preview_item_text');
            previewItem.id = 'previewItem';
            previewItem.textContent = \"{$fileText}\";
            previewList.append(previewItem);";
        }
    }
    exit();
}

//ФОРМИРОВАНИЕ СТРАНИЦЫ
require_once("../html/mainPage.html");//загрузка скелета страницы
echo "<script>";//добавление скрипта для отображения файлов
echo "let lastChoosedItem=null;
textIndex.value = '{$userIndex}';
textIndex2.value = '{$userIndex}';";
echo "function onClick(event){
    //alert('fo');
    lastChoosedItem?.classList.remove('file_item_choosed');
    event.target.classList.add('file_item_choosed'); lastChoosedItem=event.target;
    previewCheckbox.checked='checked';//передача флага
    textChoosedFile.value = event.target.textContent;
    let formData = new FormData(mainForm);
    let xhr = new XMLHttpRequest();
    xhr.open('POST','mainPage.php',false);
    xhr.send(formData);//отправка формы серверу
    //alert(xhr.response);
    eval(xhr.response);
}";
echo "let panel = document.getElementById('filePanel'); let item;";
foreach($files as $file){//добавление файлов в элемент
    echo "item = document.createElement('div');";
    echo "panel.append(item);";
    echo "item.addEventListener('click',onClick);";
    $fileName  = $file->getFileName();
    echo "item.textContent = '{$fileName}';";
    echo "item.classList.add('file_item');";
}
echo "</script>";
