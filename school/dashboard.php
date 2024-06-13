<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['selected_course'])) {
    header("Location: select_course.php");
    exit();
}

list($course_id, $course_time) = explode(',', $_SESSION['selected_course']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>欢迎, <?php echo $_SESSION['username']; ?>!</h1>
        <p>当前课程: <?php echo $course_id; ?> (<?php echo $course_time; ?>)</p>
        <a href="logout.php" class="button">登出</a>
        <div class="dashboard-links">
            <?php if ($_SESSION['role'] == 'teacher'): ?>
                <a href="manage_checkin.php" class="button">管理签到</a>
                <a href="manage_homework.php" class="button">管理作业</a>
            <?php else: ?>
                <a href="checkin.php" class="button">签到</a>
                <a href="submit_homework.php" class="button">提交作业</a>
                <a href="view_grades.php" class="button">查看成绩</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
