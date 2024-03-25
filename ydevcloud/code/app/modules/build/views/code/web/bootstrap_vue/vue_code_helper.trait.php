<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\project\Page_Model;
use function yangzie\__;

/**
 * 该trait是对Vue_Code_Fragment的封装，把Vue_Code_Fragment中的接口通过trait引入UI View中
 */
trait Vue_Code_Helper {
    /*
     * @var Vue_Code_Fragment
     */
    private $fragment;

    /**
     * @return Vue_Code_Fragment
     */
    public function get_code_fragment():Base_Code_Fragment{
        if (!$this->fragment) $this->fragment = new Vue_Code_Fragment();
        return $this->fragment;
    }

}
