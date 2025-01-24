-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: mysql3104.db.sakura.ne.jp
-- 生成日時: 2025 年 1 月 24 日 16:27
-- サーバのバージョン： 8.0.40
-- PHP のバージョン: 8.2.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `takkun-da_comtest`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `room_id` int NOT NULL COMMENT 'チャットルームID',
  `sender_id` int NOT NULL COMMENT '送信者ID',
  `receiver_id` int NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'メッセージ内容',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '送信日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_requests`
--

CREATE TABLE `chat_requests` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `chat_requests`
--

INSERT INTO `chat_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(23, 12, 9, 'accepted', '2025-01-24 06:47:27');

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL COMMENT 'ユーザー１のID',
  `receiver_id` int NOT NULL COMMENT 'ユーザー２のID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `sender_id`, `receiver_id`, `created_at`) VALUES
(18, 12, 9, '2025-01-24 06:47:59');

-- --------------------------------------------------------

--
-- テーブルの構造 `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `group_id` int DEFAULT NULL COMMENT 'グループID',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fee` int NOT NULL,
  `is_public` tinyint(1) NOT NULL COMMENT '公開かグループ内限定か',
  `created_by` int NOT NULL COMMENT 'イベント作成者のID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('pending','accepted','declined') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `follows`
--

CREATE TABLE `follows` (
  `id` int NOT NULL,
  `follower_id` int NOT NULL,
  `followed_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `followed_id`, `created_at`) VALUES
(16, 9, 12, '2025-01-24 06:48:51'),
(17, 9, 11, '2025-01-24 07:06:58');

-- --------------------------------------------------------

--
-- テーブルの構造 `group_members`
--

CREATE TABLE `group_members` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('member','admin') COLLATE utf8mb4_general_ci DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `group_members`
--

INSERT INTO `group_members` (`id`, `group_id`, `user_id`, `role`, `joined_at`) VALUES
(22, 11, 12, 'admin', '2025-01-24 06:46:56'),
(23, 11, 9, 'member', '2025-01-24 06:48:00');

-- --------------------------------------------------------

--
-- テーブルの構造 `group_messages`
--

CREATE TABLE `group_messages` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `group_messages`
--

INSERT INTO `group_messages` (`id`, `group_id`, `sender_id`, `message`, `created_at`) VALUES
(9, 11, 9, 'おい！やれるのか！？', '2025-01-24 06:48:23');

-- --------------------------------------------------------

--
-- テーブルの構造 `group_requests`
--

CREATE TABLE `group_requests` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('pending','accepted','rejected','invited') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `group_requests`
--

INSERT INTO `group_requests` (`id`, `group_id`, `user_id`, `status`, `created_at`) VALUES
(20, 11, 2, 'invited', '2025-01-24 06:47:11'),
(21, 11, 9, 'accepted', '2025-01-24 06:47:11'),
(22, 11, 10, 'invited', '2025-01-24 06:47:11'),
(23, 11, 11, 'invited', '2025-01-24 06:47:11');

-- --------------------------------------------------------

--
-- テーブルの構造 `registrations`
--

CREATE TABLE `registrations` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `memo` text COLLATE utf8mb4_general_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `last_logout` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `registrations`
--

INSERT INTO `registrations` (`id`, `name`, `email`, `password`, `company`, `position`, `memo`, `photo_path`, `created_at`, `last_logout`) VALUES
(2, '大野　拓', 'taku_oono@node-bee.com', '$2y$10$VXweeDJNrIyf9DGTNvTHLOu.E6I5LEnG71yNCa4FeZ.jsvygY.oN6', 'gs', 'student', 'test', './uploaded_photos/510a67225f75123ce0766bdb1f3452e3.png', NULL, NULL),
(9, 'アントニオ猪木', 'inoki@anton', '$2y$10$UxgE42cup2qcqSVNUH/nB.r44VKvlbnr4hzv/5.yxxqEjJT2SVOZG', '新日本プロレス', '創業者', '元気があれば何でもできる！', './uploaded_photos/914742787a894ebb7a677ebb9a2f0e64.jpeg', NULL, '2025-01-24 08:06:59'),
(10, '三沢光晴', 'misawa@misawa', '$2y$10$qQfRH1eOZxbVBiUNHjgzme.NVdG1V9mnYMdaKWldfEs4pRZtxdTtm', 'noah', 'CEO', 'エルボー', './uploaded_photos/3d93f14a2943d26d049b87b35d9eaec3.jpg', NULL, NULL),
(11, '大谷翔平', 'ohtani@ohtani', '$2y$10$CumL.KkTUsNAzmO83YF3NOKHFRzEn2XGJ7dr.ufNXePWt6zwXDqYa', 'ドジャース', '二刀流', '今年もMVP', './uploaded_photos/c5f87adfa5de737457bf0d545e450080.jpeg', NULL, '2025-01-24 08:10:51'),
(12, '大野テスト', 'oono@oono', '$2y$10$MCircMiKpnbnvIbROKaytet/vyF59utVUElclTScIntW/Tat52qlO', 'oono', 'oono', 'oono', './uploaded_photos/b43abc65065cde9015ff4a924b146690.png', NULL, '2025-01-24 07:47:36');

-- --------------------------------------------------------

--
-- テーブルの構造 `tweets`
--

CREATE TABLE `tweets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `hashtags` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `original_tweet_id` int DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `tweets`
--

INSERT INTO `tweets` (`id`, `user_id`, `content`, `hashtags`, `original_tweet_id`, `image_path`, `created_at`) VALUES
(14, 12, 'げんきですかー！\r\n1月25日に、飲むぞー！！\r\nグループ「新年会！」に招待するから、待っとけー！', '', NULL, 'uploads/67933746c13fd_inoki.jpeg', '2025-01-24 06:46:30');

-- --------------------------------------------------------

--
-- テーブルの構造 `tweet_actions`
--

CREATE TABLE `tweet_actions` (
  `id` int NOT NULL,
  `tweet_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action_type` enum('like','reply','repost','bookmark','view') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `tweet_likes`
--

CREATE TABLE `tweet_likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tweet_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `tweet_replies`
--

CREATE TABLE `tweet_replies` (
  `id` int NOT NULL,
  `parent_tweet_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `tweet_replies`
--

INSERT INTO `tweet_replies` (`id`, `parent_tweet_id`, `user_id`, `content`, `created_at`) VALUES
(5, 14, 9, '俺の画像を勝手に使うなー！！', '2025-01-24 06:55:35');

-- --------------------------------------------------------

--
-- テーブルの構造 `tweet_reposts`
--

CREATE TABLE `tweet_reposts` (
  `id` int NOT NULL,
  `original_tweet_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `user_groups`
--

INSERT INTO `user_groups` (`id`, `name`, `description`, `created_by`, `created_at`, `public`) VALUES
(11, '新年会', '元気があればお酒も飲める！', 12, '2025-01-24 06:46:56', 1);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `chat_requests`
--
ALTER TABLE `chat_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- テーブルのインデックス `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- テーブルのインデックス `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `group_messages`
--
ALTER TABLE `group_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- テーブルのインデックス `group_requests`
--
ALTER TABLE `group_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `tweets`
--
ALTER TABLE `tweets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `tweet_actions`
--
ALTER TABLE `tweet_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tweet_id` (`tweet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `tweet_likes`
--
ALTER TABLE `tweet_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`tweet_id`);

--
-- テーブルのインデックス `tweet_replies`
--
ALTER TABLE `tweet_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tweet_id` (`parent_tweet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `tweet_reposts`
--
ALTER TABLE `tweet_reposts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `original_tweet_id` (`original_tweet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- テーブルの AUTO_INCREMENT `chat_requests`
--
ALTER TABLE `chat_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- テーブルの AUTO_INCREMENT `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- テーブルの AUTO_INCREMENT `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- テーブルの AUTO_INCREMENT `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- テーブルの AUTO_INCREMENT `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- テーブルの AUTO_INCREMENT `group_messages`
--
ALTER TABLE `group_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- テーブルの AUTO_INCREMENT `group_requests`
--
ALTER TABLE `group_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- テーブルの AUTO_INCREMENT `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- テーブルの AUTO_INCREMENT `tweets`
--
ALTER TABLE `tweets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- テーブルの AUTO_INCREMENT `tweet_actions`
--
ALTER TABLE `tweet_actions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `tweet_likes`
--
ALTER TABLE `tweet_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- テーブルの AUTO_INCREMENT `tweet_replies`
--
ALTER TABLE `tweet_replies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `tweet_reposts`
--
ALTER TABLE `tweet_reposts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- テーブルの AUTO_INCREMENT `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `chat_requests`
--
ALTER TABLE `chat_requests`
  ADD CONSTRAINT `chat_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `group_messages`
--
ALTER TABLE `group_messages`
  ADD CONSTRAINT `group_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `group_requests`
--
ALTER TABLE `group_requests`
  ADD CONSTRAINT `group_requests_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `tweets`
--
ALTER TABLE `tweets`
  ADD CONSTRAINT `tweets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `registrations` (`id`);

--
-- テーブルの制約 `tweet_actions`
--
ALTER TABLE `tweet_actions`
  ADD CONSTRAINT `tweet_actions_ibfk_1` FOREIGN KEY (`tweet_id`) REFERENCES `tweets` (`id`),
  ADD CONSTRAINT `tweet_actions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `registrations` (`id`);

--
-- テーブルの制約 `tweet_replies`
--
ALTER TABLE `tweet_replies`
  ADD CONSTRAINT `tweet_replies_ibfk_1` FOREIGN KEY (`parent_tweet_id`) REFERENCES `tweets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tweet_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `tweet_reposts`
--
ALTER TABLE `tweet_reposts`
  ADD CONSTRAINT `tweet_reposts_ibfk_1` FOREIGN KEY (`original_tweet_id`) REFERENCES `tweets` (`id`),
  ADD CONSTRAINT `tweet_reposts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `registrations` (`id`);

--
-- テーブルの制約 `user_groups`
--
ALTER TABLE `user_groups`
  ADD CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
