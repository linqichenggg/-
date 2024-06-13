<?php
session_start();
include 'config.php';

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 验证用户身份
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'student') {
    die("未授权访问！");
}

$student_id = $_SESSION['id'];
$student_account = $_SESSION['account']; // 添加学生的账号

// 记录日志函数
function log_error($message) {
    error_log($message, 3, 'error_log.log');
}

// 调试输出
function debug_message($message) {
    echo "<pre>$message</pre>";
}


// 检查数据库连接
if ($conn->connect_error) {
    $error = '数据库连接失败: ' . $conn->connect_error;
    log_error($error);
    die($error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    debug_message("处理 POST 请求");
    $homework_id = $_POST['homework_id'];
    $content = $_POST['content'];

    // 提交作业
    $query = "INSERT INTO homework_submissions (homework_id, student_id, account, content) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
        log_error($error);
        die($error);
    }
    $stmt->bind_param("isss", $homework_id, $student_id, $student_account, $content);
    if (!$stmt->execute()) {
        $error = 'Execute failed: ' . $stmt->error;
        log_error($error);
        die($error);
    }
    debug_message("提交作业成功");
}

// 获取所有未提交的作业
$query = "SELECT h.homework_id, h.course_id, h.description, h.due_date 
          FROM homework h 
          JOIN student_course sc ON h.course_id = sc.course_id 
          WHERE sc.student_id = ? AND h.homework_id NOT IN (SELECT homework_id FROM homework_submissions WHERE student_id = ?)";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $error = 'Prepare failed: ' . $conn->error;
    log_error($error);
    die($error);
}
$stmt->bind_param("ss", $student_id, $student_id);
if (!$stmt->execute()) {
    $error = 'Execute failed: ' . $stmt->error;
    log_error($error);
    die($error);
}
$result = $stmt->get_result();
$homeworks = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>提交作业</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>提交作业</h1>
        <form action="submit_homework.php" method="post">
            <label for="homework_id">作业ID：</label>
            <select id="homework_id" name="homework_id" required>
                <?php if (empty($homeworks)): ?>
                    <option value="">没有未提交的作业</option>
                <?php else: ?>
                    <?php foreach ($homeworks as $homework): ?>
                        <option value="<?php echo $homework['homework_id']; ?>">
                            <?php echo "课程ID: " . $homework['course_id'] . ", 描述: " . $homework['description'] . ", 截止日期: " . $homework['due_date']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <label for="content">作业内容：</label>
            <textarea id="content" name="content" required></textarea>
            <button type="submit">提交</button>
        </form>

        <br>
        <form action="dashboard.php" method="post">
            <button type="submit" class="button">返回主界面</button>
        </form>
    </div>
</body>
</html>
