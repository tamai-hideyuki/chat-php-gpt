<?php
session_start();

// --- 1. ユーザー入力処理 ---
$input = $_POST['message'] ?? '';
$reply = '';

if ($input !== '') {
    // 応答生成（ルールベース）
    if (preg_match('/(こんにちは|やあ)/u', $input)) {
        $reply = "こんにちは！ご質問は何でしょうか？";
    } elseif (preg_match('/名前|あなた/u', $input)) {
        $reply = "私はPHPでできたChatGPT風ボットです！";
    } elseif (preg_match('/AI|人工知能/u', $input)) {
        $reply = "AIって面白いですよね。私はルールベースですが……。";
    } elseif (preg_match('/(さようなら|バイバイ)/u', $input)) {
        $reply = "お話しできて楽しかったです。またいつでも！";
    } else {
        $reply = "「{$input}」について考えてみます…（風）";
    }

    // --- 2. セッションに会話履歴を保存 ---
    $_SESSION['chat'][] = ['user' => $input, 'bot' => $reply];
}
?>

<!-- --- 3. HTML部分 --- -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>PHP ChatGPT風ボット</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f0; padding: 20px; }
        .chat-box { background: white; padding: 20px; max-width: 600px; margin: auto; border-radius: 8px; }
        .message { margin: 10px 0; }
        .user { font-weight: bold; color: #007acc; }
        .bot  { color: #333; }
        form { margin-top: 20px; display: flex; gap: 10px; }
        input[type="text"] { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 16px; border: none; background: #007acc; color: white; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<div class="chat-box">
    <h2>PHP製 ChatGPT風ボット</h2>

    <!-- --- 4. 会話表示 --- -->
    <?php if (!empty($_SESSION['chat'])): ?>
        <?php foreach ($_SESSION['chat'] as $log): ?>
            <div class="message user"> あなた: <?= htmlspecialchars($log['user']) ?></div>
            <div class="message bot"> PHP-Bot: <?= htmlspecialchars($log['bot']) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- --- 5. 入力フォーム --- -->
    <form method="POST">
        <input type="text" name="message" placeholder="何か話しかけてみてください" required autofocus>
        <button type="submit">送信</button>
    </form>
</div>
</body>
</html>
