
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `activity` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `project_id` int NOT NULL,
  `project_member_id` int NOT NULL,
  `content` text COMMENT '日志内容，可包含html',
  `type` varchar(45) DEFAULT NULL COMMENT '活动类型'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目活动日志表';

--
-- 转存表中的数据 `activity`
--

INSERT INTO `activity` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `project_id`, `project_member_id`, `content`, `type`) VALUES
(1, '2024-02-19 11:40:29', '2024-02-19 03:40:29', 0, 'a091a36a-ced8-11ee-bbac-d114ec673f04', 1, 1, '创建项目 项目管理(测试)', 'dev'),
(2, '2024-02-19 11:44:28', '2024-02-19 03:44:28', 0, '2ef1efd4-ced9-11ee-bbac-d114ec673f04', 1, 1, '添加模块 项目管理', 'dev'),
(3, '2024-02-19 11:44:44', '2024-02-19 03:44:44', 0, '389d0ec4-ced9-11ee-bbac-d114ec673f04', 1, 1, '添加功能 新增项目', 'dev'),
(4, '2024-02-19 11:58:03', '2024-02-19 03:58:03', 0, '14f71ba2-cedb-11ee-bbac-d114ec673f04', 1, 1, 'Save UI, Version: 1', 'ui'),
(5, '2024-02-20 09:56:24', '2024-02-20 01:56:24', 0, '404bf7a6-cf93-11ee-89a9-2776dbe5ebe9', 1, 1, 'Save UI, Version: 1', 'ui'),
(6, '2024-02-20 09:59:07', '2024-02-20 01:59:07', 0, 'a1b40cea-cf93-11ee-89a9-2776dbe5ebe9', 1, 1, 'web bootstrap(4.6.0) html(5.0) javascript(ECMAScript 5) php yangzie(2.0.0) php(7.0.0)换成了web bootstrap(4.6.0) html(5.0) javascript(ECMAScript 5) php yangzie(2.0.0) php(7.0.0)', 'dev'),
(7, '2024-02-20 10:01:40', '2024-02-20 02:01:40', 0, 'fd0f6224-cf93-11ee-89a9-2776dbe5ebe9', 1, 1, 'web bootstrap(4.6.0) vue(3.x) typescript(4.0) php yangzie(2.0.0) php(7.0.0)换成了web bootstrap(4.6.0) vue(3.x) typescript(4.0) php yangzie(2.0.0) php(7.0.0)', 'dev'),
(8, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fe4b2-cf9d-11ee-89a9-2776dbe5ebe9', 1, 1, 'web layui(2.9.6) html(5.0) javascript(ECMAScript 5) php yangzie(2.0.0) php(7.0.0)换成了web layui(2.9.6) html(5.0) javascript(ECMAScript 5) php yangzie(2.0.0) php(7.0.0)', 'dev'),
(11, '2024-02-20 14:42:19', '2024-02-20 06:42:19', 0, '31aba4c6-cfbb-11ee-89a9-2776dbe5ebe9', 1, 1, 'Save UI, Version: 1', 'ui');

-- --------------------------------------------------------

--
-- 表的结构 `file`
--

CREATE TABLE `file` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `file_name` varchar(145) DEFAULT NULL,
  `url` varchar(545) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `upload_date` datetime DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `file`
--

INSERT INTO `file` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `file_name`, `url`, `file_size`, `upload_date`, `type`, `project_id`) VALUES
(1, '2024-02-20 09:34:21', '2024-02-20 01:34:21', 0, '2c04d464-cf90-11ee-89a9-2776dbe5ebe9', '2799200_goals_success_flag_goal_mountain_icon.png', 'project/a0915d1a-ced8-11ee-bbac-d114ec673f04/2422065d4019d8d732.png', 14406, '2024-02-20 09:34:21', 'image', 1),
(2, '2024-02-20 09:55:40', '2024-02-20 01:55:40', 0, '268b6a0e-cf93-11ee-89a9-2776dbe5ebe9', '服务平台.png', 'project/a0915d1a-ced8-11ee-bbac-d114ec673f04/2422065d4069cd8735.png', 113514, '2024-02-20 09:55:40', 'image', 1);

-- --------------------------------------------------------

--
-- 表的结构 `function`
--

CREATE TABLE `function` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `module_id` int NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `desc` varchar(245) DEFAULT NULL,
  `screen` varchar(145) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='功能';

--
-- 转存表中的数据 `function`
--

INSERT INTO `function` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `module_id`, `name`, `desc`, `screen`) VALUES
(1, '2024-02-19 11:44:44', '2024-02-19 03:44:44', 0, '389cf182-ced9-11ee-bbac-d114ec673f04', 1, '新增项目', '', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `module`
--

CREATE TABLE `module` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `project_id` int NOT NULL,
  `desc` varchar(245) DEFAULT NULL,
  `folder` varchar(45) DEFAULT NULL COMMENT '模块存储目录名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目中的模块';

--
-- 转存表中的数据 `module`
--

INSERT INTO `module` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `project_id`, `desc`, `folder`) VALUES
(1, '2024-02-19 11:44:28', '2024-02-19 03:44:28', 0, '2ef1d26a-ced9-11ee-bbac-d114ec673f04', '项目管理', 1, '', '');

-- --------------------------------------------------------

--
-- 表的结构 `option`
--

CREATE TABLE `option` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uuid` varchar(45) DEFAULT NULL,
  `option_name` varchar(45) DEFAULT NULL,
  `option_value` text,
  `is_deleted` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- 表的结构 `page`
--

CREATE TABLE `page` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) DEFAULT NULL COMMENT '页面名称',
  `config` text COMMENT '页面的组成配置文件',
  `screen` varchar(145) DEFAULT NULL COMMENT '截屏地址',
  `module_id` int DEFAULT NULL,
  `file` varchar(45) DEFAULT NULL COMMENT '页面存储文件名',
  `url` varchar(145) NOT NULL DEFAULT '',
  `is_snapshoting` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在生成预览图',
  `function_id` int DEFAULT NULL,
  `last_member_id` int NOT NULL DEFAULT '-1' COMMENT '最新的保存者',
  `last_version_id` int NOT NULL DEFAULT '-1' COMMENT '最新一个版本',
  `page_type` enum('page','popup','master','subpage','component') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'page',
  `ref_page_id` int NOT NULL DEFAULT '0' COMMENT '当前页面引用的目标页面id',
  `create_user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `is_component` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是组件',
  `component_end_kind` enum('pc','mobile') NOT NULL DEFAULT 'pc' COMMENT '跨项目共享的终端类型',
  `component_uiid` varchar(45) NOT NULL DEFAULT '' COMMENT '作为组件的根元素id',
  `create_user_is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '作者是否删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目中的页面';

--
-- 转存表中的数据 `page`
--

INSERT INTO `page` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `config`, `screen`, `module_id`, `file`, `url`, `is_snapshoting`, `function_id`, `last_member_id`, `last_version_id`, `page_type`, `ref_page_id`, `create_user_id`, `project_id`, `is_component`, `component_end_kind`, `component_uiid`, `create_user_is_deleted`) VALUES
(4, '2024-02-20 14:42:19', '2024-02-20 06:44:26', 0, 'Page10U82J', '测试页面', '{\"type\":\"Page\",\"pageType\":\"page\",\"meta\":{\"id\":\"Page10U82J\",\"isContainer\":true,\"title\":\"\\u6d4b\\u8bd5\\u9875\\u9762\"},\"items\":[{\"type\":\"Container\",\"meta\":{\"id\":\"Page10U82Jb9iCS\",\"title\":\"Container\",\"isContainer\":true,\"selector\":{\"style\":{\"display\":\"flex\"},\"css\":{\"padding\":\"p-3\"}},\"style\":{\"flex-direction\":\"row\",\"justify-content\":\"flex-start\",\"align-items\":\"center\"}},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page10U82JPuBvP\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"\\u6807\\u9898\"},\"items\":[]},{\"type\":\"Button\",\"meta\":{\"id\":\"Page10U82JrtBHg\",\"title\":\"Button\",\"isContainer\":false,\"css\":{\"margin-left\":\"ml-3\"}},\"items\":[]}]}]}', '', 1, NULL, '', 1, 1, -1, 4, 'page', 0, 1, 1, 0, 'pc', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_style`
--

CREATE TABLE `page_bind_style` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int DEFAULT NULL,
  `style_id` int NOT NULL,
  `uiid` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `page_bind_style`
--

INSERT INTO `page_bind_style` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `style_id`, `uiid`) VALUES
(3, '2024-02-20 14:42:43', '2024-02-20 06:42:43', 0, '40270586-cfbb-11ee-89a9-2776dbe5ebe9', 4, 1, 'Page10U82Jb9iCS');

-- --------------------------------------------------------

--
-- 表的结构 `page_user`
--

CREATE TABLE `page_user` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `member_id` int NOT NULL,
  `fd` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='当前打开页面的用户';

--
-- 转存表中的数据 `page_user`
--

INSERT INTO `page_user` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `member_id`, `fd`) VALUES
(16, '2024-02-20 14:42:19', '2024-02-20 06:42:19', 0, '31b4277c-cfbb-11ee-89a9-2776dbe5ebe9', 4, 1, 7);

-- --------------------------------------------------------

--
-- 表的结构 `page_version`
--

CREATE TABLE `page_version` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `project_member_id` int NOT NULL,
  `config` text COMMENT '页面的组成配置文件',
  `screen` varchar(145) DEFAULT NULL COMMENT '截屏地址',
  `index` int NOT NULL,
  `message` varchar(999) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='页面版本';

--
-- 转存表中的数据 `page_version`
--

INSERT INTO `page_version` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `project_member_id`, `config`, `screen`, `index`, `message`) VALUES
(4, '2024-02-20 14:42:19', '2024-02-20 06:42:19', 0, '31ab7a1e-cfbb-11ee-89a9-2776dbe5ebe9', 4, 1, '{\"type\":\"Page\",\"pageType\":\"page\",\"meta\":{\"id\":\"Page10U82J\",\"isContainer\":true,\"title\":\"unnamed page\"},\"items\":[]}', '/Users/ydhleeboo/workspace/ydhl/yduibuilder-opensource/github/ydevcloud/code/app/public_html/upload//screen/a0915d1a-ced8-11ee-bbac-d114ec673f04/', 1, 'init');

-- --------------------------------------------------------

--
-- 表的结构 `project`
--

CREATE TABLE `project` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `last_page_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的页面id',
  `last_function_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的功能',
  `desc` varchar(145) DEFAULT NULL COMMENT '简要描述',
  `home_page_id` int NOT NULL DEFAULT '0' COMMENT '默认主页id',
  `end_kind` enum('pc','mobile') NOT NULL DEFAULT 'pc' COMMENT '终端类型'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户的项目';

--
-- 转存表中的数据 `project`
--

INSERT INTO `project` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `last_page_id`, `last_function_id`, `desc`, `home_page_id`, `end_kind`) VALUES
(1, '2024-02-19 11:40:29', '2024-02-19 03:40:29', 0, 'a0915d1a-ced8-11ee-bbac-d114ec673f04', '项目管理(测试)', 4, 1, '', 0, 'pc');

-- --------------------------------------------------------

--
-- 表的结构 `project_member`
--

CREATE TABLE `project_member` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `role` enum('admin','developer','reporter','guest') NOT NULL COMMENT '角色',
  `is_creater` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是项目的创建者',
  `last_page_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的页面id',
  `last_function_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的功能',
  `is_invited` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否正在邀请'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目成员';

--
-- 转存表中的数据 `project_member`
--

INSERT INTO `project_member` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `user_id`, `project_id`, `role`, `is_creater`, `last_page_id`, `last_function_id`, `is_invited`) VALUES
(1, '2024-02-19 11:40:29', '2024-02-19 03:40:29', 0, 'a0917a20-ced8-11ee-bbac-d114ec673f04', 1, 1, 'admin', 1, 4, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `project_setting`
--

CREATE TABLE `project_setting` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `project_id` int NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目设置';

--
-- 转存表中的数据 `project_setting`
--

INSERT INTO `project_setting` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `project_id`, `name`, `value`) VALUES
(1, '2024-02-19 11:40:29', '2024-02-19 03:40:29', 0, 'a091bda0-ced8-11ee-bbac-d114ec673f04', 1, 'logo', 'user/017ccd5e-f9ba-11eb-9d8c-1ff87f946644/2421965d2cd796116e.png'),
(38, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759f5de4-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'ui', 'bootstrap'),
(39, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759f6ec4-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'ui_version', '4.6.0'),
(40, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759f7a9a-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'frontend', 'web'),
(41, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759f83dc-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'backend', 'php'),
(42, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759f8c10-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'frontend_framework', 'html'),
(43, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759f95de-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'frontend_framework_version', '5.0'),
(44, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fa1d2-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'framework', 'yangzie'),
(45, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fad58-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'framework_version', '2.0.0'),
(46, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fb83e-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'frontend_language', 'javascript'),
(47, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fc176-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'frontend_language_version', 'ECMAScript 5'),
(48, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fc9fa-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'backend_language', 'php'),
(49, '2024-02-20 11:09:28', '2024-02-20 03:09:28', 0, '759fd38c-cf9d-11ee-89a9-2776dbe5ebe9', 1, 'backend_language_version', '7.0.0');

-- --------------------------------------------------------

--
-- 表的结构 `style`
--

CREATE TABLE `style` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `class_name` varchar(45) DEFAULT NULL,
  `meta` text,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='共享的样式class';

--
-- 转存表中的数据 `style`
--

INSERT INTO `style` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `class_name`, `meta`, `project_id`) VALUES
(1, '2024-02-20 09:32:18', '2024-02-20 01:32:18', 0, 'e27de178-cf8f-11ee-89a9-2776dbe5ebe9', 'container', '{\"style\":{\"display\":\"flex\"},\"css\":{\"padding\":\"p-3\"}}', 1);

-- --------------------------------------------------------

--
-- 表的结构 `uicomponent_instance`
--

CREATE TABLE `uicomponent_instance` (
  `id` int NOT NULL COMMENT 'Ui 组件实例',
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL COMMENT '实例页面',
  `uicomponent_page_id` int NOT NULL COMMENT '组件',
  `instance_uuid` varchar(45) NOT NULL COMMENT '实例uiid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `openid` varchar(45) DEFAULT NULL,
  `fromsite` varchar(145) DEFAULT NULL,
  `avatar` varchar(145) DEFAULT NULL,
  `nickname` varchar(45) DEFAULT NULL,
  `cellphone` varchar(45) NOT NULL DEFAULT '',
  `sso_token` varchar(45) DEFAULT NULL,
  `sso_token_expire` datetime DEFAULT NULL,
  `phone_region` varchar(45) NOT NULL DEFAULT '',
  `email` varchar(45) NOT NULL DEFAULT '',
  `login_pwd` varchar(45) NOT NULL DEFAULT '',
  `user_type` varchar(45) NOT NULL DEFAULT 'individual',
  `account_duedate` date DEFAULT NULL COMMENT '到期时间，null表示不过期',
  `account_setting` varchar(1000) DEFAULT NULL COMMENT '账户设置',
  `account_type` varchar(45) NOT NULL DEFAULT 'base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `openid`, `fromsite`, `avatar`, `nickname`, `cellphone`, `sso_token`, `sso_token_expire`, `phone_region`, `email`, `login_pwd`, `user_type`, `account_duedate`, `account_setting`, `account_type`) VALUES
(1, '2021-08-10 17:04:44', '2024-02-19 02:33:17', 0, '017ccd5e-f9ba-11eb-9d8c-1ff87f946644', '', '', 'http://ydecloud-os.local.com/upload/user/017ccd5e-f9ba-11eb-9d8c-1ff87f946644/2421965d303a44c986.png', 'YDUIBuilder.', '0851-83832371', NULL, NULL, '86', 'leeboo@yidianhulian.com', '25d55ad283aa400af464c76d713c07ad', 'team', NULL, NULL, 'base');

--
-- 转储表的索引
--

--
-- 表的索引 `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_project1_idx` (`project_id`),
  ADD KEY `fk_activity_project_member1_idx` (`project_member_id`);

--
-- 表的索引 `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_file_project1_idx` (`project_id`);

--
-- 表的索引 `function`
--
ALTER TABLE `function`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_function_module1_idx` (`module_id`);

--
-- 表的索引 `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_module_project_idx` (`project_id`);

--
-- 表的索引 `option`
--
ALTER TABLE `option`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_module1_idx` (`module_id`),
  ADD KEY `fk_page_function1_idx` (`function_id`),
  ADD KEY `fk_page_project1_idx` (`project_id`),
  ADD KEY `fk_page_user1` (`create_user_id`);

--
-- 表的索引 `page_bind_style`
--
ALTER TABLE `page_bind_style`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_style_page1_idx` (`page_id`),
  ADD KEY `fk_page_bind_style_style1_idx` (`style_id`),
  ADD KEY `uiid` (`uiid`);

--
-- 表的索引 `page_user`
--
ALTER TABLE `page_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category_page1_idx` (`page_id`),
  ADD KEY `fk_page_user_project_member1_idx` (`member_id`);

--
-- 表的索引 `page_version`
--
ALTER TABLE `page_version`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_version_page1_idx` (`page_id`),
  ADD KEY `fk_page_version_project_member1_idx` (`project_member_id`);

--
-- 表的索引 `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `project_member`
--
ALTER TABLE `project_member`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_member_user1_idx` (`user_id`),
  ADD KEY `fk_project_member_project1_idx` (`project_id`);

--
-- 表的索引 `project_setting`
--
ALTER TABLE `project_setting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_setting_project1_idx` (`project_id`);

--
-- 表的索引 `style`
--
ALTER TABLE `style`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_style_project1_idx` (`project_id`);

--
-- 表的索引 `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_uicomponent_instance_page1_idx` (`page_id`),
  ADD KEY `fk_uicomponent_instance_page2_idx` (`uicomponent_page_id`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `file`
--
ALTER TABLE `file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `function`
--
ALTER TABLE `function`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `module`
--
ALTER TABLE `module`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `option`
--
ALTER TABLE `option`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `page`
--
ALTER TABLE `page`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `page_bind_style`
--
ALTER TABLE `page_bind_style`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `page_user`
--
ALTER TABLE `page_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `page_version`
--
ALTER TABLE `page_version`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `project`
--
ALTER TABLE `project`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `project_member`
--
ALTER TABLE `project_member`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `project_setting`
--
ALTER TABLE `project_setting`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- 使用表AUTO_INCREMENT `style`
--
ALTER TABLE `style`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Ui 组件实例';

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 限制导出的表
--

--
-- 限制表 `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `fk_activity_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_activity_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `fk_file_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `function`
--
ALTER TABLE `function`
  ADD CONSTRAINT `fk_function_module1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`);

--
-- 限制表 `module`
--
ALTER TABLE `module`
  ADD CONSTRAINT `fk_module_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `fk_page_function1` FOREIGN KEY (`function_id`) REFERENCES `function` (`id`),
  ADD CONSTRAINT `fk_page_module1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`),
  ADD CONSTRAINT `fk_page_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_page_user1` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`);

--
-- 限制表 `page_bind_style`
--
ALTER TABLE `page_bind_style`
  ADD CONSTRAINT `fk_page_bind_style_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_style_style1` FOREIGN KEY (`style_id`) REFERENCES `style` (`id`);

--
-- 限制表 `page_user`
--
ALTER TABLE `page_user`
  ADD CONSTRAINT `fk_category_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_user_project_member1` FOREIGN KEY (`member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `page_version`
--
ALTER TABLE `page_version`
  ADD CONSTRAINT `fk_page_version_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_version_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `project_member`
--
ALTER TABLE `project_member`
  ADD CONSTRAINT `fk_project_member_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_project_member_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- 限制表 `project_setting`
--
ALTER TABLE `project_setting`
  ADD CONSTRAINT `fk_project_setting_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `style`
--
ALTER TABLE `style`
  ADD CONSTRAINT `fk_style_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  ADD CONSTRAINT `fk_uicomponent_instance_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_uicomponent_instance_page2` FOREIGN KEY (`uicomponent_page_id`) REFERENCES `page` (`id`);
COMMIT;

