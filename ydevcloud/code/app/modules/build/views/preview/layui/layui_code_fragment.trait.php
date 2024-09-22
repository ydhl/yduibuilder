<?php
namespace app\modules\build\views\preview\layui;
use app\modules\build\views\preview\Alpinejs_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;

/**
 * 该trait封装了layui的code的代码结构，layui的特点是layui.use用法
 */
class Layui_Code_Fragment extends Alpinejs_Code_Fragment {
    static $uses = [];
    public function add_use($layuiModule) {
        if (!in_array($layuiModule, self::$uses)) {
            self::$uses[] = $layuiModule;
            $this->add_code(Html_Code_Fragment::SECTION_INIT, "const {$layuiModule} = layui.{$layuiModule};");
        }
        return $this;
    }
}
