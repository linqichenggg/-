<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['selected_course'])) {
    header("Location: dashboard.php");
    exit();
}

include 'config.php';

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_course = $_POST['course'];
    $_SESSION['selected_course'] = $selected_course;
    header("Location: dashboard.php");
    exit();
}

$query = $role === 'teacher' ? 
    "SELECT * FROM course WHERE teacher_id = ?" : 
    "SELECT course.* FROM course JOIN student_course ON course.course_id = student_course.course_id WHERE student_course.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>选择课程</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>选择课程</h1>
        <form action="select_course.php" method="post">
            <label for="course">课程：</label>
            <select id="course" name="course" required>
                <?php if (empty($courses)): ?>
                    <option value="">没有课程可选</option>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id'] . ',' . $course['course_time']; ?>">
                            <?php echo $course['course_name']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select><br>
            <button type="submit">选择</button>
        </form>
        <br>
        <form action="logout.php" method="post">
            <button type="submit" class="button">回到主界面</button>
        </form>
    </div>
</body>
</html>
