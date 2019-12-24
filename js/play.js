$("#playButton").click(function () {
    $.ajax("api/engine.php/join", {
        type: "GET",
        success: join
    });

});

function join(response, status, xhr) {
    $(".content").removeClass("disappear");
    $.ajax("api/engine.php/token", {
        type: "GET",
        success: assignToken
    });
}

function assignToken(response, status, xhr) {
    let returnedToken = JSON.parse(response);
    let token = returnedToken.token;
    let username = returnedToken.user_name;
    let controller = new Controller(token, username, getGame(token));
}

function getGame(token){
    let game = null;
    $.ajax("api/engine.php/game", {
        type: "GET",
        success: function (response) {
            game = new Game(response.status, response.players, response.points, response.cards);
        }
    });
    return game;
}

