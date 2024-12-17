<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8">
    <link rel="stylesheet" href="css/style.css">
    <title>3目並べ</title>
</head>
<body>
    <h1>3目並べ</h1>

    <?php 
    session_start();

    // boardの初期化 初期化をしないとエラー（予期せぬ動作）が出る可能性が高くなるから
    if (!isset($_SESSION['board'])) {
        $_SESSION['board'] = array_fill(0, 9, 'ㅤ');
    }
    //current_player（現在のプレイヤー）の初期化 上記と同じ
    if (!isset($_SESSION['current_player'])) {
        $_SESSION['current_player'] = '';
    }

    // リセット処理 クリックされたらセッション変数boardと現在のプレイヤー情報を初期化
    if (isset($_POST['reset'])) {
        $_SESSION['board'] = array_fill(0, 9, 'ㅤ');
        $_SESSION['current_player'] = '';
    }

    // プレイヤー選択
    if (isset($_POST['select_player'])) {
        $_SESSION['current_player'] = $_POST['select'];
    }

    // ボタンクリック処理 セルの中を選択したプレイヤーがまだセレクトになっているか　&&両方Tureの場合
    // intval 整数のみ変換される
    // indexにcellを格納するため数値に変換する
    if (isset($_POST['cell']) && $_SESSION['current_player'] !== '') {
        $index = intval($_POST['cell']);
        //もしboardの中に埋まっているものがあれば選択できないようにする
        if ($_SESSION['board'][$index] === 'ㅤ') {
            $_SESSION['board'][$index] = $_SESSION['current_player'];
        }
    }

    ?>

    <form method="POST" action="">
        <div class="board">
            <?php 
            // ボタンをクリックしたらセッションボードに変数iをセッションボードの中に入れる　９個のボタンが埋まるまで繰り返す
            for ($i = 0; $i < 9; $i++) {
                echo '<button type="submit" name="cell" value="' . $i . '">' . $_SESSION['board'][$i] . '</button>';
            }
            ?>
        </div>

        <select name="select">
            <option value="">プレイヤーを選択してください</option>
            <option value="〇" <?php echo $_SESSION['current_player'] === '〇' ? 'selected' : ''; ?>>〇</option>
            <option value="×" <?php echo $_SESSION['current_player'] === '×' ? 'selected' : ''; ?>>×</option>
        </select>
        <button type="submit" name="select_player">プレイヤー選択</button>
        <button type="submit" name="reset">リセット</button>
    </form>

</body>
</html>
