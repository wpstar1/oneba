<?php
// 마이페이지 전체 설정 스크립트 v2
require_once 'api/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>마이페이지 설정 v2</title>";
echo "<style>body{font-family:sans-serif;padding:20px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;}.error{color:red;}.info{color:blue;}</style></head><body>";
echo "<h1>마이페이지 데이터베이스 설정 v2</h1>";

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. user_points 테이블
    echo "<p class='info'>1. user_points 테이블 생성 중...</p>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_points (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user (user_id),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p class='success'>✓ user_points 테이블 생성 완료</p>";

    // 2. point_history 테이블
    echo "<p class='info'>2. point_history 테이블 생성 중...</p>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS point_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount INT NOT NULL,
        description VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p class='success'>✓ point_history 테이블 생성 완료</p>";

    // 3. attendance_records 테이블
    echo "<p class='info'>3. attendance_records 테이블 생성 중...</p>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        attendance_date DATE NOT NULL,
        points_earned INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (user_id, attendance_date),
        INDEX idx_user_id (user_id),
        INDEX idx_date (attendance_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p class='success'>✓ attendance_records 테이블 생성 완료</p>";

    // 4. user_blocks 테이블
    echo "<p class='info'>4. user_blocks 테이블 생성 중...</p>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_blocks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        blocked_user_id INT NOT NULL,
        reason VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_block (user_id, blocked_user_id),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p class='success'>✓ user_blocks 테이블 생성 완료</p>";

    // 5. user_notifications 테이블
    echo "<p class='info'>5. user_notifications 테이블 생성 중...</p>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        notification_type VARCHAR(50) NOT NULL,
        enabled TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_notification (user_id, notification_type),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p class='success'>✓ user_notifications 테이블 생성 완료</p>";

    // 6. board_posts 테이블 컬럼 추가
    echo "<p class='info'>6. board_posts 테이블 컬럼 확인 중...</p>";

    // 모든 컬럼 가져오기
    $allColumns = $pdo->query("SHOW COLUMNS FROM board_posts")->fetchAll(PDO::FETCH_COLUMN);

    // views 컬럼 추가
    if (!in_array('views', $allColumns)) {
        $pdo->exec("ALTER TABLE board_posts ADD COLUMN views INT DEFAULT 0");
        echo "<p class='success'>✓ views 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ views 컬럼 이미 존재</p>";
    }

    // likes 컬럼 추가
    if (!in_array('likes', $allColumns)) {
        $pdo->exec("ALTER TABLE board_posts ADD COLUMN likes INT DEFAULT 0");
        echo "<p class='success'>✓ likes 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ likes 컬럼 이미 존재</p>";
    }

    // 7. board_comments 테이블 확인
    echo "<p class='info'>7. board_comments 테이블 확인 중...</p>";

    $commentColumns = $pdo->query("SHOW COLUMNS FROM board_comments")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('user_id', $commentColumns)) {
        $pdo->exec("ALTER TABLE board_comments ADD COLUMN user_id INT AFTER id");
        echo "<p class='success'>✓ user_id 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ user_id 컬럼 이미 존재</p>";
    }

    // 8. 기존 사용자들에게 기본 포인트 추가
    echo "<p class='info'>8. 기존 사용자 포인트 초기화 중...</p>";

    $users = $pdo->query("SELECT id FROM users")->fetchAll();
    $addedCount = 0;

    foreach ($users as $user) {
        // 이미 포인트가 있는지 확인
        $checkStmt = $pdo->prepare("SELECT id FROM user_points WHERE user_id = ?");
        $checkStmt->execute([$user['id']]);

        if (!$checkStmt->fetch()) {
            // 포인트 레코드 생성
            $insertStmt = $pdo->prepare("INSERT INTO user_points (user_id, points) VALUES (?, 100)");
            $insertStmt->execute([$user['id']]);
            $addedCount++;
        }
    }

    echo "<p class='success'>✓ {$addedCount}명의 사용자에게 초기 포인트 100P 지급 (전체 " . count($users) . "명)</p>";

    // 9. users 테이블에 status 컬럼 확인
    echo "<p class='info'>9. users 테이블 status 컬럼 확인 중...</p>";

    $userColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('status', $userColumns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER password");
        echo "<p class='success'>✓ status 컬럼 추가 완료</p>";
    } else {
        echo "<p class='success'>✓ status 컬럼 이미 존재</p>";
    }

    echo "<hr>";
    echo "<h2 class='success'>✅ 마이페이지 데이터베이스 설정이 완료되었습니다!</h2>";
    echo "<p><a href='mypage.php' style='padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:5px;'>마이페이지로 이동</a></p>";

} catch (Exception $e) {
    echo "<p class='error'>✗ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
