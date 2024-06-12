<?php

use function yangzie\__;

/**
 * 检查功能权限的hook，根据传入的参数对权限进行检查，如果有权限，则不做任何处理
 * 如果没有权限则抛出异常，hook传入Check_User_Permission对象
 */
class Check_User_Permission{
    /**
     * uibuilder功能使用权限检查
     */
    const FUNCTION_UIBUILDER = 'uibuilder';
    /**
     * dbworkbench功能使用权限检查
     */
    const FUNCTION_DBWORKBENCH = 'dbworkbench';
    /**
     * apimanage功能使用权限检查
     */
    const FUNCTION_APIMANAGE = 'apimanage';
    /**
     * uidiscuss功能使用权限检查
     */
    const FUNCTION_UIDISCUSS = 'uidiscuss';
    /**
     * 邀请成员数量检查，设置user和project
     */
    const LIMIT_INVITE_MEMBER = 'invite_member';
    /**
     * 添加项目数量检查, 设置user
     */
    const LIMIT_ADD_PROJECT = 'add_project';
    /**
     * 每个页面的版本数量，设置user和page
     */
    const LIMIT_PAGE_VERSION = 'page_version';
    public $check_type;
    /**
     * @var \app\user\User_Model
     */
    public $user;
    /**
     * @var \app\project\Project_Model
     */
    public $project;
    /**
     * @var \app\project\Page_Model
     */
    public $page;
}
define('CHECK_USER_PERMISSION', 'CHECK_USER_PERMISSION');

function user_permission(){
    return [
        'individual'=>[
            'desc'=>__('individual can not invite other user join your project'),
            'items'=> [
                'base'=>[
                    'desc'=>'just can add one project and can not invite other user to join your project',
                    'price_info'=>'Free',
                    'price'=>0,
                    'product'=>[
                        "UIBuilder"=>'y',
//                        "DBWorkbench"=>'y',
//                        "APIManager"=>'y',
//                        "UIDiscuss"=>'n',
                    ],
                    'limit'=>[
                        "Project Limit"=>3,
                        "Invite Member"=>'n',
                        "Page History"=>'20'
                    ]
                ],
                'premium'=>[
                    'desc'=>'no project limit but can not invite other user to join your project',
                    'price_info'=>'Coming Soon',
                    'price'=>50,
                    'product'=>[
                        "UIBuilder"=>'y',
//                        "DBWorkbench"=>'y',
//                        "APIManager"=>'y',
//                        "UIDiscuss"=>'n',
                    ],
                    'limit'=>[
                        "Project Limit" =>'-',
                        "Invite Member" =>'n',
                        "Page History" =>'100'
                    ]
                ]
            ]
        ],
        'team'=>[
            'desc'=>__('can invite other user join your project'),
            'items'=> [
                'base'=>[
                    'desc'=>'small team',
                    'price_info'=>'Coming Soon',
                    'price'=>150,
                    'product'=>[
                        "UIBuilder"=>'y',
//                        "DBWorkbench"=>'y',
//                        "APIManager"=>'y',
//                        "UIDiscuss"=>'y'
                    ],
                    'limit'=>[
                        "Project Limit" =>'-',
                        "Invite Member" =>'3',
                        "Page History" =>'1000'
                    ]
                ],
                'advance'=>[
                    'desc'=>'company',
                    'price_info'=>'Coming Soon',
                    'price'=>200,
                    'product'=>[
                        "UIBuilder"=>'y',
//                        "DBWorkbench"=>'y',
//                        "APIManager"=>'y',
//                        "UIDiscuss"=>'y'
                    ],
                    'limit'=>[
                        "Project Limit" =>'-',
                        "Invite Member" =>'5',
                        "Page History" =>'-'
                    ]
                ],
                'professional'=>[
                    'desc'=>'no project limit, no member limit',
                    'price_info'=>'Coming Soon',
                    'price'=>500,
                    'product'=>[
                        "UIBuilder"=>'y',
//                        "DBWorkbench"=>'y',
//                        "APIManager"=>'y',
//                        "UIDiscuss"=>'y'
                    ],
                    'limit'=>[
                        "Project Limit"=>'-',
                        "Invite Member"=>'-',
                        "Page History"=>'-'
                    ]
                ]
            ]
        ]
    ];
}

\yangzie\YZE_Hook::add_hook(CHECK_USER_PERMISSION, function(Check_User_Permission $permission) {
    if ($permission->user && $permission->user->hasAccountOverDue()) throw new \yangzie\YZE_FatalException(__('Your account has expired. Please renew it'));

    $setting = user_permission();
    switch ($permission->check_type){
        case Check_User_Permission::FUNCTION_APIMANAGE:
        case Check_User_Permission::FUNCTION_DBWORKBENCH:
        case Check_User_Permission::FUNCTION_UIBUILDER:
        case Check_User_Permission::FUNCTION_UIDISCUSS:
            return;
        case Check_User_Permission::LIMIT_ADD_PROJECT:
            if (!$permission->user) throw new \yangzie\YZE_FatalException(__('Cannot add new project. The limit has been exceeded'));
            $userSetting = $permission->user->get_account_setting();
            $userProjectLimit = $userSetting['limit']['Project Limit'];
            $configProjectLimit = $setting[$permission->user->user_type]['items'][$permission->user->account_type]['limit']['Project Limit'];
            $userProjectLimit = $userProjectLimit == '-' ? -1 : intval($userProjectLimit);
            $configProjectLimit = $configProjectLimit == '-' ? -1 : intval($configProjectLimit);
            $limit = $userProjectLimit?:$configProjectLimit;
            if ($limit==-1) return;
            // 添加项目前检查，所以这里 + 1
            if ($permission->user->getProjectCount() + 1 > $limit) throw new \yangzie\YZE_FatalException(__('Cannot add new project. The limit has been exceeded'));
            return;
        case Check_User_Permission::LIMIT_INVITE_MEMBER:
            if (!$permission->user) throw new \yangzie\YZE_FatalException(__('Cannot invite project member. The limit has been exceeded'));
            $userSetting = $permission->user->get_account_setting();
            $inviteLimit = $userSetting['limit']['Invite Member'];
            $configInviteLimit = $setting[$permission->user->user_type]['items'][$permission->user->account_type]['limit']['Invite Member'];
            $inviteLimit = $inviteLimit == '-' ? -1 : intval($inviteLimit);
            $configInviteLimit = $configInviteLimit == '-' ? -1 : intval($configInviteLimit);
            $limit = $inviteLimit?:$configInviteLimit;
            if ($limit == -1) return;
            // 邀请前检查，所以这里 + 1
            if ($permission->project->get_member_count()+1 > $limit) throw new \yangzie\YZE_FatalException(__('Cannot invite project member. The limit has been exceeded'));
            return;
    }
});
