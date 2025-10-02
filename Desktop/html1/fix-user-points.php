<?php
// user_points 테이블 수정
require_once 'api/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>포인트 테이블 수정</title>";
echo "<style>body{font-family:sans-serif;padding:20px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;padding:10px;background:#e8f5e9;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#ffebee;margin:10px 0;}";
echo ".info{color:blue;padding:10px;background:#e3f2fd;margin:10px 0;}</style></head><body>";

echo "<h1>포인트 테이블 수정</h1>";

try {
    // 1. user_points 테이블 구조 확인
    echo "<h2>1. user_points 테이블 확인</h2>";

    try {
        $columns = $pdo->query("SHOW COLUMNS FROM user_points")->fetchAll();
        $columnNames = array_column($columns, 'Field');

        echo "<div class='info'>현재 컬럼: " . implode(', ', $columnNames) . "</div>";

        // points 컬럼이 없으면 추가
        if (!in_array('points', $columnNames)) {
            echo "<p class='info'>points 컬럼을 추가합니다...</p>";
            $pdo->exec("ALTER TABLE user_points ADD COLUMN points INT DEFAULT 0 AFTER user_id");
            echo "<p class='success'>✓ points 컬럼 추가 완료</p>";
        } else {
            echo "<p class='success'>✓ points 컬럼 이미 존재</p>";
        }

    } catch (Exception $e) {
        // 테이블이 없으면 새로 생성
        echo "<p class='info'>user_points 테이블이 없습니다. 새로 생성합니다...</p>";

        $pdo->exec("CREATE TABLE IF NOT EXISTS user_points (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            points INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user (user_id),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        echo "<p class='success'>✓ user_points 테이블 생성 완료</p>";
    }

    // 2. 기존 사용자들에게 포인트 추가
    echo "<h2>2. 사용자 포인트 초기화</h2>";

    $users = $pdo->query("SELECT id, username FROM users")->fetchAll();
    $addedCount = 0;
    $skippedCount = 0;

    foreach ($users as $user) {
        // 이미 포인트가 있는지 확인
        $checkStmt = $pdo->prepare("SELECT id FROM user_points WHERE user_id = ?");
        $checkStmt->execute([$user['id']]);

        if (!$checkStmt->fetch()) {
            // 포인트 레코드 생성
            $insertStmt = $pdo->prepare("INSERT INTO user_points (user_id, points) VALUES (?, 100)");
            $insertStmt->execute([$user['id']]);
            echo "<p class='info'>✓ {$user['username']}님에게 100P 지급</p>";
            $addedCount++;
        } else {
            $skippedCount++;
        }
    }

    echo "<div class='success'>";
    echo "<strong>완료!</strong><br>";
    echo "- 새로 지급: {$addedCount}명<br>";
    echo "- 이미 있음: {$skippedCount}명<br>";
    echo "- 전체: " . count($users) . "명";
    echo "</div>";

    // 3. 최종 테이블 구조 확인
    echo "<h2>3. 최종 테이블 구조</h2>";
    $finalColumns = $pdo->query("SHOW COLUMNS FROM user_points")->fetchAll();

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

    // 4. 포인트 현황
    echo "<h2>4. 포인트 현황</h2>";
    $pointsData = $pdo->query("
        SELECT u.username, up.points, up.created_at
        FROM user_points up
        JOIN users u ON up.user_id = u.id
        ORDER BY up.points DESC
        LIMIT 10
    ")->fetchAll();

    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>사용자명</th><th>포인트</th><th>생성일</th></tr>";
    foreach ($pointsData as $data) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($data['username']) . "</td>";
        echo "<td><strong>" . number_format($data['points']) . "P</strong></td>";
        echo "<td>" . $data['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h2 class='success'>✅ 포인트 테이블 수정이 완료되었습니다!</h2>";
    echo "<p><a href='mypage.php' style='padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:5px;'>마이페이지로 이동</a></p>";

} catch (Exception $e) {
    echo "<div class='error'>오류 발생: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
