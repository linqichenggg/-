<?php
session_start();
include 'config.php';

// 获取POST数据
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// 首先检查用户名是否存在
$query = "SELECT * FROM user WHERE account = ? AND role = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result = $stmt->get_result();

// 检查查询结果
if ($result->num_rows > 0) {
    // 用户名存在，检查密码
    $user = $result->fetch_assoc();
    if ($password === $user['password']) {
        // 密码正确
        $_SESSION['id'] = $user['id'];
        $_SESSION['account'] = $user['account'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: select_course.php");
        exit();
    } else {
        // 密码错误
        $error_message = "密码错误";
    }
} else {
    // 用户名不存在
    $error_message = "用户名不存在";
}

// 如果存在错误，返回并显示错误消息
if (isset($error_message)) {
    echo "<script>alert('$error_message');history.back();</script>";
}
?>
