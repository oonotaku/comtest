<?php
require 'db.php';
require 'header.php';
require 'tweet_form.php';

// ログインユーザー確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// ツイート一覧を取得
$sql = "SELECT 
            t.id, 
            t.content, 
            t.image_path, 
            t.created_at, 
            u.id AS user_id,
            u.name AS user_name, 
            u.photo_path AS user_photo, 
            t.hashtags,
            (SELECT COUNT(*) FROM tweet_likes WHERE tweet_id = t.id) AS like_count,
            (SELECT COUNT(*) FROM tweet_likes WHERE tweet_id = t.id AND user_id = :user_id) AS user_liked,
            (SELECT COUNT(*) FROM tweet_reposts WHERE original_tweet_id = t.id) AS repost_count,
            (SELECT COUNT(*) FROM tweet_reposts WHERE original_tweet_id = t.id AND user_id = :user_id) AS user_reposted,
            (SELECT COUNT(*) FROM tweet_replies WHERE parent_tweet_id = t.id) AS reply_count, -- 返信数
            (SELECT COUNT(*) FROM tweet_replies WHERE parent_tweet_id = t.id AND user_id = :user_id) AS user_replied -- ユーザーが返信済みか
        FROM tweets t
        JOIN registrations u ON t.user_id = u.id
        ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
// URLパラメータで渡されたhighlight_idを取得
$highlightId = isset($_GET['highlight_id']) ? intval($_GET['highlight_id']) : null;


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tweet一覧</title>
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
            border-bottom: 1px solid #ddd;
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
            cursor: pointer;
            margin-right: 5px;
        }
        .like-count {
            color: blue;
        }
        .reposted {
         color: green; /* リポスト済みの状態を表す色 */
        font-weight: bold;
        cursor: pointer;
        margin-right: 5px;
        }

        .repost-count {
        color: blue; /* カウント部分の色 */
        margin-left: 5px;
        }
        .tweet-container { /* スタイリング */ }
        .highlight {
    background-color: #fffbcc; /* ハイライト用の色 */
    border: 2px solid #ffd700;
}

        h1 {
            text-align: center;
            color: #ff7f50;

    </style>
    <script>
        async function toggleLike(tweetId, element) {
            const response = await fetch('toggle_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tweet_id: tweetId })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'liked') {
                    element.classList.add('liked');
                } else if (data.status === 'unliked') {
                    element.classList.remove('liked');
                }
                // 数字のみ更新
                const likeCountElement = element.nextElementSibling;
                likeCountElement.textContent = data.like_count;
            } else {
                console.error('Like更新中にエラーが発生しました');
            }
        }
        
        async function toggleRepost(tweetId, element) {
            const response = await fetch('toggle_repost.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tweet_id: tweetId })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'reposted') {
                    element.classList.add('reposted');
                } else if (data.status === 'unreposted') {
                    element.classList.remove('reposted');
                }
                // 数字だけ更新
                const repostCountElement = element.nextElementSibling;
                repostCountElement.textContent = data.repost_count;
            } else {
                console.error('Repost更新中にエラーが発生しました');
            }
        }
        async function toggleReply(tweetId) {
            const response = await fetch('toggle_reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tweet_id: tweetId })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    const replyCountElement = document.getElementById(`reply-count-${tweetId}`);
                    replyCountElement.textContent = data.reply_count;
                }
            }
        }
            // ページロード後に該当ツイートにスクロール
    document.addEventListener('DOMContentLoaded', () => {
        const highlightElement = document.querySelector('.highlight');
        if (highlightElement) {
            highlightElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });


    </script>
</head>

<body>
<div class="tweet-container">
    <h1>Tweet一覧</h1>

    <?php foreach ($tweets as $tweet): ?>
        <div class="tweet <?php echo $tweet['id'] === $highlightId ? 'highlight' : ''; ?>">
            <img 
                src="<?php echo htmlspecialchars($tweet['user_photo']); ?>" 
                alt="User Photo" 
                class="user-photo" 
                onclick="window.location.href='detail.php?id=<?php echo urlencode($tweet['user_id']); ?>'" 
                style="cursor: pointer;">
            
            <div class="tweet-content" >
                <h3 onclick="window.location.href='detail.php?id=<?php echo urlencode($tweet['user_id']); ?>'" 
                    style="cursor: pointer;">
                    <?php echo htmlspecialchars($tweet['user_name']); ?>
                </h3>
                <p onclick="window.location.href='tweet_detail.php?tweet_id=<?php echo $tweet['id']; ?>'" style="cursor: pointer;"><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>

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

                    <span onclick="toggleReply(<?php echo $tweet['id']; ?>, this)" 
                        class="<?php echo $tweet['user_replied'] ? 'replied' : ''; ?>">
                        Reply
                    </span>
                    <span class="reply-count"><?php echo htmlspecialchars($tweet['reply_count']); ?></span>
                    
                    <!-- <span>🔖 Bookmark</span>
                    <span>👀 View</span>
                    <span>📤 Share</span> -->
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
