
$("#signUpBtn").click(function () {
    if (checkPassword()) {
        let user = {
            user_name : $("#user_name").val(),
            pass_word : $("#pass_word").val()
        };

        let user_json = JSON.stringify(user);

        $.ajax("api/engine.php/signUp", {
            success: success,
            type : "POST",
            contentType: "application/json",
            data: user_json,
            error: error
        });
    } else {
        show_error("The two passwords don't match.");
    }
});

function success(result, status, xhr) {
    $(".message").removeClass("alert-danger");
    $(".message").addClass("alert-success").html(
        "<strong>Successful Sign Up! </strong>You will be redirected to sign in page in a second."
    );
    setTimeout(function () {
        document.location.href = result;
    }, 3000);
}

function error(xhr, status, error) {
    show_error(xhr.responseText);
}

function show_error(error) {
    $(".message").addClass("alert-danger").html("<strong>Error!</strong>"+error);
}

function checkPassword() {
    return $("#pass_word").val() === $("#pass_word2").val();
}