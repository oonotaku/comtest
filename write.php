<?php
require 'db.php'; // DB接続ファイル
// session_start(); // セッション開始
require 'header.php'; // 共通ヘッダー

// フォームデータ取得
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // パスワードをハッシュ化
$company = htmlspecialchars($_POST['company'], ENT_QUOTES, 'UTF-8');
$position = htmlspecialchars($_POST['position'], ENT_QUOTES, 'UTF-8');
$memo = htmlspecialchars($_POST['memo'], ENT_QUOTES, 'UTF-8');
$photoPath = '';

// Email重複チェック
$sql = "SELECT COUNT(*) FROM registrations WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email]);
$emailExists = $stmt->fetchColumn();
if ($emailExists > 0) {
// Emailが重複している場合
echo "<p style='color: red; text-align: center;'>このEmailは既に登録されています。</p>";
echo "<p style='text-align: center;'><a href='touroku.php'>戻る</a></p>";
exit;
}

// 画像アップロード処理
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileNameCmps = explode(".", $_FILES['photo']['name']);
    $fileExtension = strtolower(end($fileNameCmps));
    $newFileName = md5(time() . $_FILES['photo']['name']) . '.' . $fileExtension;
    $uploadFileDir = './uploaded_photos/';
    $dest_path = $uploadFileDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        $photoPath = $dest_path;
    } else {
        die("画像の保存に失敗しました。");
    }
}

// データベースに挿入
try {
    $sql = "INSERT INTO registrations (name, email, password, company, position, memo, photo_path) 
            VALUES (:name, :email, :password, :company, :position, :memo, :photo_path)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $password,
        ':company' => $company,
        ':position' => $position,
        ':memo' => $memo,
        ':photo_path' => $photoPath
    ]);
    // 登録したユーザーの情報を取得
    $userId = $pdo->lastInsertId(); // 自動生成されたIDを取得

    // セッションにログイン情報を保存
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;

echo "<p style='color: green; text-align: center;'>登録が完了しました！</p>";
echo "<p style='text-align: center;'><a href='index.php'>登録一覧ページへ戻る</a></p>";
} catch (PDOException $e) {	} catch (PDOException $e) {
die("データ登録エラー: " . $e->getMessage());	die("データ登録エラー: " . $e->getMessage());
}	
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録完了</title>
</head>
<body>
    <h1>登録が完了しました！</h1>
    <!-- <nav>
        <a href="index.php">登録一覧ページへ</a>
    </nav> -->
<?php require 'footer.php'; // 共通フッター ?>
</body>
</html>
