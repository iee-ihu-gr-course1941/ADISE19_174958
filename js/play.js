var updater = null;
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
    let game = getGame(token);
    game.user = getUser(token);
    let controller = new Controller(token, game);
    updater = setInterval(function () {
        $.ajax("api/engine.php/game",
            {
                success: (response) => {
                    let game = JSON.parse(response);
                    game.user = getUser(token);
                    controller.updateGame(game);
                },
                type: "GET",
                beforeSend: (xhr) => {
                    xhr.setRequestHeader("TOKEN", token);
                }
            });
    }, 1000)

}


function getGame(token) {
    let game = null;
    $.ajax("api/engine.php/game", {
        type: "GET",
        beforeSend: function (xhr) {
            xhr.setRequestHeader("TOKEN", token)
        },
        async: false,
        success: function (response, status, xhr) {
            game = JSON.parse(response);
        }
    });
    return game;
}

function getUser(token) {
    let user = null;
    $.ajax("api/engine.php/user", {
        type: "GET",
        beforeSend: function (xhr) {
            xhr.setRequestHeader("TOKEN", token)
        },
        async: false,
        success: function (response, status, xhr) {
            user = JSON.parse(response);
        }
    });
    return user;
}

function stopUpdater() {
    if (updater != null) {
        clearInterval(updater);
    }
}

class Controller {

    constructor(token, game) {
        this.game = game;
        this.token = token;
        this.view = new View(game);
        this.view.render();
        this._play();
    }


    updateGame(game) {
        this.game = game;
        this.view.update(this.game);
        if (this._needToExit()) {
            this.view.close();
            stopUpdater();
        }
        this._play();
    }

    _needToExit() {
        return this.game.user === undefined;
    }

    _play() {
        switch (this.game.status) {
            case "betting":
                this._tryBetting();
                break;
            case "players_turn":
                this._tryHitting();
                break;
        }
    }

    _tryBetting() {
        $.ajax("api/engine.php/bet", {
            success: (response) => {
                let answer = JSON.parse(response);

                if (answer.answer === true) {
                    let bet = 0;
                    do {
                        bet = Number(window.prompt("How much do you want to bet?", "0"));

                        if (isNaN(bet) || bet > this.game.user.balance) {
                            window.prompt("Wrong input.Your input = " + bet + ".Correct inputs' range [" + 0 + "," + this.game.user.balance + "]");
                        }

                    } while (isNaN(bet) && bet > this.game.user.balance) ;
                    $.ajax("api/engine.php/bet", {
                        type: "POST",
                        data: {
                            amount: bet
                        },
                        beforeSend: (xhr) => xhr.setRequestHeader("TOKEN", this.token)
                    });
                }
            },
            type: "GET",
            beforeSend: (xhr) => xhr.setRequestHeader("TOKEN", this.token)
        });
    }

    _tryHitting() {

    }


}

class View {

    constructor(game) {
        this.game = game;
    }

    render() {
        this._clear();
        this._renderGame();

        for (let card of this.game.cards) {
            $(".computer-cards").append(`<img src="${card}" class="img-fluid my_card p-1"/>`);
        }

        for (let player of this.game.players) {
            this._renderPlayer(player);
        }

        $(`.computer-card`).addClass("bg-dark").addClass("text-white");

    }

    close() {
        this._clear();
    }

    _renderGame() {
        $(".gameView").removeClass(".disappear");
        $(".computer-status").html("Game's Status : " + this.game.status);
        $(".computer-points").html("Points : " + this.game.points);
    }

    _clear() {
        $(".computer-status").empty();
        $(".computer-points").empty();
        $(".computer-cards").empty();
        $(".players").empty();
    }

    _renderPlayer(player) {
        let cardDiv = `
            <div class="card player ${player.username}-player">
                <div class="card-header text-center">
                    ${player.username}
                </div>
                <div class="card-body ">
                    <p class="text-left ${player.username}-status">Status:${player.status}</p>
                    <p class="text-left ${player.username}-money">Money:${player.balance}</p>
                    <p class="text-left ${player.username}-points">Points:${player.points}</p>
                </div>
                
                    <div class="d-flex flex-wrap justify-content-center mt-2 ${player.username}-cards">
                    </div>
            </div>`;

        $(".players").append(cardDiv);

        if (player.username === this.game.user.username) {
            $(`.${player.username}-player`).addClass("text-white").addClass(`bg-primary`);
        }

        for (let card of player.cards) {
            $(`.${player.username}-cards`).append(`<img src="${card}" class="img-fluid my_card p-1"/>`);
        }
    }

    update(game) {
        let oldPlayers = this.game.players;
        this.game = game;

        this._renderGame();
        this._removeLeftPlayers(oldPlayers);

        for (let player of this.game.players) {
            $(`.${player.username}-status`).html("Status : " + player.status);
            $(`.${player.username}-money`).html("Money : " + player.balance);
            $(`.${player.username}-points`).html("Points : " + player.points);
        }

        this.game.players.filter((player) => {
            for (let oldPlayer of oldPlayers) {
                if (oldPlayer.username === player.username) {
                    return false;
                }
            }
            return true;
        }).forEach((player) => {
            this._renderPlayer(player);
        })

    }

    _removeLeftPlayers(oldPlayers) {
        oldPlayers.filter((oldPlayer) => {
            for (let player of this.game.players) {
                if (player.username === oldPlayer.username) {
                    return false;
                }
            }
            return true;
        }).forEach((leftPlayer) => {
            $(`.${leftPlayer.username}-player`).remove();
        });
    }
}