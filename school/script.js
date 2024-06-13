function validateLogin() {
    var username = document.getElementById("username").value.trim();
    var password = document.getElementById("password").value.trim();
    var role = document.getElementById("role").value;

    if (username === "" || password === "" || role === "") {
        document.getElementById("loginErrorMessage").innerText = "请填写所有字段";
        return false;
    }
    return true;
}
