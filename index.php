<?php
session_start();

// --- セッションリセット処理 ---
if (isset($_GET['reset'])) {
    $_SESSION['chat'] = [];
    header("Location: index.php");
    exit;
}

// --- 入力取得 ---
$input = $_POST['message'] ?? '';
$reply = '';

// --- カテゴリ分類ロジック ---
function categorize($input) {
    $input = mb_strtolower($input);
    if (preg_match('/(こんにちは|やあ|おはよう|こんばんは)/u', $input)) {
        return 'greeting';
    } elseif (preg_match('/(あなたは|誰|名前)/u', $input)) {
        return 'identity';
    } elseif (preg_match('/(ありがとう|助かった)/u', $input)) {
        return 'thanks';
    } elseif (preg_match('/(さようなら|ばいばい)/u', $input)) {
        return 'goodbye';
    } elseif (preg_match('/(疲れた|元気|調子)/u', $input)) {
        return 'feeling';
    } elseif (preg_match('/\?$/u', $input)) {
        return 'question';
    } else {
        return 'other';
    }
}

// --- 応答生成ロジック ---
function generateReply($input, $context) {
    $category = categorize($input);
    switch ($category) {
        case 'greeting':
            return "こんにちは！今日はどんな話をしましょうか？";
        case 'identity':
            return "私はPHPだけで作られたチャットボットですよ。";
        case 'thanks':
            return "どういたしまして！また何かあればどうぞ！";
        case 'goodbye':
            return "お話しできて楽しかったです。またね！";
        case 'feeling':
            return "気分の波ってありますよね。無理せずいきましょう。";
        case 'question':
            return "それは難しい質問ですね…。ちょっと考えてみます。";
        default:
            if (str_contains($context, 'こんにちは')) {
                return "最近どうですか？元気にしていますか？";
            } elseif (str_contains($context, '調子')) {
                return "無理せずいきましょうね。";
            } else {
                return "「{$input}」について、もう少し詳しく教えてもらえますか？";
            }
    }
}

// --- 応答処理 ---
if ($input !== '') {
    $last_bot_reply = $_SESSION['chat'][count($_SESSION['chat']) - 1]['bot'] ?? '';
    $reply = generateReply($input, $last_bot_reply);
    $_SESSION['chat'][] = ['user' => $input, 'bot' => $reply];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>PHPチャットボット</title>
</head>
<body>
<h2>純PHP製チャットボット</h2>

<!-- 会話履歴 -->
<?php if (!empty($_SESSION['chat'])): ?>
    <?php foreach ($_SESSION['chat'] as $entry): ?>
        <p><strong> あなた:</strong> <?= htmlspecialchars($entry['user']) ?></p>
        <p><strong> 純正PHPボット:</strong> <?= htmlspecialchars($entry['bot']) ?></p>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 入力フォーム -->
<form method="POST">
    <input type="text" name="message" placeholder="話しかけてください" style="width:300px;" required autofocus>
    <button type="submit">送信</button>
</form>

<!-- リセットリンク -->
<p><a href="?reset=1"> 会話をリセット</a></p>
</body>
</html>
