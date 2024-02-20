<?php

use function yangzie\__;


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
