$(document).ready(function () {

    // Alert Helper Function
    function showAlert(message, type = 'danger') {
        const icon = type === 'success' 
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' 
            : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${icon}
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertPlaceholder').html(alertHtml);
    }

    $("#loginBtn").click(function () {

        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        if (email === "" || password === "") {
            showAlert("All fields required", "warning");
            return;
        }

        // Loading State
        let $btn = $(this);
        let originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');

        $.ajax({
            url: "/backend/login.php",
            type: "POST",
            dataType: "json",
            data: { email, password },
            success: function (response) {
                if (response.status === "success") {
                    localStorage.setItem("session_token", response.token);
                    // Slight delay to show success state if needed, or instant redirect
                    window.location.href = "profile.html";
                } else {
                    showAlert(response.message || "Invalid credentials", "danger");
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function (xhr, status, error) {
                console.error("Login Error:", error);
                showAlert("Login failed. Please check your connection.", "danger");
                $btn.prop('disabled', false).html(originalText);
            }
        });

    });

});