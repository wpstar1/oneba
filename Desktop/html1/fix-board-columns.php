<?php
// board_posts 테이블에 user_id 컬럼 추가
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'api/config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>게시판 컬럼 수정</title>";
echo "<style>body{font-family:sans-serif;padding:20px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;padding:10px;background:#e8f5e9;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#ffebee;margin:10px 0;}";
echo ".info{color:blue;padding:10px;background:#e3f2fd;margin:10px 0;}</style></head><body>";

echo "<h1>게시판 테이블 컬럼 수정</h1>";

try {
    // 1. board_posts 테이블 확인
    echo "<h2>1. board_posts 테이블 확인</h2>";

    // 현재 컬럼 확인
    $columns = $pdo->query("SHOW COLUMNS FROM board_posts")->fetchAll();
    echo "<div class='info'>현재 컬럼: ";
    $columnNames = array_column($columns, 'Field');
    echo implode(', ', $columnNames);
    echo "</div>";

    // user_id 컬럼이 없으면 추가
    if (!in_array('user_id', $columnNames)) {
        echo "<p class='info'>user_id 컬럼을 추가합니다...</p>";
        $pdo->exec("ALTER TABLE board_posts ADD COLUMN user_id INT AFTER id");
        echo "<p class='success'>✓ user_id 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ user_id 컬럼 이미 존재</p>";
    }

    // username 컬럼이 없으면 추가
    if (!in_array('username', $columnNames)) {
        echo "<p class='info'>username 컬럼을 추가합니다...</p>";
        $pdo->exec("ALTER TABLE board_posts ADD COLUMN username VARCHAR(100) AFTER user_id");
        echo "<p class='success'>✓ username 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ username 컬럼 이미 존재</p>";
    }

    // likes 컬럼이 없으면 추가
    if (!in_array('likes', $columnNames)) {
        echo "<p class='info'>likes 컬럼을 추가합니다...</p>";
        $pdo->exec("ALTER TABLE board_posts ADD COLUMN likes INT DEFAULT 0");
        echo "<p class='success'>✓ likes 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ likes 컬럼 이미 존재</p>";
    }

    // 2. board_comments 테이블 확인
    echo "<h2>2. board_comments 테이블 확인</h2>";

    $commentsColumns = $pdo->query("SHOW COLUMNS FROM board_comments")->fetchAll();
    $commentsColumnNames = array_column($commentsColumns, 'Field');
    echo "<div class='info'>현재 컬럼: " . implode(', ', $commentsColumnNames) . "</div>";

    // user_id 컬럼이 없으면 추가
    if (!in_array('user_id', $commentsColumnNames)) {
        echo "<p class='info'>user_id 컬럼을 추가합니다...</p>";
        $pdo->exec("ALTER TABLE board_comments ADD COLUMN user_id INT AFTER id");
        echo "<p class='success'>✓ user_id 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ user_id 컬럼 이미 존재</p>";
    }

    // username 컬럼이 없으면 추가
    if (!in_array('username', $commentsColumnNames)) {
        echo "<p class='info'>username 컬럼을 추가합니다...</p>";
        $pdo->exec("ALTER TABLE board_comments ADD COLUMN username VARCHAR(100) AFTER user_id");
        echo "<p class='success'>✓ username 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ username 컬럼 이미 존재</p>";
    }

    // 3. 기존 데이터 업데이트 (author 컬럼이 있다면)
    echo "<h2>3. 기존 데이터 업데이트</h2>";

    if (in_array('author', $columnNames)) {
        echo "<p class='info'>author 컬럼의 데이터를 username으로 복사합니다...</p>";
        $pdo->exec("UPDATE board_posts SET username = author WHERE username IS NULL OR username = ''");
        echo "<p class='success'>✓ 데이터 복사 완료</p>";

        // user_id 업데이트
        echo "<p class='info'>username을 기반으로 user_id를 업데이트합니다...</p>";
        $pdo->exec("UPDATE board_posts p
                    JOIN users u ON p.username = u.username
                    SET p.user_id = u.id
                    WHERE p.user_id IS NULL OR p.user_id = 0");
        echo "<p class='success'>✓ user_id 업데이트 완료</p>";
    }

    // 4. 최종 확인
    echo "<h2>4. 최종 확인</h2>";
    $finalColumns = $pdo->query("SHOW COLUMNS FROM board_posts")->fetchAll();
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($finalColumns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h2 class='success'>✅ 모든 작업이 완료되었습니다!</h2>";
    echo "<p><a href='setup-mypage.php'>setup-mypage.php 실행하기</a></p>";
    echo "<p><a href='mypage.php'>마이페이지로 이동</a></p>";

} catch (Exception $e) {
    echo "<div class='error'>오류 발생: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
