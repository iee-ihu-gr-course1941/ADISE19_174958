var token = null;
var currentUsername = null;

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
    token = returnedToken.token;
    currentUsername = returnedToken.user_name;

    setInterval(checkGame, 1000);
}

function loadTable(response, status, xmlHttpRequest) {
    let users = JSON.parse(response);

    previousUsers  = users;

    let players = $(".players").children(".player");


    users.forEach((user) => {
            let usersDiv = $("." + user.user_name);

            if (usersDiv.length) {
                updateUserData(user,players);
            } else {
                addUserToTable(user,players);
            }
        }
    );

    checkLeftUsers(users,players);

}

function addUserToTable(user, table) {
    let index = 0;

    while (!$(table[index]).hasClass("disappear") && index < table.length) {
        index++;
    }

    if (index < table.length) {
        let textColor = "text-dark";

        if (user.user_name === currentUsername) {
            textColor = "text-white";
            $(table[index]).addClass("bg-primary");

        }
        $(table[index]).addClass(user.user_name);
        let playerDiv = $("." + user.user_name);
        $(playerDiv).removeClass("disappear");
        $(playerDiv).find(".status").html("Status : " + user.player_status).addClass(textColor);
        $(playerDiv).find(".player_name").html(user.user_name).addClass(textColor);
        $(playerDiv).find(".money").html("Money : " + user.balance).addClass(textColor);
        $(playerDiv).find(".points").html("Status : " + user.points).addClass(textColor);
    }
}

function updateUserData(user, table) {
    let index = 0;

    while (!$(table[index]).hasClass("." + user.user_name) && index < table.length) {
        index++;
    }
    if (index < table.length) {
        let playerDiv = $("." + user.user_name);
        $(playerDiv).find(".status").html("Status : " + user.player_status).addClass("text-white");
        $(playerDiv).find(".player_name").html(user.user_name).addClass("text-white")
        $(playerDiv).find(".money").html("Money : " + user.balance).addClass("text-white")
        $(playerDiv).find(".points").html("Status : " + user.points).addClass("text-white")
    }

}

function checkLeftUsers(users,table){
    let leftUsers = table.filter((row)=>{
        for (user of users) {
            if (user.user_name === $(table[row]).find(".player_name").text()) {
                return false;
            }
        }
        return true;
    });
    for (let index = 0; index < leftUsers.length; index++) {
        $(leftUsers[index]).addClass("disappear");
    }
}

function checkGame() {
    //updates view
    $.ajax("api/engine.php/players", {
        type: "GET",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('TOKEN', token);
        },
        success: loadTable
    });

    //checks status
    $.ajax("api/engine.php/game_status", {
        type: "GET",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('TOKEN', token);
        },
        success: checkStatus
    });
}

function checkStatus(response, status, xhr) {
    let gameStatus = JSON.parse(response);
    $(".computer-status").html("Game's Status : " + gameStatus.games_status);
    switch (gameStatus.games_status) {
        case "betting":
            betting();
            break;
        case "players_turn" :

            break;
        case "computer_turn" :

            break;
    }
}

function betting() {

}
