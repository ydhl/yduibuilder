<?php
namespace app\modules\build\views\preview;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\project\Page_Model;
use phpseclib3\Crypt\EC\BaseCurves\Base;
use function yangzie\__;

/**
 * 该trait封装了html的code的代码结构
 */
abstract class Html_Code_Fragment extends Base_Code_Fragment {
    const SECTION_BEGIN = 'begin';
    const SECTION_END = 'end';
    const SECTION_DATA_DEFINE = 'data_define';
    const SECTION_INIT = 'init';
    const SECTION_EVENT = 'event';
    /**
     * @param $pageName string 子页面名称
     * @param $pagePath string 页面加载路径
     * @param $inputData array [子页面参数名=>主页面参数名]
     * @return void
     */
    public abstract function add_subpage_module($pageName, $pagePath, $inputData=[]);
    public abstract function get_subpage_modules();
}
