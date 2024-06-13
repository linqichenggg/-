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

// 发布作业
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publish_homework'])) {
    $course_id = $_POST['course_id'];
    $course_time = $_POST['course_time'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $query = "INSERT INTO homework (course_id, course_time, teacher_id, account, description, due_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $course_id, $course_time, $teacher_id, $teacher_account, $description, $due_date);
    $stmt->execute();
}

// 批改作业
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade_homework'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    $query = "UPDATE homework_submissions SET grade = ?, feedback = ? WHERE submission_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $grade, $feedback, $submission_id);
    $stmt->execute();
}

// 获取所有发布的作业
$query = "SELECT * FROM homework WHERE teacher_id = ? AND account = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $teacher_id, $teacher_account);
$stmt->execute();
$result = $stmt->get_result();
$homeworks = $result->fetch_all(MYSQLI_ASSOC);

// 获取所有未批改的作业提交
$query = "
    SELECT hs.*, h.course_id, h.course_time, h.description, u.name AS student_name
    FROM homework_submissions hs
    JOIN homework h ON hs.homework_id = h.homework_id
    JOIN user u ON hs.student_id = u.id
    WHERE h.teacher_id = ? AND hs.grade IS NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>管理作业</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>发布作业</h1>
        <form action="manage_homework.php" method="post">
            <input type="hidden" name="publish_homework" value="1">
            <label for="course_id">课程ID：</label>
            <input type="text" id="course_id" name="course_id" required>
            <label for="course_time">课程时间：</label>
            <input type="text" id="course_time" name="course_time" required>
            <label for="description">作业描述：</label>
            <textarea id="description" name="description" required></textarea>
            <label for="due_date">截止日期：</label>
            <input type="date" id="due_date" name="due_date" required>
            <button type="submit">发布</button>
        </form>

        <h1>已发布的作业</h1>
        <?php if (empty($homeworks)): ?>
            <p>没有已发布的作业。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($homeworks as $homework): ?>
                    <li><?php echo "课程ID: " . $homework['course_id'] . ", 课程时间: " . $homework['course_time'] . ", 描述: " . $homework['description'] . ", 截止日期: " . $homework['due_date']; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h1>批改作业</h1>
        <?php if (empty($submissions)): ?>
            <p>没有未批改的作业。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($submissions as $submission): ?>
                    <li>
                        学生: <?php echo $submission['student_name']; ?>, 课程ID: <?php echo $submission['course_id']; ?>, 课程时间: <?php echo $submission['course_time']; ?>, 作业描述: <?php echo $submission['description']; ?><br>
                        提交内容: <?php echo $submission['content']; ?><br>
                        提交时间: <?php echo $submission['submission_time']; ?><br>
                        <form action="manage_homework.php" method="post">
                            <input type="hidden" name="grade_homework" value="1">
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
