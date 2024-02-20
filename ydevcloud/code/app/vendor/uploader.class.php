<?php
namespace app\vendor;
use yangzie\YZE_RuntimeException;
use const DS;
use const YZE_DEVELOP_MODE;
use const YZE_INSTALL_PATH;
use function yangzie\__;

class Uploader{
	const IMAGE = 'image';
	const FLASH = 'flash';
	const VIDEO = 'video';
	const MOVIE = 'moive';
	const ARCHIVE = 'archive';
	private $upload_path;
	public $file_type;
	private $watermark = true;//是否加水印，默认要加

	private $uploaded_filenames = array();

	/**
	 * @param string $upload_path 上传文件的保存路径
	 */
	public function __construct($upload_path){
		$this->upload_path = $upload_path;
	}
	/**
	 * 把临时目录中的上传文件copy到正式目录，并返回文件的web路径
	 *
	 * @param unknown_type $name
	 * @param unknown_type $path
	 * @param unknown_type $size
	 * @return unknown
     * @throws
	 */
	private function uploadByCopy($name, $path, $size){
		$ext = pathinfo($name,PATHINFO_EXTENSION);
		$dist_path = rtrim($this->upload_path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$this->mk_local_dirs($dist_path);
		$filename = uniqid(date("ynj")).".".$ext;
		if(!copy($path,$dist_path.$filename)){
			throw new YZE_RuntimeException("Can't upload file,can not copy (".$path.") to (".$dist_path.$filename.")");
		}
		return $dist_path.$filename;
	}

	private function addWatermark($dist_path_filename){
		list($width, $height, $type, $attr) = @getimagesize($dist_path_filename);
		if($type == IMAGETYPE_JPEG){
			$src_im = imagecreatefromgif(YZE_INSTALL_PATH."28td.gif");//水印图片.
		    $pct = 20;//透明度  0 - 100.100为为透明.
			$dst_im = imagecreatefromjpeg($dist_path_filename);
			$dst_x = imagesx($dst_im) - imagesx($src_im)-10;//距离右边10
		    $dst_y = 10;//距离上边10
		    imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, 0, 0, imagesx($src_im), imagesy($src_im), $pct);
			imagejpeg($dst_im, $dist_path_filename ,100);
			imagedestroy($dst_im);
		}
	}

	private function mk_local_dirs($path){
		#如果已经是目录，直接退出
		if( is_dir($path) ) return;
		#构建上级文件夹
		$this->mk_local_dirs(dirname($path));
		#构建当前文件夹
		if(!@mkdir($path, 0777)){
			//throw new YZE_RuntimeException("Can\'t create dir ".$path);
		}
		@chmod($path,0777);
	}

	/**
	 * 一次上传一个文件，上传文件的控件名相同，$_FILES结构如下：
	 * Array
	 * (
	 * 	[comment_upload_file] => Array
	 * 	(
	 * 		[name] => lizidevstudio.jpg
	 * 		[type] => image/jpeg
	 * 		[tmp_name] => C:\WINDOWS\Temp\php7C.tmp
	 * 		[error] => 0
	 * 		[size] => 6445
	 *	)
	 * )
	 *
	 *
	 * @param unknown_type $name
	 *
	 * @return string 返回文件在服务器上的绝对路径
     * @throws
	 */
	public function upload($name, $watermark = false){
		$this->watermark = $watermark;
		$filename = $_FILES[$name]['name'];
		$fileTempName = $_FILES[$name]['tmp_name'];
		$fileSize = $_FILES[$name]['size'];
		$erorinfo = $this->getUploadError($_FILES[$name]);
		if(!empty($erorinfo)){
			throw new YZE_RuntimeException($erorinfo);
		}
		$this->uploaded_filenames = $filename;
		return $this->uploadByCopy($filename,$fileTempName,$fileSize);
	}

	public function convert_to_relative($prefix_path, $full_path)
	{
        return self::remove_abspath($prefix_path, $full_path);
	}

	public static function remove_abspath($prefix_path, $full_path)
	{
		return "/".ltrim(strtr($full_path, array($prefix_path=>'',DS=>"/")),"/");
	}

	/**
	 * 一次上传多个文件，上传文件的控件名相同，$_FILES结构如下：
	 * Array
	 * (
	 * 	[comment_upload_file] => Array
	 * 	(
	 * 		[name] => Array
	 * 			(
	 * 				[0..n] => lizidevstudio.jpg
	 * 			)
	 * 		[type] => Array
	 * 			(
	 * 				[0..n] => image/jpeg
	 * 			)
	 * 		[tmp_name] => Array
	 * 			(
	 * 				[0..n] => C:\WINDOWS\Temp\php7C.tmp
	 * 			)
	 * 		[error] => Array
	 * 			(
	 * 				[0..n] => 0
	 * 			)
	 * 		[size] => Array
	 * 			(
	 * 				[0..n] => 6445
	 * 			)
	 *	)
	 * )
	 *
	 *
	 * @param unknown_type $name
	 *
	 * @return array 返回上传成功后的文件列表,没有文件上传将返回array() key为传入的原索引;
     * @throws
	 */
	public function upload_mutil_files($upload_name, $watermark = false){
		$this->watermark = $watermark;
		$names = @$_FILES[$upload_name]['name'];
		$types = @$_FILES[$upload_name]['type'];
		$tmpNames = @$_FILES[$upload_name]['tmp_name'];
		$errors = @$_FILES[$upload_name]['error'];
		$sizes = @$_FILES[$upload_name]['size'];

		$files = array();
		foreach((array)$names as $index => $name){
			if(empty($name))continue;#可能没有指定文件来出现空值，如果都是空值将方法将返回array()
			//$name可能是数组也可能不是数组，有如下的情况，
// 情况1：name=$upload_name[subname][]
//Array
//(
//    [$upload_name] => Array
//        (
//            [name] => Array
//                (
//                    [subname] => Array
//                        (
//                            [0] => abrt.png
//                            [1] =>
//                            [2] => evolution-memos.png
//                        )
//                )
//        )
//
//)
// 情况2：name=$upload_name[]
//Array
//(
//    [$upload_name] => Array
//        (
//            [name] => Array
//                (
//                     [0] => abrt.png
//                     [1] =>
//                     [2] => evolution-memos.png
//                )
//        )
//
//)
//
            if (is_array($name)) {
            	foreach ($name as $sub_index => $n) {
            		if(empty($n))continue;#可能没有指定文件来出现空值
	                $file = array('name'=>$n,
	                        'type'=>$types[$index][$sub_index],
	                        'tmp_name'=>$tmpNames[$index][$sub_index],
	                        'error'=>$errors[$index][$sub_index],
	                        'size'=>$sizes[$index][$sub_index]
	                );
	                $errorinfo = $this->getUploadError($file);
	                if(!empty($errorinfo)){
	                    throw new YZE_RuntimeException($errorinfo);
	                }
	                $this->uploaded_filenames[$index][$sub_index] = $name;
	                $files[$index][$sub_index] = $this->uploadByCopy($n,$tmpNames[$index][$sub_index],$sizes[$index][$sub_index]);
            	}
            }else{
            	$file = array('name'=>$name,
                        'type'=>$types[$index],
                        'tmp_name'=>$tmpNames[$index],
                        'error'=>$errors[$index],
                        'size'=>$sizes[$index]
            	);
            	$errorinfo = $this->getUploadError($file);
            	if(!empty($errorinfo)){
            		throw new YZE_RuntimeException($errorinfo);
            	}
            	$this->uploaded_filenames[$index] = $name;
            	$files[$index] = $this->uploadByCopy($name,$tmpNames[$index],$sizes[$index]);
            }
		}
		return $files;
	}
	/**
	 * 错误处理函数
	 *
	 * @param String $name
	 *
	 * @return String---errorcode
	 */
	public  function getUploadError($files){
		if ( ! $files)return __("No file was uploaded.");

		$this->errorCode = $files['error'];
		switch ($this->errorCode){
			case UPLOAD_ERR_INI_SIZE :
				return __("文件大小超过了系统设置允许的文件大小(upload_max_filesize)");
				//"上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值"
			case UPLOAD_ERR_FORM_SIZE :
				return __("文件大小超过了表单中设置的最大文件大小(MAX_FILE_SIZE)");
				//"上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值"
			case UPLOAD_ERR_PARTIAL :
				return __("上传文件不完整, 有丢失");
				//"文件只有部分被上传"
				break;
			case UPLOAD_ERR_NO_FILE :
				return __("没有选择文件上传");
				//"没有文件被上传"
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				return __("临时目录不存在");
				//"找不到临时文件夹"
				break;
			case UPLOAD_ERR_CANT_WRITE:
				return __("上传目录中无法写入");
				//"文件写入失败"
				break;
			case UPLOAD_ERR_EXTENSION:
				return __("文件上传终止");
		}
		$ext = strtolower(pathinfo($files['name'],PATHINFO_EXTENSION));
		switch($this->file_type){
			case self::FLASH:
				if(!preg_match("/^application\/x-shockwave-flash\/*/",$files['type'])){
					return __(vsprintf("只能上传 Flash：%s",array($files['type'])));
				}
				break;
			case self::ARCHIVE:
				if(!in_array($ext,array('rar','zip'))){
					return __(vsprintf("附件只能上传.RAR .ZIP格式：%s",array($files['type'])));
				}
				break;
			case self::IMAGE:
				if(!preg_match("/^image\/*/",$files['type'])){
					return __(vsprintf("只能上传图片：%s",array($files['type'])));
				}
				break;
			default:
				if(in_array($ext,
					array('php','php3','phar','vbs','dll','js','com','bat','cmd','vbe','jse','wsf','wsh','py','pyw','pl','htm','html'))){
					return __(vsprintf("不允许上传 %s 文件",array($ext)));
				}
				break;
		}
		return null;
	}

	public function download(File $file){
		return $this->downloadCopyFile($file);
	}

	public function downloadCopyFile(File $file){
		$path = $file->file_path;
		$name = $file->file_name;
		$file_path = chop($this->upload_path.DIRECTORY_SEPARATOR.$path);
		if(!empty($file_path)){
			if(!file_exists($file_path)){
				return 'Sorry, the file '.$name.' can\'t be found!';
			}
			$file_size = filesize($file_path);
			ob_end_clean();
			header('Pragma:   no-cache');
			header('Cache-Control:   private');
			header('Content-Encoding:   none');
			//header("Content-type: application/octet-stream");
			header("Content-type: application/force-download");

			header("Accept-Ranges: bytes");
			header("Accept-Length: $file_size");
			header("Content-Disposition: attachment; filename=".$name);
			$fp = fopen($file_path,"r");
			$buffer_size = 1024;

			while($buffer = fread($fp,$buffer_size)){
				echo $buffer;
			}
			fclose($fp);
			flush();
			return '';
		}
	}

	public function get_uploaded_filenames(){
		return $this->uploaded_filenames;
	}
}
?>
