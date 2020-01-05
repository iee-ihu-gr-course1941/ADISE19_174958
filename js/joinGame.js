$("#playButton").click(function () {
    $.ajax("api/engine.php/join", {
        type: "GET",
        success: join
    });

});

function join(response, status, xhr) {
    document.location.href = response;
}