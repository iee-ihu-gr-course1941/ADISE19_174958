
$("#signInBtn").on("click",signInHandler);

function signInHandler() {
    let user = {};
    user.user_name = $("#user_name").val();
    user.pass_word = $("#pass_word").val();
    let user_json = JSON.stringify(user);

    $.ajax("api/engine.php/signIn", {
        success: successful_login,
        type: "PUT",
        contentType: "application/json",
        data: user_json,
        error:handleError
    });
}

function handleError(xhr, status, error) {
    console.log(xhr.responseText);
    $(".message").removeClass("alert-success").addClass("alert-danger").html("<strong>Error!</strong>"+xhr.responseText);
}

function successful_login(result, status, xhr) {
    $(".message").removeClass("alert-danger").addClass("alert-success").html(
        "<strong>Successful login! </strong>You will be redirected in a few seconds"
    );
    setTimeout(function () {
        document.location.href = result;
    }, 3000);
}