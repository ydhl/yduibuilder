<?php
namespace app\vendor\ydsms;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;

/**
 * 用腾讯云发送短信验证码
 * @author leeboo
 *
 */
class Ydsms {
    /**
     *
     * 发送短信验证码
     *
     * @param $to 手机号
     * @param $templateId 提供方模板id
     * @param $param 模板中的参数
     */
    public function sendSms($to, $templateId, $tplParam) {
        return true;
    }
    public static function generateVerifyCode($number=4) {
        return rand ( 0, 9 ) . rand ( 0, 9 ) . rand ( 0, 9 ) . rand ( 0, 9 ). rand ( 0, 9 ). rand ( 0, 9 );
    }
}
