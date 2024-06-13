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
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    // 批改作业
    $query = "UPDATE homework_submissions SET grade = ?, feedback = ? WHERE submission_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $grade, $feedback, $submission_id);
    $stmt->execute();
}

// 获取所有提交的作业
$query = "
    SELECT hs.*, h.course_id, h.course_time, h.description, u.name AS student_name
    FROM homework_submissions hs
    JOIN homework h ON hs.homework_id = h.homework_id
    JOIN user u ON hs.student_id = u.id
    WHERE h.teacher_id = ? AND h.account = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $teacher_id, $teacher_account);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>批改作业</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>批改作业</h1>
        <?php if (empty($submissions)): ?>
            <p>没有提交的作业。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($submissions as $submission): ?>
                    <li>
                        学生: <?php echo $submission['student_name']; ?>, 课程ID: <?php echo $submission['course_id']; ?>, 课程时间: <?php echo $submission['course_time']; ?>, 作业描述: <?php echo $submission['description']; ?><br>
                        提交内容: <?php echo $submission['content']; ?><br>
                        提交时间: <?php echo $submission['submission_time']; ?><br>
                        <form action="grade_homework.php" method="post">
                            <input type="hidden" name="submission_id" value="<?php echo $submission['submission_id']; ?>">
                            <label for="grade">评分：</label>
                            <input type="text" id="grade" name="grade" required>
                            <label for="feedback">反馈：</label>
                            <textarea id="feedback" name="feedback" required></textarea>
                            <button type="submit">提交批改</button>
                        </form>
                    </li>
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
