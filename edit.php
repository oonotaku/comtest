<?php
require 'db.php'; // データベース接続
require 'header.php'; // 共通ヘッダー

// 管理者認証関数
function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// ログイン中のユーザーのIDを取得
$userId = $_SESSION['user_id'] ?? null;

// 管理者が他のユーザーを編集する場合
if (isset($_GET['id']) && isAdmin()) {
    $userId = $_GET['id'];
}

// ユーザーIDがない場合、不正なアクセスと判断
if (!$userId) {
    echo "<p style='color: red; text-align: center;'>不正なアクセスです。ログインしてください。</p>";
    require 'footer.php';
    exit;
}

// データベースから該当ユーザーの情報を取得
$sql = "SELECT * FROM registrations WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p style='color: red; text-align: center;'>データが見つかりません。</p>";
    require 'footer.php';
    exit;
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $company = htmlspecialchars($_POST['company'], ENT_QUOTES, 'UTF-8');
    $position = htmlspecialchars($_POST['position'], ENT_QUOTES, 'UTF-8');
    $memo = htmlspecialchars($_POST['memo'], ENT_QUOTES, 'UTF-8');

    // 写真アップロード処理
    $photoPath = $user['photo_path'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileNameCmps = explode(".", $_FILES['photo']['name']);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $_FILES['photo']['name']) . '.' . $fileExtension;
        $uploadFileDir = './uploaded_photos/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $photoPath = $dest_path;

            // 古い画像を削除
            if (file_exists($user['photo_path'])) {
                unlink($user['photo_path']);
            }
        } else {
            echo "<p style='color: red;'>画像の保存に失敗しました。</p>";
            require 'footer.php';
            exit;
        }
    }

    // データベース更新
    $sql = "UPDATE registrations 
            SET name = :name, company = :company, position = :position, memo = :memo, photo_path = :photo_path 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':company' => $company,
        ':position' => $position,
        ':memo' => $memo,
        ':photo_path' => $photoPath,
        ':id' => $userId
    ]);

    echo "<p style='color: green; text-align: center;'>情報が更新されました！</p>";
    echo '<p style="text-align: center;"><a href="index.php">INDEXに戻る</a></p>';
    require 'footer.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>情報編集</title>
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #ff7f50;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #ff7f50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #ff6347;
        }

        .current-photo {
            display: block;
            margin-top: 15px;
            max-width: 100%;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>情報編集</h1>

        <form method="post" action="edit.php<?php echo isset($_GET['id']) ? '?id=' . htmlspecialchars($_GET['id']) : ''; ?>" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name">名前:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="input-group">
                <label for="company">所属会社:</label>
                <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($user['company']); ?>">
            </div>
            <div class="input-group">
                <label for="position">役職:</label>
                <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($user['position']); ?>">
            </div>
            <div class="input-group">
                <label for="memo">備考:</label>
                <textarea id="memo" name="memo"><?php echo htmlspecialchars($user['memo']); ?></textarea>
            </div>
            <div class="input-group">
                <label>現在の写真:</label>
                <?php if (!empty($user['photo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($user['photo_path']); ?>" alt="現在の写真" class="current-photo">
                <?php else: ?>
                    <p>写真が登録されていません。</p>
                <?php endif; ?>
            </div>
            <div class="input-group">
                <label for="photo">写真を変更:</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
            <button type="submit">更新</button>
        </form>
        <p style="text-align: center;"><a href="index.php">← INDEXに戻る</a></p>
    </div>

    <?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
