<?php
require 'db.php';
require 'header.php';

// ログインユーザー確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// クエリパラメータのtweet_idを取得
if (!isset($_GET['tweet_id'])) {
    header('Location: tweet.php');
    exit;
}
$tweetId = intval($_GET['tweet_id']);

// 該当ツイートを取得
$sql = "SELECT 
            t.id, 
            t.content, 
            t.image_path, 
            t.created_at, 
            u.name AS user_name, 
            u.photo_path AS user_photo, 
            t.hashtags,
            (SELECT COUNT(*) FROM tweet_likes WHERE tweet_id = t.id) AS like_count,
            (SELECT COUNT(*) FROM tweet_likes WHERE tweet_id = t.id AND user_id = :user_id) AS user_liked,
            (SELECT COUNT(*) FROM tweet_reposts WHERE original_tweet_id = t.id) AS repost_count,
            (SELECT COUNT(*) FROM tweet_reposts WHERE original_tweet_id = t.id AND user_id = :user_id) AS user_reposted,
            (SELECT COUNT(*) FROM tweet_replies WHERE parent_tweet_id = t.id) AS reply_count,
            (SELECT COUNT(*) FROM tweet_replies WHERE parent_tweet_id = t.id AND user_id = :user_id) AS user_replied
        FROM tweets t
        JOIN registrations u ON t.user_id = u.id
        WHERE t.id = :tweet_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId, ':tweet_id' => $tweetId]);
$tweet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tweet) {
    echo "該当ツイートが見つかりません。";
    exit;
}

// リプライを取得
$replySql = "SELECT r.content, r.created_at, u.name AS user_name, u.photo_path AS user_photo
             FROM tweet_replies r
             JOIN registrations u ON r.user_id = u.id
             WHERE r.parent_tweet_id = :parent_tweet_id
             ORDER BY r.created_at ASC";
$replyStmt = $pdo->prepare($replySql);
$replyStmt->execute([':parent_tweet_id' => $tweetId]);
$replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tweet詳細</title>
    <style>
        .tweet-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .tweet {
            padding: 15px 0;
            display: flex;
            gap: 10px;
        }
        .user-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .tweet-content {
            flex-grow: 1;
        }
        .tweet-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .tweet-actions span {
            cursor: pointer;
            color: #007bff;
        }
        .tweet-actions .liked {
            color: red;
        }
        .hashtags {
            color: #ff7f50;
        }
        .hashtags a {
            color: #ff7f50;
            text-decoration: none;
            margin-right: 5px;
        }
        .hashtags a:hover {
            text-decoration: underline;
        }
        .tweet-image {
            margin-top: 10px;
            max-width: 100%;
            border-radius: 8px;
        }
        .liked {
            color: red;
            font-weight: bold;
        }
        .reposted {
            color: green;
            font-weight: bold;
        }
        .reply-count {
            color: blue;
        }
        .back-button {
            display: block;
            margin-bottom: 20px;
            font-size: 18px;
            color: #007bff;
            text-decoration: none;
        }
        .back-button:hover {
            text-decoration: underline;
        }
        .reply-form textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
        }
        .reply-form button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .reply-form button:hover {
            background-color: #0056b3;
        }
        .replies {
            margin-top: 30px;
        }
        .reply {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .reply-content {
            flex-grow: 1;
        }
    </style>
    <script>
        async function toggleLike(tweetId, element) {
            // Like toggle処理
        }
        async function toggleRepost(tweetId, element) {
            // Repost toggle処理
        }
    </script>
</head>
<body>
    <div class="tweet-container">
        <a href="tweet.php?highlight_id=<?php echo $tweetId; ?>" class="back-button">← Back</a>
        <div class="tweet">
            <img src="<?php echo htmlspecialchars($tweet['user_photo']); ?>" alt="User Photo" class="user-photo">
            <div class="tweet-content">
                <h3><?php echo htmlspecialchars($tweet['user_name']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>

                <?php if (!empty($tweet['hashtags'])): ?>
                    <div class="hashtags">
                        <?php
                        $hashtags = explode(',', $tweet['hashtags']);
                        foreach ($hashtags as $hashtag):
                        ?>
                            <a href="search.php?tag=<?php echo urlencode($hashtag); ?>">#<?php echo htmlspecialchars($hashtag); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tweet['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($tweet['image_path']); ?>" alt="Tweet Image" class="tweet-image">
                <?php endif; ?>

                <div class="tweet-actions">
                    <span onclick="toggleLike(<?php echo $tweet['id']; ?>, this)" 
                          class="<?php echo $tweet['user_liked'] ? 'liked' : ''; ?>">
                        Like
                    </span>
                    <span class="like-count"><?php echo htmlspecialchars($tweet['like_count']); ?></span>

                    <span onclick="toggleRepost(<?php echo $tweet['id']; ?>, this)" 
                          class="<?php echo $tweet['user_reposted'] ? 'reposted' : ''; ?>">
                        Repost
                    </span>
                    <span class="repost-count"><?php echo htmlspecialchars($tweet['repost_count']); ?></span>

                    <span> Reply</span>
                    <span class="reply-count"><?php echo htmlspecialchars($tweet['reply_count']); ?></span>
                </div>
            </div>
        </div>

        <div class="reply-form">
            <h3>Post your reply</h3>
            <form method="POST" action="post_reply.php">
                <textarea name="content" rows="3" placeholder="Write your reply..." required></textarea>
                <input type="hidden" name="parent_tweet_id" value="<?php echo $tweetId; ?>">
                <button type="submit">Reply</button>
            </form>
        </div>

        <div class="replies">
            <h3>Replies</h3>
            <?php foreach ($replies as $reply): ?>
                <div class="reply">
                    <img src="<?php echo htmlspecialchars($reply['user_photo']); ?>" alt="User Photo" class="user-photo">
                    <div class="reply-content">
                        <h4><?php echo htmlspecialchars($reply['user_name']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
                        <span><?php echo htmlspecialchars($reply['created_at']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
