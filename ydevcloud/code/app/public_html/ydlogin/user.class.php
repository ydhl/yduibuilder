<?php
class YDLoginUser{
    /**
     * 登录用户来自哪个网站，
     * @var string
     */
    public $fromSite;
    
    /**
     * 登录用户在来源网站的id
     * @var unknown
     */
    public $openid;
    
    /**
     * 登录用户的名字
     * @var unknown
     */
    public $displayName;
    
    /**
     * 用户头像，没有为null
     * @var unknown
     */
    public $avatar;

    /**
     * 用户在来源网站上的完整信息
     *
     * @var array
     */
    public $origData;
}