<?php 
/**********************
���� Spider
���Ϲ����ĸ���PHP����ȫ����û
���һЩ������εĺ�����Ҫ�Լ��������
���ʲ����ٷ�֮һ
**********************/
error_reporting(E_ERROR);
ini_set('max_execution_time',20000);
ini_set('memory_limit','512M');
header("content-Type: text/html; charset=gb2312");
$matches = array(
        '/function\_exists\s*\(\s*[\'|\"](popen|exec|proc\_open|system|passthru)+[\'|\"]\s*\)/i',
        '/(exec|shell\_exec|system|passthru)+\s*\(\s*\$\_(\w+)\[(.*)\]\s*\)/i',
        '/((udp|tcp)\:\/\/(.*)\;)+/i',
        '/preg\_replace\s*\((.*)\/e(.*)\,\s*\$\_(.*)\,(.*)\)/i',
        '/preg\_replace\s*\((.*)\(base64\_decode\(\$/i',
        '/(eval|assert|include|require|include\_once|require\_once)+\s*\(\s*(base64\_decode|str\_rot13|gz(\w+)|file\_(\w+)\_contents|(.*)php\:\/\/input)+/i',
        '/(eval|assert|include|require|include\_once|require\_once|array\_map|array\_walk)+\s*\(\s*\$\_(GET|POST|REQUEST|COOKIE|SERVER|SESSION)+\[(.*)\]\s*\)/i',
        '/eval\s*\(\s*\(\s*\$\$(\w+)/i',
        '/(include|require|include\_once|require\_once)+\s*\(\s*[\'|\"](\w+)\.(jpg|gif|ico|bmp|png|txt|zip|rar|htm|css|js)+[\'|\"]\s*\)/i',
        '/\$\_(\w+)(.*)(eval|assert|include|require|include\_once|require\_once)+\s*\(\s*\$(\w+)\s*\)/i',
        '/\(\s*\$\_FILES\[(.*)\]\[(.*)\]\s*\,\s*\$\_(GET|POST|REQUEST|FILES)+\[(.*)\]\[(.*)\]\s*\)/i',
        '/(fopen|fwrite|fputs|file\_put\_contents)+\s*\((.*)\$\_(GET|POST|REQUEST|COOKIE|SERVER)+\[(.*)\](.*)\)/i',
        '/echo\s*curl\_exec\s*\(\s*\$(\w+)\s*\)/i',
        '/new com\s*\(\s*[\'|\"]shell(.*)[\'|\"]\s*\)/i',
        '/\$(.*)\s*\((.*)\/e(.*)\,\s*\$\_(.*)\,(.*)\)/i',
        '/\$\_\=(.*)\$\_/i',
        '/\$\_(GET|POST|REQUEST|COOKIE|SERVER)+\[(.*)\]\(\s*\$(.*)\)/i',
        '/\$(\w+)\s*\(\s*\$\_(GET|POST|REQUEST|COOKIE|SERVER)+\[(.*)\]\s*\)/i',
        '/\$(\w+)\s*\(\s*\$\{(.*)\}/i',
        '/\$(\w+)\s*\(\s*chr\(\d+\)/i'
);
function antivirus($dir,$exs,$matches) {
        if(($handle = @opendir($dir)) == NULL) return false;
        while(false !== ($name = readdir($handle))) {
                if($name == '.' || $name == '..') continue;
                $path = $dir.$name;
                if(is_dir($path)) {
                        //chmod($path,0777);/*��Ҫ���һЩ0111��Ŀ¼*/
                        if(is_readable($path)) antivirus($path.'/',$exs,$matches);
                } elseif(strpos($name,';') > -1 || strpos($name,'%00') > -1 || strpos($name,'/') > -1) {
                        echo '���� <input type="text" style="width:218px;" value="����©��"> '.$path.'<div></div>'; flush(); ob_flush();
                } else {
                        if(!preg_match($exs,$name)) continue;
                        if(filesize($path) > 10000000) continue;
                        $fp = fopen($path,'r');
                        $code = fread($fp,filesize($path));
                        fclose($fp);
                        if(empty($code)) continue;
                        foreach($matches as $matche) {
                                $array = array();
                                preg_match($matche,$code,$array);
                                if(!$array) continue;
                                if(strpos($array[0],"\x24\x74\x68\x69\x73\x2d\x3e")) continue;
                                $len = strlen($array[0]);
                                if($len > 6 && $len < 200) {
                                        echo '���� <input type="text" style="width:218px;" value="'.htmlspecialchars($array[0]).'"> '.$path.'<div></div>';
                                        flush(); ob_flush(); break;
                                }
                        }
                        unset($code,$array);
                }
        }
        closedir($handle);
        return true;
}
function strdir($str) { return str_replace(array('\\','//','//'),array('/','/','/'),chop($str)); }
echo '<form method="POST">';
echo '·��: <input type="text" name="dir" value="'.($_POST['dir'] ? strdir($_POST['dir'].'/') : strdir($_SERVER['DOCUMENT_ROOT'].'/')).'" style="width:398px;"><div></div>';
echo '��׺: <input type="text" name="exs" value="'.($_POST['exs'] ? $_POST['exs'] : '.php|.inc|.phtml').'" style="width:398px;"><div></div>';
echo '����: <input type="submit" style="width:80px;" value="scan"><div></div>';
echo '</form>';
if(file_exists($_POST['dir']) && $_POST['exs']) {
        $dir = strdir($_POST['dir'].'/');
        $exs = '/('.str_replace('.','\\.',$_POST['exs']).')/i';
        echo antivirus($dir,$exs,$matches) ? '<div></div>ɨ�����' : '<div></div>ɨ���ж�';
}
?>