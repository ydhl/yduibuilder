<?php
namespace app\logs;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_Session_Context;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Simple_View;

$logs = $this->get_data('logs');
$total= $this->get_data('total');
$pagesize = $this->get_data('pagesize');
$request = YZE_Request::get_instance();

include_once 'style.inc.php';
?>
<div class="log-container">
    <h2 style="padding: 5px">操作日志</h2>
    <div class="log-header">
        <form method="get">
            <table>
                <tr>
                    <td>
                        <input type="datetime-local" style="padding: 3px" value="<?= @$_GET['time_from']?>" name="time_from">
                        <input type="datetime-local" style="padding: 3px" value="<?= @$_GET['time_to']?>" name="time_to">

                        <input style="padding: 5px" type="text" value="<?= @$_GET['search']?>" name="search" placeholder="模糊搜索用户名，操作内容，客户端ip，请求地址等">

                        <input type="text" placeholder="表名" style="padding: 5px;width:50px;" value="<?= @$_GET['table']?>" name="table">
                        <input type="text" placeholder="字段" style="padding: 5px;width:50px;" value="<?= @$_GET['column']?>" name="column">
                        <select name="method" style="padding: 5px">
                            <option value="">所有</option>
                            <option value="get" <?= @$_GET['method']=="get" ? "selected" : ""?>>get</option>
                            <option value="post" <?= @$_GET['method']=="post" ? "selected" : ""?>>post</option>
                        </select>

                        <button type="submit" style="padding: 3px">查询</button>
                        <button type="button" class="log-delete" onclick="deleteConfirm()">删除日志</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div class="log-body">
        <table>
            <thead>
                <tr>
                    <th>用户</th>
                    <th>操作时间</th>
                    <th>操作</th>
                    <th>请求方法</th>
                    <th>请求地址</th>
                    <th>客户端IP</th>
                    <th>客户端信息</th>
                    <th>影响的表</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log){?>
                <tr>
                    <td><?= $log->user_name?></td>
                    <td><?= $log->action_time?></td>
                    <td><?= $log->action_name?></td>
                    <td><?= $log->request_method?></td>
                    <td><a href="<?= $log->request_url?>" target="_blank"><div class="log-text-truncate" style="width: 200px"><?= $log->request_url?></div></a></td>
                    <td><?= $log->client_ip?></td>
                    <td><div class="log-text-truncate" style="width: 100px"><?= $log->get_client_info()?></div></td>
                    <td><a href="/logs/<?= $log->uuid?>"><?= $log->get_affected_table()?></a> </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
        <?php
        $pagination = new YZE_Simple_View($request->view_path()."/pagination", ['total'=>$total,'pagesize'=>$pagesize], $this->controller);
        $pagination->output();
        ?>
    </div>
</div>
<script >
    function post(url, data) {
        var xmlhttp;
        if (window.XMLHttpRequest) {
            xmlhttp=new XMLHttpRequest();
        } else {
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                // window.location.reload()
            }
        }
        xmlhttp.open("POST",url,true);
        xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xmlhttp.send(data);
    }
    function deleteConfirm(){
        if (prompt("确定要删除当前所查询出来的所有日志吗？\r\n\r\n日志删除后不可恢复，如确认删除，请输入 确认删除")=="确认删除"){
            post("/logs/remove.json", "<?= http_build_query($_GET)?>");
        }
    }
</script>
