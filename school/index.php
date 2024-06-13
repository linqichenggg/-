<?php
session_start();
if (isset($_SESSION['id'])) {
    if (isset($_SESSION['selected_course'])) {
        header("Location: dashboard.php");
    } else {
        header("Location: select_course.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>教学管理系统</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <style>
        /* 添加一些测试样式 */
        body {
            border: 5px solid red;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="container">
        <h1>欢迎来到教学管理系统</h1>
        <form action="login.php" method="post" onsubmit="return validateLogin();">
            <label for="username">用户名：</label>
            <input type="text" id="username" name="username" required>
            <label for="password">密码：</label>
            <input type="password" id="password" name="password" required>
            <label for="role">身份：</label>
            <select id="role" name="role" required>
                <option value="student">学生</option>
                <option value="teacher">老师</option>
            </select>
            <button type="submit">登录</button>
            <div id="loginErrorMessage" class="error-message"></div>
        </form>
        <footer>
            <p>© 2024 管理信息系统-教学管理系统</p>
            <p> 李豪 林岂丞 </p>
        </footer>
    </div>
</body>
</html>

