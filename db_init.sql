-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-06-12 02:10:48
-- 服务器版本： 8.0.26
-- PHP 版本： 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `ydecloud_os`
--

-- --------------------------------------------------------

--
-- 表的结构 `action`
--

CREATE TABLE `action` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `type` enum('output','redirect','popup','call','webapi','emit','mutation','closepopup') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'output',
  `redirect` varchar(2995) NOT NULL DEFAULT '',
  `redirect_type` enum('inside','outside','unset') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'unset',
  `popup_type` enum('page','alert','unset') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'unset',
  `popupPageId` varchar(45) NOT NULL DEFAULT '',
  `call_uiid` varchar(45) DEFAULT NULL,
  `input` text,
  `output` text,
  `bind_api_id` int NOT NULL DEFAULT '0',
  `popup_page_type` enum('page','popup','unset') NOT NULL DEFAULT 'unset',
  `bind_class` varchar(45) DEFAULT NULL,
  `bind_uuid` varchar(45) DEFAULT NULL,
  `page_id` int NOT NULL,
  `emit_event_id` int DEFAULT NULL,
  `index` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `action`
--

INSERT INTO `action` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `type`, `redirect`, `redirect_type`, `popup_type`, `popupPageId`, `call_uiid`, `input`, `output`, `bind_api_id`, `popup_page_type`, `bind_class`, `bind_uuid`, `page_id`, `emit_event_id`, `index`) VALUES
(12, '2024-06-12 09:45:39', '2024-06-12 01:45:39', 0, '78889f5c-285d-11ef-800d-382e3fb0b88a', 'mutation', '', 'unset', 'unset', '', NULL, NULL, NULL, 0, 'unset', 'app\\project\\Page_Bind_Event_Model', '7886a15c-285d-11ef-800d-382e3fb0b88a', 1, NULL, 0),
(13, '2024-06-12 09:46:01', '2024-06-12 01:46:01', 0, '85d90b60-285d-11ef-800d-382e3fb0b88a', 'webapi', '', 'unset', 'unset', '', NULL, NULL, NULL, 2, 'unset', 'app\\project\\Page_Bind_Event_Model', '85d73560-285d-11ef-800d-382e3fb0b88a', 1, NULL, 0),
(14, '2024-06-12 09:47:37', '2024-06-12 01:47:37', 0, 'bef86b52-285d-11ef-800d-382e3fb0b88a', 'mutation', '', 'unset', 'unset', '', NULL, '', '', 0, 'unset', 'app\\project\\Page_Bind_Api_Action_Model', 'bef624be-285d-11ef-800d-382e3fb0b88a', 1, NULL, 0),
(15, '2024-06-12 09:47:37', '2024-06-12 01:47:37', 0, 'befc9628-285d-11ef-800d-382e3fb0b88a', 'output', '', 'unset', 'unset', '', NULL, '', '', 0, 'unset', 'app\\project\\Page_Bind_Api_Action_Model', 'bef624be-285d-11ef-800d-382e3fb0b88a', 1, NULL, 0),
(16, '2024-06-12 09:48:06', '2024-06-12 01:48:06', 0, 'd083c556-285d-11ef-800d-382e3fb0b88a', 'popup', '', 'unset', 'alert', '', NULL, '{\"expression\":{\"type\":\"connect\",\"data\":{\"fromUuid\":\"A5079D02-C441-4A5E-B946-B3129028882F\",\"scope\":\"local\",\"id\":\"8F950847-B516-493A-B2E7-048CD61A740B\",\"type\":\"string\",\"path\":\"rst.msg\",\"name\":\"msg\"}},\"desc\":\"rst.msg\"}', '', 0, 'unset', 'app\\project\\Page_Bind_Api_Action_Model', 'd080f8bc-285d-11ef-800d-382e3fb0b88a', 1, NULL, 0);

-- --------------------------------------------------------

--
-- 表的结构 `activity`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='项目活动日志表';

--
-- 转存表中的数据 `activity`
--

INSERT INTO `activity` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `project_id`, `project_member_id`, `content`, `type`) VALUES
(1, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '60ffc21c-27f8-11ef-800d-382e3fb0b88a', 1, 1, '创建项目 PC项目', 'dev'),
(2, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '032b2f5e-27fe-11ef-800d-382e3fb0b88a', 2, 2, '创建项目 Mobile项目', 'dev'),
(3, '2024-06-12 08:32:36', '2024-06-12 00:32:36', 0, '44800614-2853-11ef-800d-382e3fb0b88a', 1, 1, '添加模块 测试模块', 'dev'),
(4, '2024-06-12 08:32:52', '2024-06-12 00:32:52', 0, '4df8044e-2853-11ef-800d-382e3fb0b88a', 1, 1, '添加功能 测试功能', 'dev'),
(5, '2024-06-12 08:34:42', '2024-06-12 00:34:42', 0, '8f3de6ee-2853-11ef-800d-382e3fb0b88a', 1, 1, '保存UI，版本号：1', 'ui');

-- --------------------------------------------------------

--
-- 表的结构 `api_folder`
--

CREATE TABLE `api_folder` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL COMMENT '目录名',
  `api_folder_id` int DEFAULT NULL COMMENT '上级目录',
  `comment` varchar(145) DEFAULT NULL COMMENT '备注',
  `project_id` int NOT NULL,
  `index` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='api的目录';

--
-- 转存表中的数据 `api_folder`
--

INSERT INTO `api_folder` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `api_folder_id`, `comment`, `project_id`, `index`) VALUES
(1, '2024-06-12 08:42:37', '2024-06-12 00:42:37', 0, 'aa5932a2-2854-11ef-800d-382e3fb0b88a', '测试api', NULL, '', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `code`
--

CREATE TABLE `code` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `target` varchar(45) DEFAULT NULL,
  `code` varchar(45) DEFAULT NULL,
  `expirein` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `file`
--

INSERT INTO `file` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `file_name`, `url`, `file_size`, `upload_date`, `type`, `project_id`) VALUES
(1, '2024-06-12 10:02:33', '2024-06-12 02:02:33', 0, 'd53ced1e-285f-11ef-800d-382e3fb0b88a', '阅读.png', 'project/60ed69c8-27f8-11ef-800d-382e3fb0b88a/24612666901b998b50.png', 166229, '2024-06-12 10:02:33', 'image', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='功能';

--
-- 转存表中的数据 `function`
--

INSERT INTO `function` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `module_id`, `name`, `desc`, `screen`) VALUES
(1, '2024-06-12 08:32:52', '2024-06-12 00:32:52', 0, '4df6377c-2853-11ef-800d-382e3fb0b88a', 1, '测试功能', '', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `label`
--

CREATE TABLE `label` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) DEFAULT NULL COMMENT '标签名',
  `pinyin` varchar(200) DEFAULT NULL,
  `pinyin_sort` varchar(45) DEFAULT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `label_target`
--

CREATE TABLE `label_target` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `label_id` int NOT NULL,
  `target_class` varchar(45) DEFAULT NULL,
  `target_id` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `log`
--

CREATE TABLE `log` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `user_name` varchar(45) DEFAULT NULL COMMENT '用户名',
  `user_id` int DEFAULT NULL COMMENT '系统中的用户id',
  `action_time` datetime DEFAULT NULL COMMENT '操作时间',
  `action_name` varchar(45) DEFAULT NULL COMMENT '操作, 如新增用户',
  `request_method` varchar(45) DEFAULT NULL COMMENT '请求的方法，如post,get',
  `request_url` text COMMENT '访问地址',
  `client_info` text COMMENT '终端信息，如浏览器，操作系统',
  `client_ip` varchar(45) DEFAULT NULL COMMENT '终端ip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `log_column`
--

CREATE TABLE `log_column` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `column` varchar(45) DEFAULT NULL COMMENT '字段名',
  `old_value` text COMMENT '原值',
  `new_value` text,
  `db_type` enum('C','R','U','D') DEFAULT 'R' COMMENT '数据库操作类型',
  `table` varchar(45) DEFAULT NULL,
  `log_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='项目中的模块';

--
-- 转存表中的数据 `module`
--

INSERT INTO `module` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `project_id`, `desc`, `folder`) VALUES
(1, '2024-06-12 08:32:36', '2024-06-12 00:32:36', 0, '447984ba-2853-11ef-800d-382e3fb0b88a', '测试模块', 1, '', 'test');

-- --------------------------------------------------------

--
-- 表的结构 `mutation`
--

CREATE TABLE `mutation` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mutation_from_uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mutation_data_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mutation_data_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mutation_data_type` varchar(45) DEFAULT NULL,
  `action_id` int NOT NULL,
  `expression` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `mutation`
--

INSERT INTO `mutation` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `mutation_from_uuid`, `mutation_data_id`, `mutation_data_name`, `mutation_data_type`, `action_id`, `expression`) VALUES
(9, '2024-06-12 09:45:53', '2024-06-12 01:45:53', 0, '81332e92-285d-11ef-800d-382e3fb0b88a', 'BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD', 'BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD', 'textVisible', 'boolean', 12, '{\"type\":\"expression\",\"data\":{\"fromUuid\":\"BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD\",\"scope\":\"page\",\"id\":\"BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD\",\"type\":\"boolean\",\"path\":\"page.textVisible\",\"name\":\"textVisible\",\"modifier\":\"not\"}}'),
(10, '2024-06-12 09:47:37', '2024-06-12 01:47:37', 0, 'befa962a-285d-11ef-800d-382e3fb0b88a', 'A2D2776B-F50A-4E19-A105-1647EA172795', 'A2D2776B-F50A-4E19-A105-1647EA172795', 'userVisible', 'boolean', 14, '{\"type\":\"literal\",\"literal\":\"true\"}');

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
  `page_type` enum('page','popup','master','subpage','component') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'page',
  `ref_page_id` int NOT NULL DEFAULT '0' COMMENT '当前页面引用的目标页面id',
  `create_user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `is_component` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是组件',
  `component_end_kind` enum('pc','mobile') NOT NULL DEFAULT 'pc' COMMENT '跨项目共享的终端类型',
  `component_uiid` varchar(45) NOT NULL DEFAULT '' COMMENT '作为组件的根元素id',
  `create_user_is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '作者是否删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='项目中的页面';

--
-- 转存表中的数据 `page`
--

INSERT INTO `page` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `config`, `screen`, `module_id`, `file`, `url`, `is_snapshoting`, `function_id`, `last_member_id`, `last_version_id`, `page_type`, `ref_page_id`, `create_user_id`, `project_id`, `is_component`, `component_end_kind`, `component_uiid`, `create_user_is_deleted`) VALUES
(1, '2024-06-12 08:34:42', '2024-06-12 02:02:50', 0, 'Page1s6uJ1', '页面', '{\"type\":\"Page\",\"pageType\":\"page\",\"meta\":{\"id\":\"Page1s6uJ1\",\"isContainer\":true,\"title\":\"\\u9875\\u9762\",\"css\":{\"padding\":\"p-3\"}},\"items\":[{\"type\":\"Container\",\"meta\":{\"id\":\"Page1s6uJ1lD08N\",\"title\":\"Container\",\"isContainer\":true},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ1d8HXG\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"Hello World\",\"style\":{\"font-size\":\"2rem\"},\"css\":{\"foregroundTheme\":\"success\"}},\"items\":[]}]},{\"type\":\"Button\",\"meta\":{\"id\":\"Page1s6uJ1INo53\",\"title\":\"\\u70b9\\u6211\",\"isContainer\":false},\"items\":[]},{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ13YNEp\",\"title\":\"\\u88ab\\u4f60\\u53d1\\u73b0\\u4e86\",\"isContainer\":false,\"value\":\"\\u88ab\\u4f60\\u53d1\\u73b0\\u4e86\"},\"items\":[]},{\"type\":\"Hr\",\"meta\":{\"id\":\"Page1s6uJ19QHGs\",\"title\":\"Hr\",\"isContainer\":false,\"css\":{\"margin-top\":\"mt-3\",\"margin-bottom\":\"mb-3\"}},\"items\":[]},{\"type\":\"Container\",\"meta\":{\"id\":\"Page1s6uJ1GMHfO\",\"title\":\"Container\",\"isContainer\":true,\"style\":{\"display\":\"flex\",\"justify-content\":\"center\",\"align-items\":\"center\"},\"css\":{\"margin\":\"m-3\"}},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ18kSLn\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"uuid\\uff1a\"},\"items\":[]},{\"type\":\"Input\",\"meta\":{\"id\":\"Page1s6uJ1TmpmA\",\"title\":\"Input\",\"form\":{},\"isContainer\":false},\"items\":[]}]},{\"type\":\"Container\",\"placeInParent\":\"\",\"meta\":{\"id\":\"Page1s6uJ1F4XPz\",\"title\":\"Container\",\"isContainer\":true,\"style\":{\"display\":\"flex\",\"justify-content\":\"flex-start\",\"align-items\":\"center\"},\"css\":{\"margin\":\"m-3\"}},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ1fIOwL\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"gender\\uff1a\"},\"items\":[]},{\"type\":\"Select\",\"meta\":{\"id\":\"Page1s6uJ1M1D0X\",\"title\":\"Select\",\"form\":{},\"isContainer\":false,\"values\":[{\"text\":\"\\u7537\",\"value\":\"1\",\"checked\":true,\"disabled\":false},{\"text\":\"\\u5973\",\"value\":\"0\",\"checked\":false,\"disabled\":false}]},\"items\":[]}]},{\"type\":\"Button\",\"meta\":{\"id\":\"Page1s6uJ1NHc5j\",\"title\":\"\\u8c03\\u7528api\\u67e5\\u8be2\\u7528\\u6237\\u4fe1\\u606f\",\"isContainer\":false},\"items\":[]},{\"type\":\"Container\",\"meta\":{\"id\":\"Page1s6uJ1Fg67t\",\"title\":\"\\u7528\\u6237\\u4fe1\\u606f\",\"isContainer\":true},\"items\":[{\"type\":\"Container\",\"placeInParent\":\"\",\"meta\":{\"id\":\"Page1s6uJ11SPdT\",\"title\":\"Container\",\"isContainer\":true,\"style\":{\"display\":\"flex\",\"justify-content\":\"flex-start\",\"align-items\":\"center\"},\"css\":{\"margin\":\"m-3\"}},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ11befl\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"name\\uff1a\"},\"items\":[]},{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ1UF2pj\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"\\u59d3\\u540d\"},\"items\":[]}]},{\"type\":\"Container\",\"meta\":{\"id\":\"Page1s6uJ1eMhRH\",\"title\":\"Container\",\"isContainer\":true,\"style\":{\"display\":\"flex\",\"justify-content\":\"flex-start\",\"align-items\":\"center\"},\"css\":{\"margin\":\"m-3\"}},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ1V9urX\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"class\\uff1a\"},\"items\":[]},{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ1ngAR8\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"\\u73ed\\u7ea7\"},\"items\":[]}]},{\"type\":\"Container\",\"meta\":{\"id\":\"Page1s6uJ1MWxVj\",\"title\":\"Container\",\"isContainer\":true,\"style\":{\"display\":\"flex\",\"justify-content\":\"flex-start\",\"align-items\":\"center\"},\"css\":{\"margin\":\"m-3\"}},\"items\":[{\"type\":\"Text\",\"meta\":{\"id\":\"Page1s6uJ17Ppw5\",\"title\":\"Text\",\"isContainer\":false,\"value\":\"\\u8bfe\\u7a0b\\uff1a\"},\"items\":[]},{\"type\":\"List\",\"meta\":{\"id\":\"Page1s6uJ1WGSoW\",\"title\":\"List\",\"isContainer\":false},\"items\":[]}]}]}]}', '/screen/60ed69c8-27f8-11ef-800d-382e3fb0b88a/Page1s6uJ1-1.jpg', 1, NULL, '', 1, 1, -1, 1, 'page', 0, 1, 1, 0, 'pc', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_api`
--

CREATE TABLE `page_bind_api` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `web_api_id` int NOT NULL,
  `name` varchar(45) NOT NULL COMMENT 'api名称',
  `method` varchar(45) NOT NULL COMMENT 'api方法',
  `path` varchar(45) NOT NULL COMMENT '请求路径',
  `input` text,
  `output` text,
  `requestBodyType` enum('none','form-data','x-www-form-urlencoded','json','xml','raw','binary','GraphQL','msgpack') NOT NULL DEFAULT 'form-data',
  `major` tinyint NOT NULL DEFAULT '0',
  `minor` smallint NOT NULL DEFAULT '0',
  `revision` smallint NOT NULL DEFAULT '1',
  `version` int NOT NULL DEFAULT '1',
  `bind_class` varchar(45) DEFAULT NULL COMMENT '调用api的对象',
  `bind_uuid` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='绑定的接口';

--
-- 转存表中的数据 `page_bind_api`
--

INSERT INTO `page_bind_api` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `web_api_id`, `name`, `method`, `path`, `input`, `output`, `requestBodyType`, `major`, `minor`, `revision`, `version`, `bind_class`, `bind_uuid`) VALUES
(2, '2024-06-12 09:46:07', '2024-06-12 01:46:07', 0, '893b7e82-285d-11ef-800d-382e3fb0b88a', 1, 1, '用户详情', 'GET', 'student/detail/{uuid}', '{\"param\":[{\"uuid\":\"15A544FB-BE90-4FFD-929C-039CC32DF678\",\"name\":\"gender\",\"type\":\"string\",\"title\":\"性别\",\"sample\":\"1\",\"comment\":\"1或0\"}],\"path\":[{\"name\":\"uuid\",\"uuid\":\"1AD91CA5-1E0D-4DBA-9BFB-57B0C1F6940B\",\"type\":\"string\",\"title\":\"用户uuid\",\"required\":true}]}', '[{\"name\":\"成功\",\"code\":200,\"contentType\":\"JSON\",\"uuid\":\"540ED04E-105C-44FD-9468-4B917EEAD7DB\",\"body\":{\"uuid\":\"752F166E-5D8F-45F0-8657-867678BB36F4\",\"type\":\"object\",\"isRoot\":true,\"props\":[{\"uuid\":\"7D800668-2F1A-47F5-BD2F-7B0413E03FFA\",\"type\":\"boolean\",\"name\":\"success\",\"title\":\"请求是否成功\",\"defaultValue\":\"true\"},{\"uuid\":\"3E1568A8-4FF4-4C42-A7C9-090F57CAABF5\",\"type\":\"object\",\"name\":\"data\",\"title\":\"用户数据\",\"props\":[{\"uuid\":\"739F927D-770B-4995-8E17-C16E8E16DEAA\",\"type\":\"string\",\"name\":\"name\",\"title\":\"姓名\"},{\"uuid\":\"DDE349B8-2783-4BE6-8AB0-DF39FDEBAC68\",\"type\":\"string\",\"name\":\"class\",\"title\":\"班级\"},{\"uuid\":\"7E3FF564-DBE4-4999-95CE-3465B9F4AC81\",\"type\":\"array\",\"name\":\"course\",\"title\":\"课程\",\"item\":{\"uuid\":\"95D97F80-F8E1-42BD-B326-E26BC0A21600\",\"type\":\"string\",\"comment\":\"课程名\"}}]}]}},{\"code\":200,\"contentType\":\"JSON\",\"uuid\":\"8B317312-0029-439F-847D-40DB40B84F4F\",\"name\":\"错误\",\"body\":{\"uuid\":\"A5079D02-C441-4A5E-B946-B3129028882F\",\"type\":\"object\",\"isRoot\":true,\"props\":[{\"uuid\":\"CE50A60E-3F63-4191-8915-596FAB8AB5A0\",\"type\":\"boolean\",\"name\":\"success\",\"defaultValue\":\"false\",\"title\":\"是否成功标志\"},{\"uuid\":\"8F950847-B516-493A-B2E7-048CD61A740B\",\"type\":\"string\",\"name\":\"msg\",\"title\":\"错误消息\"}]}}]', 'none', 0, 0, 1, 1, 'app\\project\\Page_Bind_Event_Model', '85d73560-285d-11ef-800d-382e3fb0b88a');

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_api_action`
--

CREATE TABLE `page_bind_api_action` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `from_class` varchar(45) NOT NULL COMMENT '绑定关联的对象',
  `from_uuid` varchar(45) NOT NULL,
  `output_data_id` varchar(45) DEFAULT NULL,
  `page_id` int NOT NULL,
  `mode` enum('setting','code') NOT NULL DEFAULT 'setting',
  `code` text,
  `expression` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='页面绑定的行为';

--
-- 转存表中的数据 `page_bind_api_action`
--

INSERT INTO `page_bind_api_action` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `from_class`, `from_uuid`, `output_data_id`, `page_id`, `mode`, `code`, `expression`) VALUES
(3, '2024-06-12 09:47:37', '2024-06-12 01:47:37', 0, 'bef624be-285d-11ef-800d-382e3fb0b88a', 'app\\project\\Page_Bind_Api_Model', '893b7e82-285d-11ef-800d-382e3fb0b88a', '540ED04E-105C-44FD-9468-4B917EEAD7DB', 1, 'setting', '', '{\"type\":\"expression\",\"data\":{\"fromUuid\":\"752F166E-5D8F-45F0-8657-867678BB36F4\",\"scope\":\"local\",\"id\":\"7D800668-2F1A-47F5-BD2F-7B0413E03FFA\",\"type\":\"boolean\",\"path\":\"rst.success\",\"name\":\"success\"}}'),
(4, '2024-06-12 09:48:06', '2024-06-12 01:48:06', 0, 'd080f8bc-285d-11ef-800d-382e3fb0b88a', 'app\\project\\Page_Bind_Api_Model', '893b7e82-285d-11ef-800d-382e3fb0b88a', '8B317312-0029-439F-847D-40DB40B84F4F', 1, 'setting', '', '{\"type\":\"expression\",\"data\":{\"fromUuid\":\"A5079D02-C441-4A5E-B946-B3129028882F\",\"scope\":\"local\",\"id\":\"CE50A60E-3F63-4191-8915-596FAB8AB5A0\",\"type\":\"boolean\",\"path\":\"rst.success\",\"name\":\"success\",\"modifier\":\"not\"}}');

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_data`
--

CREATE TABLE `page_bind_data` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `type` enum('string','integer','number','array','boolean','object','null','any','map') NOT NULL DEFAULT 'string',
  `name` varchar(45) DEFAULT NULL,
  `title` varchar(45) DEFAULT NULL,
  `comment` varchar(45) DEFAULT NULL,
  `content` text,
  `data_from` enum('page','path','query') NOT NULL DEFAULT 'page',
  `defaultValue` text,
  `deprecated` tinyint(1) NOT NULL DEFAULT '0',
  `nullable` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `min` int DEFAULT NULL,
  `max` int DEFAULT NULL,
  `pattern` varchar(945) DEFAULT NULL COMMENT '验证模式',
  `enumValue` text COMMENT '枚举值列表',
  `sample` varchar(45) DEFAULT NULL COMMENT '样例',
  `action` enum('ReadOnly','WriteOnly','ReadWrite') NOT NULL DEFAULT 'ReadWrite' COMMENT '动作',
  `mock` varchar(45) NOT NULL DEFAULT '',
  `initLength` tinyint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='绑定的数据';

--
-- 转存表中的数据 `page_bind_data`
--

INSERT INTO `page_bind_data` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `type`, `name`, `title`, `comment`, `content`, `data_from`, `defaultValue`, `deprecated`, `nullable`, `required`, `min`, `max`, `pattern`, `enumValue`, `sample`, `action`, `mock`, `initLength`) VALUES
(1, '2024-06-12 08:37:06', '2024-06-12 00:37:06', 0, 'BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD', 1, 'boolean', 'textVisible', '', '', 'null', 'page', 'false', 0, 0, 0, 0, 0, '', 'null', '', 'ReadWrite', '', 0),
(2, '2024-06-12 08:57:29', '2024-06-12 00:57:29', 0, 'A2D2776B-F50A-4E19-A105-1647EA172795', 1, 'boolean', 'userVisible', '', '', 'null', 'page', 'false', 0, 0, 0, 0, 0, '', 'null', '', 'ReadWrite', '', 0),
(3, '2024-06-12 09:01:56', '2024-06-12 01:01:56', 0, 'C881BD20-B84A-482B-9679-96DA4B2AA0D5', 1, 'object', 'query', '', '查询数据', '[{\"type\":\"string\",\"uuid\":\"AD71DADE-0BA3-4008-80C9-CFF6FF64FE10\",\"name\":\"uuid\",\"in\":\"Page1s6uJ1TmpmA\",\"out\":null,\"bound\":null},{\"type\":\"integer\",\"uuid\":\"F39ABDC4-4A31-474B-8A20-E676324DE3F6\",\"name\":\"gender\",\"in\":\"Page1s6uJ1M1D0X\",\"out\":null,\"title\":\"性别\"}]', 'page', '', 0, 0, 0, 0, 0, '', 'null', '', 'ReadWrite', '', 0),
(4, '2024-06-12 09:05:17', '2024-06-12 01:05:17', 0, 'BAE26055-03CF-40B2-A004-B7C33BE641DC', 1, 'object', 'userDetail', '查询结果', '', '[{\"type\":\"boolean\",\"uuid\":\"940D59B8-7A76-45BB-A1F1-9DEE140D1758\",\"name\":\"success\",\"in\":null,\"out\":null},{\"type\":\"object\",\"uuid\":\"5C6C7561-D8FD-40B4-BD74-9A8BB20062F5\",\"props\":[{\"type\":\"string\",\"uuid\":\"8EF34106-EEF6-4A97-9BFD-DC4A11A9EF6F\",\"name\":\"name\",\"in\":null,\"out\":{\"Page1s6uJ1UF2pj\":\"TEXT\"},\"bound\":{\"Page1s6uJ1UF2pj\":\"none\"}},{\"type\":\"string\",\"uuid\":\"45697E6F-FC4D-400E-8CEF-70057A02158E\",\"name\":\"class\",\"in\":null,\"out\":{\"Page1s6uJ1ngAR8\":\"TEXT\"},\"bound\":{\"Page1s6uJ1ngAR8\":\"none\"}},{\"type\":\"array\",\"uuid\":\"066A2F48-E0FB-49EB-A7C0-EBA0363837AC\",\"name\":\"course\",\"item\":{\"type\":\"string\",\"uuid\":\"0CEDB2A1-D4EC-41F0-93F3-40F47BA3E8A9\",\"in\":null,\"out\":null},\"in\":null,\"out\":{\"Page1s6uJ1WGSoW\":\"VALUELIST\"},\"bound\":{\"Page1s6uJ1WGSoW\":\"none\"}}],\"name\":\"data\",\"in\":null,\"out\":null}]', 'page', '', 0, 0, 0, 0, 0, '', 'null', '', 'ReadWrite', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_event`
--

CREATE TABLE `page_bind_event` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `event` varchar(45) NOT NULL COMMENT '绑定的事件',
  `uiid` varchar(445) DEFAULT NULL COMMENT '绑定的uiid',
  `uicomponent_event_id` int DEFAULT NULL,
  `desc` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='页面事件绑定';

--
-- 转存表中的数据 `page_bind_event`
--

INSERT INTO `page_bind_event` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `event`, `uiid`, `uicomponent_event_id`, `desc`) VALUES
(3, '2024-06-12 09:45:39', '2024-06-12 01:45:39', 0, '7886a15c-285d-11ef-800d-382e3fb0b88a', 1, 'onClick', 'Page1s6uJ1INo53', NULL, ''),
(4, '2024-06-12 09:46:01', '2024-06-12 01:46:01', 0, '85d73560-285d-11ef-800d-382e3fb0b88a', 1, 'onClick', 'Page1s6uJ1NHc5j', NULL, '');

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_io`
--

CREATE TABLE `page_bind_io` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `type` enum('in','out') NOT NULL DEFAULT 'in',
  `data_id` varchar(45) NOT NULL,
  `uiid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `from_class` varchar(45) NOT NULL,
  `from_uuid` varchar(45) NOT NULL,
  `output_as` varchar(45) DEFAULT NULL COMMENT '输出绑定到ui到哪个属性（label，value）上',
  `bound_as` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `page_bind_io`
--

INSERT INTO `page_bind_io` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `type`, `data_id`, `uiid`, `from_class`, `from_uuid`, `output_as`, `bound_as`) VALUES
(6, '2024-06-12 09:44:43', '2024-06-12 01:44:43', 0, '57713586-285d-11ef-800d-382e3fb0b88a', 1, 'in', 'AD71DADE-0BA3-4008-80C9-CFF6FF64FE10', 'Page1s6uJ1TmpmA', 'app\\project\\Page_Bind_Data_Model', 'C881BD20-B84A-482B-9679-96DA4B2AA0D5', '', 'none'),
(7, '2024-06-12 09:44:46', '2024-06-12 01:44:46', 0, '595a1ed0-285d-11ef-800d-382e3fb0b88a', 1, 'in', 'F39ABDC4-4A31-474B-8A20-E676324DE3F6', 'Page1s6uJ1M1D0X', 'app\\project\\Page_Bind_Data_Model', 'C881BD20-B84A-482B-9679-96DA4B2AA0D5', '', 'none'),
(8, '2024-06-12 09:44:53', '2024-06-12 01:44:53', 0, '5d5723ac-285d-11ef-800d-382e3fb0b88a', 1, 'out', '8EF34106-EEF6-4A97-9BFD-DC4A11A9EF6F', 'Page1s6uJ1UF2pj', 'app\\project\\Page_Bind_Data_Model', 'BAE26055-03CF-40B2-A004-B7C33BE641DC', 'TEXT', 'none'),
(9, '2024-06-12 09:44:57', '2024-06-12 01:44:57', 0, '5fc54a7e-285d-11ef-800d-382e3fb0b88a', 1, 'out', '45697E6F-FC4D-400E-8CEF-70057A02158E', 'Page1s6uJ1ngAR8', 'app\\project\\Page_Bind_Data_Model', 'BAE26055-03CF-40B2-A004-B7C33BE641DC', 'TEXT', 'none'),
(10, '2024-06-12 09:45:01', '2024-06-12 01:45:01', 0, '62122964-285d-11ef-800d-382e3fb0b88a', 1, 'out', '066A2F48-E0FB-49EB-A7C0-EBA0363837AC', 'Page1s6uJ1WGSoW', 'app\\project\\Page_Bind_Data_Model', 'BAE26055-03CF-40B2-A004-B7C33BE641DC', 'VALUELIST', 'none');

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_state`
--

CREATE TABLE `page_bind_state` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `page_id` int NOT NULL,
  `uiid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state_type` enum('activated','disabled','hidden','pseudo','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `style` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '样式内容',
  `style_id` varchar(145) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Selector',
  `expression` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `page_bind_state`
--

INSERT INTO `page_bind_state` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `uiid`, `state_type`, `state_name`, `style`, `style_id`, `expression`) VALUES
(1, '2024-06-12 08:37:38', '2024-06-12 00:37:38', 0, 'f82f20f0-2853-11ef-800d-382e3fb0b88a', 1, 'Page1s6uJ13YNEp', 'hidden', 'hidden', '{}', NULL, '{\"type\":\"expression\",\"data\":{\"fromUuid\":\"BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD\",\"scope\":\"page\",\"id\":\"BBB5C6A6-6FA2-4A35-B633-34573F0A6ADD\",\"type\":\"boolean\",\"path\":\"page.textVisible\",\"name\":\"textVisible\"}}'),
(2, '2024-06-12 09:00:13', '2024-06-12 01:00:13', 0, '201b3eb6-2857-11ef-800d-382e3fb0b88a', 1, 'Page1s6uJ1Fg67t', 'hidden', 'hidden', '{}', NULL, '{\"type\":\"expression\",\"data\":{\"fromUuid\":\"A2D2776B-F50A-4E19-A105-1647EA172795\",\"scope\":\"page\",\"id\":\"A2D2776B-F50A-4E19-A105-1647EA172795\",\"type\":\"boolean\",\"path\":\"page.userVisible\",\"name\":\"userVisible\"}}');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_variable`
--

CREATE TABLE `page_bind_variable` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `from_page_id` int NOT NULL,
  `from_class` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'data_id的来源',
  `from_uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `from_expression` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `to_page_id` int NOT NULL,
  `to_class` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'data_id的来源',
  `to_uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Root data uuid',
  `to_data_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `to_data_path` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `page_bind_variable`
--

INSERT INTO `page_bind_variable` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `from_page_id`, `from_class`, `from_uuid`, `from_expression`, `to_page_id`, `to_class`, `to_uuid`, `to_data_id`, `to_data_path`) VALUES
(4, '2024-06-12 09:46:55', '2024-06-12 01:46:55', 0, 'a603d6f4-285d-11ef-800d-382e3fb0b88a', 1, 'app\\project\\Page_Bind_Data_Model', 'C881BD20-B84A-482B-9679-96DA4B2AA0D5', '{\"type\":\"connect\",\"data\":{\"fromUuid\":\"C881BD20-B84A-482B-9679-96DA4B2AA0D5\",\"scope\":\"page\",\"id\":\"F39ABDC4-4A31-474B-8A20-E676324DE3F6\",\"type\":\"integer\",\"path\":\"page.query.gender\",\"name\":\"gender\"}}', 1, 'app\\project\\Page_Bind_Api_Model', '893b7e82-285d-11ef-800d-382e3fb0b88a', '15A544FB-BE90-4FFD-929C-039CC32DF678', 'gender'),
(5, '2024-06-12 09:47:01', '2024-06-12 01:47:01', 0, 'a97bd1e2-285d-11ef-800d-382e3fb0b88a', 1, 'app\\project\\Page_Bind_Data_Model', 'C881BD20-B84A-482B-9679-96DA4B2AA0D5', '{\"type\":\"connect\",\"data\":{\"fromUuid\":\"C881BD20-B84A-482B-9679-96DA4B2AA0D5\",\"scope\":\"page\",\"id\":\"AD71DADE-0BA3-4008-80C9-CFF6FF64FE10\",\"type\":\"string\",\"path\":\"page.query.uuid\",\"name\":\"uuid\"}}', 1, 'app\\project\\Page_Bind_Api_Model', '893b7e82-285d-11ef-800d-382e3fb0b88a', '1AD91CA5-1E0D-4DBA-9BFB-57B0C1F6940B', 'uuid'),
(6, '2024-06-12 09:47:09', '2024-06-12 01:47:09', 0, 'ae8fe59c-285d-11ef-800d-382e3fb0b88a', 1, 'app\\project\\Page_Bind_Api_Model', '893b7e82-285d-11ef-800d-382e3fb0b88a', '{\"type\":\"connect\",\"data\":{\"scope\":\"page\",\"fromUuid\":\"893b7e82-285d-11ef-800d-382e3fb0b88a\",\"path\":\"rst\",\"id\":\"752F166E-5D8F-45F0-8657-867678BB36F4\"}}', 1, 'app\\project\\Page_Bind_Data_Model', 'BAE26055-03CF-40B2-A004-B7C33BE641DC', 'BAE26055-03CF-40B2-A004-B7C33BE641DC', 'page.userDetail');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='当前打开页面的用户';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='页面版本';

--
-- 转存表中的数据 `page_version`
--

INSERT INTO `page_version` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `page_id`, `project_member_id`, `config`, `screen`, `index`, `message`) VALUES
(1, '2024-06-12 08:34:42', '2024-06-12 00:34:42', 0, '8f3ab10e-2853-11ef-800d-382e3fb0b88a', 1, 1, '{\"type\":\"Page\",\"pageType\":\"page\",\"meta\":{\"id\":\"Page1s6uJ1\",\"isContainer\":true,\"title\":\"unnamed page\"},\"items\":[]}', '/screen/60ed69c8-27f8-11ef-800d-382e3fb0b88a/Page1s6uJ1-1.jpg', 1, 'init');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户的项目';

--
-- 转存表中的数据 `project`
--

INSERT INTO `project` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `last_page_id`, `last_function_id`, `desc`, `home_page_id`, `end_kind`) VALUES
(1, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '60ed69c8-27f8-11ef-800d-382e3fb0b88a', 'PC项目', 1, 1, 'HTML + Bootstrap', 0, 'pc'),
(2, '2024-06-11 22:22:19', '2024-06-11 14:22:19', 1, '0327ac58-27fe-11ef-800d-382e3fb0b88a', 'Mobile项目', 0, 0, 'html5 + bootstrap', 0, 'mobile');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='项目成员';

--
-- 转存表中的数据 `project_member`
--

INSERT INTO `project_member` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `user_id`, `project_id`, `role`, `is_creater`, `last_page_id`, `last_function_id`, `is_invited`) VALUES
(1, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '60efea04-27f8-11ef-800d-382e3fb0b88a', 1, 1, 'admin', 1, 1, 1, 1),
(2, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '0329972a-27fe-11ef-800d-382e3fb0b88a', 1, 2, 'admin', 1, 0, 0, 1);

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
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='项目设置';

--
-- 转存表中的数据 `project_setting`
--

INSERT INTO `project_setting` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `project_id`, `name`, `value`) VALUES
(1, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '6102fb76-27f8-11ef-800d-382e3fb0b88a', 1, 'logo', ''),
(2, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '61040c6e-27f8-11ef-800d-382e3fb0b88a', 1, 'ui', 'bootstrap'),
(3, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '61053c2e-27f8-11ef-800d-382e3fb0b88a', 1, 'ui_version', '4.6.0'),
(4, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '61064f24-27f8-11ef-800d-382e3fb0b88a', 1, 'frontend', 'web'),
(5, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '61077336-27f8-11ef-800d-382e3fb0b88a', 1, 'backend', 'php'),
(6, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '61088014-27f8-11ef-800d-382e3fb0b88a', 1, 'frontend_framework', 'html'),
(7, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '6109a5a2-27f8-11ef-800d-382e3fb0b88a', 1, 'frontend_framework_version', '5.0'),
(8, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '610ac662-27f8-11ef-800d-382e3fb0b88a', 1, 'framework', 'yangzie'),
(9, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '610be22c-27f8-11ef-800d-382e3fb0b88a', 1, 'framework_version', '2.0.0'),
(10, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '610d07ce-27f8-11ef-800d-382e3fb0b88a', 1, 'frontend_language', 'javascript'),
(11, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '610e1038-27f8-11ef-800d-382e3fb0b88a', 1, 'frontend_language_version', 'ECMAScript 5'),
(12, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '610f3f08-27f8-11ef-800d-382e3fb0b88a', 1, 'backend_language', 'php'),
(13, '2024-06-11 21:42:00', '2024-06-11 13:42:00', 0, '61147fe0-27f8-11ef-800d-382e3fb0b88a', 1, 'backend_language_version', '7.0.0'),
(14, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '032cda02-27fe-11ef-800d-382e3fb0b88a', 2, 'logo', ''),
(15, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '032e1cbe-27fe-11ef-800d-382e3fb0b88a', 2, 'ui', 'bootstrap'),
(16, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '032f2abe-27fe-11ef-800d-382e3fb0b88a', 2, 'ui_version', '4.6.0'),
(17, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '033074d2-27fe-11ef-800d-382e3fb0b88a', 2, 'frontend', 'web'),
(18, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '033194a2-27fe-11ef-800d-382e3fb0b88a', 2, 'backend', 'php'),
(19, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '0332bb98-27fe-11ef-800d-382e3fb0b88a', 2, 'frontend_framework', 'html'),
(20, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '0333e3e2-27fe-11ef-800d-382e3fb0b88a', 2, 'frontend_framework_version', '5.0'),
(21, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '0334ec9c-27fe-11ef-800d-382e3fb0b88a', 2, 'framework', 'yangzie'),
(22, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '03362e04-27fe-11ef-800d-382e3fb0b88a', 2, 'framework_version', '2.0.0'),
(23, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '0337393e-27fe-11ef-800d-382e3fb0b88a', 2, 'frontend_language', 'javascript'),
(24, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '03386f70-27fe-11ef-800d-382e3fb0b88a', 2, 'frontend_language_version', 'ECMAScript 5'),
(25, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '0339875c-27fe-11ef-800d-382e3fb0b88a', 2, 'backend_language', 'php'),
(26, '2024-06-11 22:22:20', '2024-06-11 14:22:20', 0, '033ad53a-27fe-11ef-800d-382e3fb0b88a', 2, 'backend_language_version', '7.0.0'),
(27, '2024-06-11 22:22:34', '2024-06-11 14:22:34', 0, '0bcfc188-27fe-11ef-800d-382e3fb0b88a', 1, 'logo', ''),
(28, '2024-06-11 22:41:24', '2024-06-11 14:41:24', 0, 'ad422c2a-2800-11ef-800d-382e3fb0b88a', 1, 'logo', 'user/35dc97ea-27f8-11ef-800d-382e3fb0b88a/2461166686200a8ab3.png'),
(29, '2024-06-11 22:41:46', '2024-06-11 14:41:46', 0, 'ba79e0fe-2800-11ef-800d-382e3fb0b88a', 2, 'logo', 'user/35dc97ea-27f8-11ef-800d-382e3fb0b88a/2461166686220d33e7.png'),
(30, '2024-06-12 08:52:24', '2024-06-12 00:52:24', 0, '083eb9a4-2856-11ef-800d-382e3fb0b88a', 1, 'api_env', '{\"测试\":\"http:\\/\\/localhost:8080\\/test\"}');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='共享的样式class';

-- --------------------------------------------------------

--
-- 表的结构 `uicomponent_event`
--

CREATE TABLE `uicomponent_event` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `page_id` int NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '事件名',
  `args` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '输入参数',
  `desc` varchar(145) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='组件定义的事件';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `openid`, `fromsite`, `avatar`, `nickname`, `cellphone`, `sso_token`, `sso_token_expire`, `phone_region`, `email`, `login_pwd`, `user_type`, `account_duedate`, `account_setting`, `account_type`) VALUES
(1, '2024-06-11 21:40:48', '2024-06-11 13:40:48', 0, '35dc97ea-27f8-11ef-800d-382e3fb0b88a', NULL, NULL, '/logo.png', 'YDE', '', NULL, NULL, '', '', '', 'individual', NULL, NULL, 'base');

-- --------------------------------------------------------

--
-- 表的结构 `web_api`
--

CREATE TABLE `web_api` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'api名称',
  `method` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'api方法',
  `path` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '请求路径',
  `status` enum('develop','test','deprecated','released') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '状态',
  `api_folder_id` int DEFAULT NULL COMMENT '目录',
  `project_member_id` int DEFAULT NULL COMMENT '负责人',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '说明',
  `requestBodyType` enum('none','form-data','x-www-form-urlencoded','json','xml','raw','binary','GraphQL','msgpack') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'form-data',
  `project_id` int NOT NULL,
  `index` tinyint NOT NULL DEFAULT '0',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `major` tinyint NOT NULL DEFAULT '0',
  `minor` smallint NOT NULL DEFAULT '0',
  `revision` smallint NOT NULL DEFAULT '1',
  `version` int NOT NULL DEFAULT '1',
  `commit_msg` varchar(145) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `web_api`
--

INSERT INTO `web_api` (`id`, `created_on`, `modified_on`, `is_deleted`, `uuid`, `name`, `method`, `path`, `status`, `api_folder_id`, `project_member_id`, `comment`, `requestBodyType`, `project_id`, `index`, `content`, `major`, `minor`, `revision`, `version`, `commit_msg`) VALUES
(1, '2024-06-12 08:48:24', '2024-06-12 00:48:24', 0, '79871d82-2855-11ef-800d-382e3fb0b88a', '用户详情', 'GET', 'student/detail/{uuid}', 'develop', 1, 1, NULL, 'none', 1, 0, '{\"request\":{\"param\":[{\"uuid\":\"15A544FB-BE90-4FFD-929C-039CC32DF678\",\"name\":\"gender\",\"type\":\"string\",\"title\":\"性别\",\"sample\":\"1\",\"comment\":\"1或0\"},{\"uuid\":\"B0948F67-6E06-42AB-A1D7-FD6D577D519D\",\"name\":\"\",\"type\":\"string\"}],\"path\":[{\"name\":\"uuid\",\"uuid\":\"1AD91CA5-1E0D-4DBA-9BFB-57B0C1F6940B\",\"type\":\"string\",\"title\":\"用户uuid\",\"required\":true}],\"body\":[]},\"response\":[{\"name\":\"成功\",\"code\":200,\"contentType\":\"JSON\",\"uuid\":\"540ED04E-105C-44FD-9468-4B917EEAD7DB\",\"body\":{\"uuid\":\"752F166E-5D8F-45F0-8657-867678BB36F4\",\"type\":\"object\",\"isRoot\":true,\"props\":[{\"uuid\":\"7D800668-2F1A-47F5-BD2F-7B0413E03FFA\",\"type\":\"boolean\",\"name\":\"success\",\"title\":\"请求是否成功\",\"defaultValue\":\"true\"},{\"uuid\":\"3E1568A8-4FF4-4C42-A7C9-090F57CAABF5\",\"type\":\"object\",\"name\":\"data\",\"title\":\"用户数据\",\"props\":[{\"uuid\":\"739F927D-770B-4995-8E17-C16E8E16DEAA\",\"type\":\"string\",\"name\":\"name\",\"title\":\"姓名\"},{\"uuid\":\"DDE349B8-2783-4BE6-8AB0-DF39FDEBAC68\",\"type\":\"string\",\"name\":\"class\",\"title\":\"班级\"},{\"uuid\":\"7E3FF564-DBE4-4999-95CE-3465B9F4AC81\",\"type\":\"array\",\"name\":\"course\",\"title\":\"课程\",\"item\":{\"uuid\":\"95D97F80-F8E1-42BD-B326-E26BC0A21600\",\"type\":\"string\",\"comment\":\"课程名\"}}]}]}},{\"code\":200,\"contentType\":\"JSON\",\"uuid\":\"8B317312-0029-439F-847D-40DB40B84F4F\",\"name\":\"错误\",\"body\":{\"uuid\":\"A5079D02-C441-4A5E-B946-B3129028882F\",\"type\":\"object\",\"isRoot\":true,\"props\":[{\"uuid\":\"CE50A60E-3F63-4191-8915-596FAB8AB5A0\",\"type\":\"boolean\",\"name\":\"success\",\"defaultValue\":\"false\",\"title\":\"是否成功标志\"},{\"uuid\":\"8F950847-B516-493A-B2E7-048CD61A740B\",\"type\":\"string\",\"name\":\"msg\",\"title\":\"错误消息\"}]}}]}', 0, 0, 1, 1, '添加测试接口');

--
-- 转储表的索引
--

--
-- 表的索引 `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_action_page1_idx` (`page_id`),
  ADD KEY `fk_action_uicomponent_event1_idx` (`emit_event_id`),
  ADD KEY `bind_class` (`bind_class`,`bind_uuid`);

--
-- 表的索引 `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_project1_idx` (`project_id`),
  ADD KEY `fk_activity_project_member1_idx` (`project_member_id`);

--
-- 表的索引 `api_folder`
--
ALTER TABLE `api_folder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_api_folder_api_folder1_idx` (`api_folder_id`),
  ADD KEY `fk_api_folder_project1_idx` (`project_id`);

--
-- 表的索引 `code`
--
ALTER TABLE `code`
  ADD PRIMARY KEY (`id`);

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
-- 表的索引 `label`
--
ALTER TABLE `label`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_label_project1_idx` (`project_id`);

--
-- 表的索引 `label_target`
--
ALTER TABLE `label_target`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category2_label1_idx` (`label_id`);

--
-- 表的索引 `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `log_column`
--
ALTER TABLE `log_column`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_column_log1_idx` (`log_id`);

--
-- 表的索引 `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_module_project_idx` (`project_id`);

--
-- 表的索引 `mutation`
--
ALTER TABLE `mutation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mutation_action1_idx` (`action_id`);

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
-- 表的索引 `page_bind_api`
--
ALTER TABLE `page_bind_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_api_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_api_action`
--
ALTER TABLE `page_bind_api_action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_action_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_data`
--
ALTER TABLE `page_bind_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_data_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_event`
--
ALTER TABLE `page_bind_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_event_page1_idx` (`page_id`),
  ADD KEY `fk_page_bind_event_uicomponent_event1` (`uicomponent_event_id`);

--
-- 表的索引 `page_bind_io`
--
ALTER TABLE `page_bind_io`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_io_page1_idx` (`page_id`),
  ADD KEY `idx_from` (`from_uuid`,`from_class`);

--
-- 表的索引 `page_bind_state`
--
ALTER TABLE `page_bind_state`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category2_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_style`
--
ALTER TABLE `page_bind_style`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_style_page1_idx` (`page_id`),
  ADD KEY `fk_page_bind_style_style1_idx` (`style_id`),
  ADD KEY `uiid` (`uiid`);

--
-- 表的索引 `page_bind_variable`
--
ALTER TABLE `page_bind_variable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_io_page1_idx` (`from_page_id`),
  ADD KEY `idx_from` (`from_class`),
  ADD KEY `fk_page_bind_variable_page1_idx` (`to_page_id`);

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
-- 表的索引 `uicomponent_event`
--
ALTER TABLE `uicomponent_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_uicomponent_event_page1_idx` (`page_id`);

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
-- 表的索引 `web_api`
--
ALTER TABLE `web_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_web_api_api_folder1_idx` (`api_folder_id`),
  ADD KEY `fk_web_api_project_member1_idx` (`project_member_id`),
  ADD KEY `fk_web_api_project1_idx` (`project_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `action`
--
ALTER TABLE `action`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `api_folder`
--
ALTER TABLE `api_folder`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `code`
--
ALTER TABLE `code`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `file`
--
ALTER TABLE `file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `function`
--
ALTER TABLE `function`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `label`
--
ALTER TABLE `label`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `label_target`
--
ALTER TABLE `label_target`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `log`
--
ALTER TABLE `log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `log_column`
--
ALTER TABLE `log_column`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `module`
--
ALTER TABLE `module`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mutation`
--
ALTER TABLE `mutation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `option`
--
ALTER TABLE `option`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page`
--
ALTER TABLE `page`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `page_bind_api`
--
ALTER TABLE `page_bind_api`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `page_bind_api_action`
--
ALTER TABLE `page_bind_api_action`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `page_bind_data`
--
ALTER TABLE `page_bind_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `page_bind_event`
--
ALTER TABLE `page_bind_event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `page_bind_io`
--
ALTER TABLE `page_bind_io`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `page_bind_state`
--
ALTER TABLE `page_bind_state`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `page_bind_style`
--
ALTER TABLE `page_bind_style`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_variable`
--
ALTER TABLE `page_bind_variable`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `page_user`
--
ALTER TABLE `page_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_version`
--
ALTER TABLE `page_version`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `project`
--
ALTER TABLE `project`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `project_member`
--
ALTER TABLE `project_member`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `project_setting`
--
ALTER TABLE `project_setting`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- 使用表AUTO_INCREMENT `style`
--
ALTER TABLE `style`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `uicomponent_event`
--
ALTER TABLE `uicomponent_event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Ui 组件实例';

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `web_api`
--
ALTER TABLE `web_api`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 限制导出的表
--

--
-- 限制表 `action`
--
ALTER TABLE `action`
  ADD CONSTRAINT `fk_action_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_action_uicomponent_event1` FOREIGN KEY (`emit_event_id`) REFERENCES `uicomponent_event` (`id`);

--
-- 限制表 `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `fk_activity_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_activity_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `api_folder`
--
ALTER TABLE `api_folder`
  ADD CONSTRAINT `fk_api_folder_api_folder1` FOREIGN KEY (`api_folder_id`) REFERENCES `api_folder` (`id`),
  ADD CONSTRAINT `fk_api_folder_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

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
-- 限制表 `label`
--
ALTER TABLE `label`
  ADD CONSTRAINT `fk_label_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `label_target`
--
ALTER TABLE `label_target`
  ADD CONSTRAINT `fk_category2_label1` FOREIGN KEY (`label_id`) REFERENCES `label` (`id`);

--
-- 限制表 `log_column`
--
ALTER TABLE `log_column`
  ADD CONSTRAINT `fk_log_column_log1` FOREIGN KEY (`log_id`) REFERENCES `log` (`id`);

--
-- 限制表 `module`
--
ALTER TABLE `module`
  ADD CONSTRAINT `fk_module_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `mutation`
--
ALTER TABLE `mutation`
  ADD CONSTRAINT `fk_mutation_action1` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`);

--
-- 限制表 `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `fk_page_function1` FOREIGN KEY (`function_id`) REFERENCES `function` (`id`),
  ADD CONSTRAINT `fk_page_module1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`),
  ADD CONSTRAINT `fk_page_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_page_user1` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`);

--
-- 限制表 `page_bind_api`
--
ALTER TABLE `page_bind_api`
  ADD CONSTRAINT `fk_page_bind_api_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_api_action`
--
ALTER TABLE `page_bind_api_action`
  ADD CONSTRAINT `fk_page_bind_action_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_data`
--
ALTER TABLE `page_bind_data`
  ADD CONSTRAINT `fk_page_bind_data_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_event`
--
ALTER TABLE `page_bind_event`
  ADD CONSTRAINT `fk_page_bind_event_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_event_uicomponent_event1` FOREIGN KEY (`uicomponent_event_id`) REFERENCES `uicomponent_event` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- 限制表 `page_bind_io`
--
ALTER TABLE `page_bind_io`
  ADD CONSTRAINT `fk_page_bind_io_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_state`
--
ALTER TABLE `page_bind_state`
  ADD CONSTRAINT `fk_category2_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_style`
--
ALTER TABLE `page_bind_style`
  ADD CONSTRAINT `fk_page_bind_style_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_style_style1` FOREIGN KEY (`style_id`) REFERENCES `style` (`id`);

--
-- 限制表 `page_bind_variable`
--
ALTER TABLE `page_bind_variable`
  ADD CONSTRAINT `fk_page_bind_io_page10` FOREIGN KEY (`from_page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_variable_page1` FOREIGN KEY (`to_page_id`) REFERENCES `page` (`id`);

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
-- 限制表 `uicomponent_event`
--
ALTER TABLE `uicomponent_event`
  ADD CONSTRAINT `fk_uicomponent_event_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  ADD CONSTRAINT `fk_uicomponent_instance_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_uicomponent_instance_page2` FOREIGN KEY (`uicomponent_page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `web_api`
--
ALTER TABLE `web_api`
  ADD CONSTRAINT `fk_web_api_api_folder1` FOREIGN KEY (`api_folder_id`) REFERENCES `api_folder` (`id`),
  ADD CONSTRAINT `fk_web_api_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_web_api_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
