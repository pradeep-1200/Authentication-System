$(document).ready(function () {
    
    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertPlaceholder').html(alertHtml);
    }

    $("#registerBtn").click(function () {
        
        // Collect Auth Details
        let name = $("#name").val().trim();
        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        // Collect Profile Details
        let age = $("#age").val().trim();
        let dob = $("#dob").val().trim();
        let contact = $("#contact").val().trim();

        // Validate
        if (!name || !email || !password || !age || !dob || !contact) {
            showAlert("Please fill in ALL fields (Account & Profile)", "warning");
            return;
        }

        // Loading
        let $btn = $(this);
        let originalText = $btn.html();
        $btn.prop('disabled', true).html('Processing...');

        $.ajax({
            url: "/backend/register.php",
            type: "POST",
            dataType: "json",
            data: {
                name, email, password,
                age, dob, contact
            },
            success: function (response) {
                if (response.status === 'success') {
                    showAlert(response.message, "success");
                    $("#registerBtn").hide(); // prevent multiple submits
                    setTimeout(() => window.location.href = "login.html", 1500);
                } else {
                    showAlert(response.message || "Registration failed", "danger");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                showAlert("Server communication error", "danger");
            },
            complete: function() {
                if ($("#registerBtn").is(":visible")) {
                   $btn.prop('disabled', false).html(originalText);
                }
            }
        });
    });
});