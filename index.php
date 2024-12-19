<?php
session_start();
// DB接続
$pdo = new PDO('mysql:host=localhost;dbname=sanmokudb;charset=utf8', 'root', '');

// boardの初期化
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = array_fill(0, 9, 'ㅤ');
}

// current_player（現在のプレイヤー）の初期化
if (!isset($_SESSION['current_player'])) {
    $_SESSION['current_player'] = '';
}

// リセット処理
if (isset($_POST['reset'])) {
    $_SESSION['board'] = array_fill(0, 9, 'ㅤ');
    $_SESSION['current_player'] = '';
    $_SESSION['message'] = '';
}

// プレイヤー選択
if (isset($_POST['select_player'])) {
    $_SESSION['current_player'] = $_POST['select'];
}

// 勝敗判定関数
function judge($board) {
    $win_column = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], // 横
        [0, 3, 6], [1, 4, 7], [2, 5, 8], // 縦
        [0, 4, 8], [2, 4, 6]             // 斜め
    ];
    foreach ($win_column as $combination) {
        if (
            $board[$combination[0]] !== 'ㅤ' &&
            $board[$combination[0]] === $board[$combination[1]] &&
            $board[$combination[1]] === $board[$combination[2]]
            ) {
                return $board[$combination[0]]; // 勝者のマークを返す
            }
    }
    return null; // 勝者なし
}

// cellボタンクリック処理
if (isset($_POST['cell']) && $_SESSION['current_player'] === '〇') {
    $index = intval($_POST['cell']);
    if ($_SESSION['board'][$index] === 'ㅤ') {
        $_SESSION['board'][$index] = '〇';
        $winner = judge($_SESSION['board']);
        if ($winner) {
            $_SESSION['message'] = "プレイヤー {$winner} の勝利です！";
            $_SESSION['current_player'] = '';
            if ($winner === '〇') {
                $stmt = $pdo->prepare('
                    UPDATE game_results
                    SET circle_win = circle_win + 1, game_nam = game_nam + 1
                ');
            } elseif ($winner === '×') {
                $stmt = $pdo->prepare('
                    UPDATE game_results
                    SET cross_win = cross_win + 1, game_nam = game_nam + 1
                ');
            }
            $stmt->execute();
        } else if (!in_array('ㅤ', $_SESSION['board'])) {
            $_SESSION['message'] = "引き分けです！";
            $_SESSION['current_player'] = '';
            $stmt = $pdo->prepare('
                UPDATE game_results
                SET draw = draw + 1, game_nam = game_nam + 1
            ');
            $stmt->execute();
        } else {
            // CPの操作 (空いているセルにランダムに追加)
            $cell_null = [];
            for ($i = 0; $i < 9; $i++) {
                if ($_SESSION['board'][$i] === 'ㅤ') {
                    $cell_null[] = $i;
                }
            }
            if (!empty($cell_null)) {
                $cp = $cell_null[array_rand($cell_null)];
                $_SESSION['board'][$cp] = '×';
                $winner = judge($_SESSION['board']);
                if ($winner) {
                    $_SESSION['message'] = "プレイヤー {$winner} の勝利です！";
                    $_SESSION['current_player'] = '';
                    if ($winner === '〇') {
                        $stmt = $pdo->prepare('
                            UPDATE game_results
                            SET circle_win = circle_win + 1, game_nam = game_nam + 1
                        ');
                    } elseif ($winner === '×') {
                        $stmt = $pdo->prepare('
                            UPDATE game_results
                            SET cross_win = cross_win + 1, game_nam = game_nam + 1
                        ');
                    }
                    $stmt->execute();
                } else if (!in_array('ㅤ', $_SESSION['board'])) {
                    $_SESSION['message'] = "引き分けです！";
                    $_SESSION['current_player'] = '';
                    $stmt = $pdo->prepare('
                        UPDATE game_results
                        SET draw = draw + 1, game_nam = game_nam + 1
                    ');
                    $stmt->execute();
                }
            }
        }
    }
}

// DBからゲーム結果を取得
$result = $pdo->query('SELECT * FROM game_results')->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8">
    <link rel="stylesheet" href="css/style.css">
    <title>3目並べ</title>
</head>
<body>
    <h1>3目並べ</h1>
        <h2>対戦履歴</h2>
    <p>〇の勝利数： <?php echo $result['circle_win']; ?>ㅤㅤ
    ×の勝利数： <?php echo $result['cross_win']; ?><br>
    引き分け数： <?php echo $result['draw']; ?>ㅤㅤ
    総試合数： <?php echo $result['game_nam'];?></p>
    <?php 
    // メッセージの表示
    if (!empty($_SESSION['message'])) {
        echo "<h2>{$_SESSION['message']}</h2>";
    }
    ?>
    <form method="POST" action="">
        <div class="board">
            <?php 
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
