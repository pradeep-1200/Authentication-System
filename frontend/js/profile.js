$(document).ready(function () {

    // --- Helper Functions ---
    function showAlert(message, type = 'danger') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <div>${message}</div>
                <button type="button" class="btn-close close-alert-btn" aria-label="Close"></button>
            </div>
        `;
        $('#alertPlaceholder').html(alertHtml);
    }


    // --- Event Listener for Alert Close Button ---
    $(document).on('click', '.close-alert-btn', function() {
        $(this).closest('.alert').remove();
    });

    function toggleEditMode(enable) {
        if (enable) {
            $("#age, #dob, #contact").prop("disabled", false);
            $("#saveActions").removeClass("d-none");
            $("#enableEditBtn").addClass("d-none");
        } else {
            $("#age, #dob, #contact").prop("disabled", true);
            $("#saveActions").addClass("d-none");
            $("#enableEditBtn").removeClass("d-none");
        }
    }

    // --- Auth Check ---
    let token = localStorage.getItem("session_token");

    if (!token) {
        window.location.href = "login.html";
        return;
    }

    // --- Validate Session ---
    $.ajax({
        url: "/backend/validate_session.php",
        type: "POST",
        dataType: "json",
        data: { token: token },
        success: function (response) {
            if (response.status !== "success") {
                localStorage.removeItem("session_token");
                window.location.href = "login.html";
            }
        }
    });

    // --- Fetch Profile Data ---
    $.ajax({
        url: "/backend/profile_fetch.php",
        type: "GET",
        headers: {
            "Authorization": token
        },
        success: function (res) {
            if (res.status === "success") {
                $("#age").val(res.data.age);
                $("#dob").val(res.data.dob);
                $("#contact").val(res.data.contact);
            } else {
                showAlert(res.message, "warning");
            }
        },
        error: function (xhr) {
            const msg = xhr && xhr.responseText ? xhr.responseText : "Connection error while fetching profile.";
            showAlert(msg, "danger");
        }
    });

    // --- UI Interactions ---
    $("#enableEditBtn").click(function() {
        toggleEditMode(true);
    });

    $("#cancelEditBtn").click(function() {
        toggleEditMode(false);
        // Optionally re-fetch data to reset changes
        location.reload(); 
    });

    // --- Update Profile ---
    $("#updateProfile").click(function () {
        
        let $btn = $(this);
        let originalText = $btn.html();
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: "/backend/profile_update.php",
            type: "POST",
            dataType: "json",
            data: {
                token,
                age: $("#age").val(),
                dob: $("#dob").val(),
                contact: $("#contact").val()
            },
            success: function (res) {
                if(res.status === 'success') {
                    showAlert(res.message, "success");
                    toggleEditMode(false); // Return to read-only
                } else {
                    showAlert(res.message || "Update failed", "danger");
                }
            },
            error: function () {
                showAlert("An error occurred while updating.", "danger");
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // --- Logout ---
    $("#logoutBtn").click(function () {
        $.ajax({
            url: "/backend/logout.php",
            type: "POST",
            data: { token: token },
            complete: function () {
                localStorage.removeItem("session_token");
                window.location.href = "login.html";
            }
        });
    });
});
