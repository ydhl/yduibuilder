<?php
namespace yangzie;

/**
 * 通过后缀名判断给定的文件是不是图片
 *
 * @param $file
 * @return bool
 */
function yze_isimage($file){
	$type = array("png","gif","jpeg","jpg","bmp","ico","svg","webp");
	return in_array(strtolower(pathinfo($file,PATHINFO_EXTENSION) ?: $file), $type);
}

/**
 * this/is/../a/./test/.///is, 格式化成this/a/test/is，但要注意不能有stream wrapper，比如http:// phar://等
 * //会被处理掉
 * @param $path
 * @param string $in
 * @return string
 */
function yze_get_abs_path($path, $in=''){
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $in."/".ltrim($path, "/"));
    $leftHasSperator = substr($path, 0, 1) == '/' || substr($path, 0) == '\\';
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return ($leftHasSperator ? DIRECTORY_SEPARATOR : '').implode(DIRECTORY_SEPARATOR, $absolutes);
}

/**
 * 从路径path中删除need_remove
 *
 * @param $path
 * @param $need_remove
 * @return string
 */
function yze_remove_path($path, $need_remove){
	$path = strtr($path, array(DS=>"/"));
    $need_remove =  strtr($need_remove, array(DS=>"/"));
	return strtr($path, array($need_remove=>''));
}

/**
 *
 * 把文件移到指定目录中去, 并返回移动成功后的目标文件路径，移动失败则返回false
 *
 * @param unknown_type $src_file 绝对路径
 * @param unknown_type $dist_dir 绝对路径
 */
function yze_move_file($src_file, $dist_dir){
	$dist_file = yze_copy_file($src_file, $dist_dir);
	if($dist_file){
		@unlink($src_file);
		return $dist_file;
	}else{
		return false;
	}
}

/**
 * 把src_file 拷贝到 dist_dir 中去, 并返回拷贝成功的一文件路径，如果拷贝失败返回false
 * dist_dir不存在则创建
 *
 * @author leeboo
 *
 * @param unknown $src_file
 * @param unknown $dist_dir
 * @return unknown|string
 *
 * @return
 */
function yze_copy_file($src_file, $dist_dir){
	if (!$dist_dir){
		return false;
	}

	yze_make_dirs($dist_dir);

	$dist_file = rtrim($dist_dir,DS).DS.basename($src_file);
	return copy($src_file,$dist_file) ? $dist_file : false ;
}

/**
 *
 * 拷贝目录及其下所有子目录文件到指定目录
 * @param $srcDir
 * @param $destDir
 */
function yze_copy_dir($srcDir, $destDir) {
    if ( ! file_exists($destDir) ) {
        if ( ! mkdir($destDir, 0777, true) ) {
            return false;
        }
    }
    $dir_handle = opendir($srcDir);
    while ( false !== ( $file = readdir($dir_handle)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {

            if ( is_dir($srcDir . DS . $file) ) {
                yze_copy_dir($srcDir . DS . $file, $destDir . DS . $file);
            } else {
                if( ! copy($srcDir . DS . $file, $destDir . DS . $file)){
                    closedir($dir_handle);
                    return false;
                }
            }
        }
    }
    closedir($dir_handle);

    return true;
}

/**
 *  根据传入的目录路径创建它们, 目录存在不做处理
 *
 * @param unknown_type $dirs 绝对地址
 */
function yze_make_dirs($dirs){
	if (file_exists($dirs))return;
    $dir = '';
	foreach (explode(DS,strtr(rtrim($dirs,DS),array("/"=>DS))) as $d){
		$dir = @$dir.$d.DS;
		@mkdir($dir,0777);
	}
}
