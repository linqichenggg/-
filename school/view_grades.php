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

// 获取学生已完成的所有作业成绩，并计算平均分
$query = "
    SELECT hs.grade, h.course_id, h.course_time, h.description 
    FROM homework_submissions hs 
    JOIN homework h ON hs.homework_id = h.homework_id 
    WHERE hs.student_id = ? AND hs.grade IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);

$total_grades = 0;
$grade_count = count($grades);
foreach ($grades as $grade) {
    $total_grades += $grade['grade'];
}
$average_grade = ($grade_count > 0) ? $total_grades / $grade_count : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>查看成绩</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>查看成绩</h1>
        <p>平均成绩: <?php echo round($average_grade, 2); ?></p>

        <?php if (empty($grades)): ?>
            <p>没有已完成的作业成绩。</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>课程ID</th>
                    <th>课程时间</th>
                    <th>作业描述</th>
                    <th>成绩</th>
                </tr>
                <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?php echo $grade['course_id']; ?></td>
                        <td><?php echo $grade['course_time']; ?></td>
                        <td><?php echo $grade['description']; ?></td>
                        <td><?php echo $grade['grade']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <br>
        <form action="dashboard.php" method="post">
            <button type="submit" class="button">返回主界面</button>
        </form>
    </div>
</body>
</html>
