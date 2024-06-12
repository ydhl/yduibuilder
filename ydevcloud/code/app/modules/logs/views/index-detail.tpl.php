<?php
namespace app\logs;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_Session_Context;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Simple_View;

$log = $this->get_data('log');
include_once 'style.inc.php';
?>
<div class="log-container">
    <h2 style="padding: 5px">操作日志详情</h2>
    <div class="log-body">
        <table>
            <tr>
                <td>用户</td>
                <td><?= $log->user_name?></td>
                <td>操作时间</td>
                <td><?= $log->action_time?></td>
                <td>操作</td>
                <td><?= $log->action_name?></td>
            </tr>
            <tr>
                <td>请求方法</td>
                <td><?= $log->request_method?></td>
                <td>请求地址</td>
                <td><a href="<?= $log->request_url?>" target="_blank"><?= $log->request_url?></a></td>
                <td>客户端IP</td>
                <td><?= $log->client_ip?></td>
            </tr>
            <tr>
                <td>客户端信息</td>
                <td colspan="5"><?= $log->client_info."<br/>".$log->get_client_info()?></td>
            </tr>
        </table>
        <h3 style="padding: 5px">数据库操作记录</h3>
        <table>
            <thead>
                <tr>
                    <th>表</th>
                    <th>操作</th>
                    <th>字段</th>
                    <th>修改前</th>
                    <th>修改后</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($log->get_items() as $log_item){?>
                <tr>
                    <td><?= $log_item->table?></td>
                    <td><?= $log_item->get_db_type_string()?></td>
                    <td><?= $log_item->get_column_mean($log_item->column)?></td>
                    <td><div style="width: 200px"><?= $log_item->old_value?></div></td>
                    <td><div style="width: 200px"><?= $log_item->new_value?></div></td>
                </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
</div>
