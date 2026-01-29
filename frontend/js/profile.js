$(document).ready(function () {

    let token = localStorage.getItem("session_token");

    if (!token) {
        window.location.href = "login.html";
        return;
    }

    // Token exists → verify with backend
    $.ajax({
        url: "../backend/validate_session.php",
        type: "POST",
        data: { token: token },
        success: function (response) {
            if (response.status !== "success") {
                localStorage.removeItem("session_token");
                window.location.href = "login.html";
            }
        }
    });

    // Fetch profile data
    $.ajax({
        url: "../backend/profile_fetch.php",
        type: "POST",
        data: { token },
        success: function (res) {
            if (res.status === "success") {
                $("#age").val(res.data.age);
                $("#dob").val(res.data.dob);
                $("#contact").val(res.data.contact);
            }
        }
    });

    // Update profile
    $("#updateProfile").click(function () {

        $.ajax({
            url: "../backend/profile_update.php",
            type: "POST",
            data: {
                token,
                age: $("#age").val(),
                dob: $("#dob").val(),
                contact: $("#contact").val()
            },
            success: function (res) {
                alert(res.message);
            }
        });

    });

    $("#logoutBtn").click(function () {

        let token = localStorage.getItem("session_token");

        $.ajax({
            url: "../backend/logout.php",
            type: "POST",
            data: { token: token },
            complete: function () {
                // Frontend cleanup
                localStorage.removeItem("session_token");
                window.location.href = "login.html";
            }
        });

    });
});