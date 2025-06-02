<?php
session_start();

// ── セッション変数の初期化 ──
if (!isset($_SESSION['chat']) || !is_array($_SESSION['chat'])) {
    $_SESSION['chat'] = [];
}
if (!isset($_SESSION['topics']) || !is_array($_SESSION['topics'])) {
    $_SESSION['topics'] = [];
}

// ── リセット機能 ──
if (isset($_GET['reset'])) {
    $_SESSION['chat'] = [];
    $_SESSION['topics'] = [];
    header("Location: index.php");
    exit;
}

// ── 入力取得 ──
$input = trim($_POST['message'] ?? '');
$reply = '';

// ── キーワード抽出関数 ──
function extractKeywords(string $text): array {
    $keywords = [
        '天気', '好き', '嫌い', '趣味',
        '映画', '音楽', 'ゲーム', '仕事',
        'PHP', 'Laravel', 'AI', '人工知能',
        '疲れ', '元気', '旅行',
    ];
    $found = [];
    $lower = mb_strtolower($text);
    foreach ($keywords as $kw) {
        if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
            $found[] = $kw;
        }
    }
    return $found;
}

// ── 応答パターン一覧 ──
function getResponsePatterns(): array {
    return [
        'greeting' => [
            "こんにちは！今日はどんな話をしましょうか？",
            "やあ！ごきげんいかがですか？",
            "おはようございます！何か気になることはありますか？",
        ],
        'identity' => [
            "私は純正 PHP で作られたチャットボットです。",
            "僕は PHP の if/preg_match だけで動いてますよ。",
            "PHP の魔力で会話しているボットです。",
        ],
        'thanks' => [
            "どういたしまして！何か他に聞きたいことはありますか？",
            "いつでもどうぞ！ほかに気になることは？",
            "お役に立ててうれしいです。また何かあれば教えてください。",
        ],
        'goodbye' => [
            "お話しできて楽しかったです。またお越しくださいね！",
            "ありがとうございました！またいつでも話しかけてください。",
            "それでは失礼します。良い一日を！",
        ],
        'feeling' => [
            "気分の波ってありますよね。無理せずいきましょう。",
            "大丈夫ですか？無理せず休憩を取ってくださいね。",
            "疲れたときは深呼吸してみると楽になりますよ。",
        ],
        'question' => [
            "それは面白い質問ですね…ちょっと考えさせてください。",
            "むむ…それについては少し調べてみますね。",
            "良い質問です！私の知識を総動員してお答えします。",
        ],
        'default' => [
            "なるほど…。もう少し詳しく教えてもらえますか？",
            "興味深いですね。「{INPUT}」について、どう思われますか？",
            "面白い話題ですね。もっと話を聞かせてください！",
        ],
    ];
}

// ── カテゴリ分類 ──
function categorize(string $input): string {
    $input_lc = mb_strtolower($input);
    if (preg_match('/\b(こんにちは|やあ|おはよう|こんばんは)\b/u', $input_lc)) {
        return 'greeting';
    }
    if (preg_match('/\b(あなたは|誰|名前)\b/u', $input_lc)) {
        return 'identity';
    }
    if (preg_match('/\b(ありがとう|助かった)\b/u', $input_lc)) {
        return 'thanks';
    }
    if (preg_match('/\b(さようなら|ばいばい)\b/u', $input_lc)) {
        return 'goodbye';
    }
    if (preg_match('/\b(疲れ|元気|調子)\b/u', $input_lc)) {
        return 'feeling';
    }
    if (preg_match('/\?$/u', $input_lc)) {
        return 'question';
    }
    return 'other';
}

// ── ファイル書き込み用ヘルパー関数 ──
function appendToLog(string $user, string $bot): void {
    date_default_timezone_set('Asia/Tokyo');
    $timestamp = date('Y-m-d H:i:s');
    $line = "[{$timestamp}] USER: {$user} → BOT: {$bot}\n";
    file_put_contents(__DIR__ . '/chat_log.txt', $line, FILE_APPEND | LOCK_EX);
}

// ── 応答生成 ──
function generateReply(string $input): string {
    $keywords = extractKeywords($input);
    foreach ($keywords as $kw) {
        if (!in_array($kw, $_SESSION['topics'], true)) {
            $_SESSION['topics'][] = $kw;
        }
    }

    $category = categorize($input);
    $patterns = getResponsePatterns();

    if ($category !== 'other' && isset($patterns[$category])) {
        $pool = $patterns[$category];
        return $pool[array_rand($pool)];
    }

    $pool = $patterns['default'];
    $template = $pool[array_rand($pool)];
    if (mb_strpos($template, '{INPUT}') !== false) {
        $template = str_replace('{INPUT}', $input, $template);
    }
    if (!empty($_SESSION['topics'])) {
        $lastTopic = end($_SESSION['topics']);
        $template .= "ちなみに、「{$lastTopic}」の話題についてはどう思いますか？";
    }
    return $template;
}

// ── “考え中…” 演出 ──
if ($input !== '') {
    echo "<!DOCTYPE html>\n<html lang=\"ja\"><head><meta charset=\"UTF-8\"><title>PHPチャットボット</title></head><body>";
    echo "<p> ボット: …考え中…</p>";
    session_write_close();
    usleep(500000);
    session_start();
}

// ── メイン処理：応答を作って、セッションとファイルに保存 ──
if ($input !== '') {
    $reply = generateReply($input);
    $_SESSION['chat'][] = ['user' => $input, 'bot' => $reply];
    appendToLog($input, $reply);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>PHP製 高機能チャットボット</title>
</head>
<body>
<h2> PHP製 ChatGPTっぽいチャットボット</h2>

<!-- ここから：うまく使うための具体的なコツ（箇条書き） -->
<div style="background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
    <p><strong>うまく使うための具体的なコツ：</strong></p>
    <ul>
        <li>まずは「こんにちは」「やあ」などの挨拶から始めるとスムーズです。</li>
        <li>文末に「？」をつけて質問すると、より適切な返答が返ってきます。</li>
        <li>「映画」「音楽」「PHP」「AI」などのキーワードを含めると、会話が深まりやすくなります。</li>
        <li>「疲れた」「元気？」など感情を含む内容を送ると共感的な返事が返ってきます。</li>
        <li>会話が行き詰まったらキーワードを明示的に入れて、トピックを切り替えてみましょう。</li>
        <li>「 会話をリセット」をクリックすると、会話履歴とトピックがクリアされます。</li>
    </ul>
</div>
<!-- ここまで：うまく使うための具体的なコツ -->

<!-- 会話履歴（セッションベース） -->
<?php if (!empty($_SESSION['chat'])): ?>
    <?php foreach ($_SESSION['chat'] as $entry): ?>
        <p><strong> あなた:</strong> <?= nl2br(htmlspecialchars($entry['user'], ENT_QUOTES)) ?></p>
        <p><strong> ボット:</strong> <?= nl2br(htmlspecialchars($entry['bot'], ENT_QUOTES)) ?></p>
        <hr>
    <?php endforeach; ?>
<?php else: ?>
    <p>はじめまして！何でも気軽に話しかけてみてください。</p>
<?php endif; ?>

<!-- 入力フォーム -->
<form method="POST" style="margin-top: 20px;">
    <input type="text" name="message" placeholder="話しかけてください" style="width: 300px;" required autofocus>
    <button type="submit">送信</button>
</form>

<!-- リセットリンク -->
<p style="margin-top: 10px;">
    <a href="?reset=1"> 会話をリセット</a>
</p>
</body>
</html>
