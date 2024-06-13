<?php
session_start();
include 'config.php';

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 验证用户身份
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['id'];
$teacher_account = $_SESSION['account'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $course_time = $_POST['course_time'];

    // 检查教师账号和ID是否存在于user表中
    $query = "SELECT * FROM user WHERE account = ? AND id = ? AND role = 'teacher'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $teacher_account, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 发布签到任务
        $query = "INSERT INTO check_in (course_id, course_time, teacher_id, teacher_account) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("SQL prepare 错误: " . $conn->error);
        }
        $bind = $stmt->bind_param("ssss", $course_id, $course_time, $teacher_id, $teacher_account);
        if ($bind === false) {
            die("参数绑定错误: " . $stmt->error);
        }
        $exec = $stmt->execute();
        if ($exec === false) {
            die("SQL 执行错误: " . $stmt->error);
        }
    } else {
        die("教师账号或ID不存在");
    }
}

// 获取所有已发布的签到任务及其签到记录
$query = "
    SELECT c.*, r.student_id, r.student_account, r.check_in_time
    FROM check_in c
    LEFT JOIN check_in_records r ON c.check_in_id = r.check_in_id
    WHERE c.teacher_id = ?
    ORDER BY c.check_in_id, r.check_in_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>管理签到</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>发布签到任务</h1>
        <form action="manage_checkin.php" method="post">
            <label for="course_id">课程ID：</label>
            <input type="text" id="course_id" name="course_id" required>
            <label for="course_time">课程时间：</label>
            <input type="text" id="course_time" name="course_time" required>
            <button type="submit">发布</button>
        </form>

        <h1>查看已发布任务</h1>
        <?php if (empty($tasks)): ?>
            <p>没有已发布的任务。</p>
        <?php else: ?>
            <ul>
                <?php
                $current_check_in_id = null;
                foreach ($tasks as $task):
                    if ($task['check_in_id'] != $current_check_in_id):
                        if ($current_check_in_id !== null): ?>
                            </ul>
                        <?php endif; ?>
                        <li>
                            课程ID: <?php echo $task['course_id']; ?>, 课程时间: <?php echo $task['course_time']; ?>, 签到状态: <?php echo $task['check_in_condition']; ?>
                            <ul>
                    <?php
                        $current_check_in_id = $task['check_in_id'];
                    endif;
                    if ($task['student_id'] !== null): ?>
                        <li>学生ID: <?php echo $task['student_id']; ?>, 学生账号: <?php echo $task['student_account']; ?>, 签到时间: <?php echo $task['check_in_time']; ?></li>
                    <?php endif;
                endforeach;
                if ($current_check_in_id !== null): ?>
                    </ul>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <br>
        <form action="dashboard.php" method="post">
            <button type="submit" class="button">返回主界面</button>
        </form>
    </div>
</body>
</html>
