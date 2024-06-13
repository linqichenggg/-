<?php
session_start();
include 'config.php';

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 验证用户身份
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['id'];
$student_account = $_SESSION['account'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in_id = $_POST['check_in_id'];

    // 学生签到
    $query = "INSERT INTO check_in_records (check_in_id, student_id, student_account) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("SQL prepare 错误: " . $conn->error);
    }

    $bind = $stmt->bind_param("iss", $check_in_id, $student_id, $student_account);
    if ($bind === false) {
        die("参数绑定错误: " . $stmt->error);
    }

    $exec = $stmt->execute();
    if ($exec === false) {
        die("SQL 执行错误: " . $stmt->error);
    }

    echo "签到成功";
}

// 获取所有签到任务
$query = "SELECT * FROM check_in";
$result = $conn->query($query);
$tasks = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>签到</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>签到</h1>
        <form action="checkin.php" method="post">
            <label for="check_in_id">签到任务ID：</label>
            <select id="check_in_id" name="check_in_id" required>
                <?php if (empty($tasks)): ?>
                    <option value="">没有可签到的任务</option>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?php echo $task['check_in_id']; ?>">
                            <?php echo "课程ID: " . $task['course_id'] . ", 课程时间: " . $task['course_time']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button type="submit">签到</button>
        </form>

        <h1>查看签到状态</h1>
        <?php if (empty($tasks)): ?>
            <p>没有签到任务。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($tasks as $task): ?>
                    <?php
                    $query = "SELECT * FROM check_in_records WHERE check_in_id = ? AND student_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("is", $task['check_in_id'], $student_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $record = $result->fetch_assoc();
                    ?>
                    <?php if ($record): ?>
                        <li><?php echo "课程ID: " . $task['course_id'] . ", 课程时间: " . $task['course_time'] . ", 签到时间: " . $record['check_in_time']; ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <br>
        <form action="dashboard.php" method="post">
            <button type="submit" class="button">返回主界面</button>
        </form>
    </div>
</body>
</html>
