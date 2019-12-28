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
        this.view = new View(this);
        this.view.render();
        this.hittingWindowOn = false;
        this.bettingWindowOn = false;
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
        return this.game.user === null;
    }

    _play() {
        if (this.game.user.status === "betting" && this.game.status === "Betting" && this.bettingWindowOn === false) {
            this._tryBetting();
        } else if (this.game.user.status === "hitting" && this.hittingWindowOn === false) {
            this._tryHitting()
        }else if (this.game.user.status === "waiting") {
            this.view.clearPlayersHand();
        } else if (this.game.user.status !== "hitting" && this.game.user.status !== "betting") {
            this.hittingWindowOn = false;
            this.bettingWindowOn = false;
            this.view.hideWindow();
        }

        if (this.game.status === "Initialized") {
            this.view.clearComputerHand();
        }
    }

    _tryBetting() {
        this.bettingWindowOn = true;
        this.view.showBettingWindow();
    }

    bet(bet) {
        if (bet < 0 || bet > this.game.user.balance) {
            window.alert("Wrong bet amount.Valid bets : [0," + this.game.user.balance + "].");
            return;
        }
        $.ajax("api/engine.php/bet", {
            type: "POST",
            data: {
                amount: bet
            },
            beforeSend: (xhr) => xhr.setRequestHeader("TOKEN", this.token)
        });
    }

    _tryHitting() {
        this.hittingWindowOn = true;
        this.view.showHittingWindow();
    }

    hit() {
        $.ajax("api/engine.php/hit", {
            beforeSend: (xhr) => {
                xhr.setRequestHeader("TOKEN", this.token);
            }
        })
    }

    enough() {
        $.ajax("api/engine.php/enough", {
            beforeSend: (xhr) => {
                xhr.setRequestHeader("TOKEN", this.token);
            }
        })
    }


}

class View {

    constructor(controller) {
        this.game = controller.game;
        this.controller = controller;
    }

    render() {
        this._clear();
        this._renderGame();

        for (let card of this.game.cards) {
            $(".computer-cards").append(`<img src="${card}" ${card} class="img-fluid my_card p-1"/>`);
        }

        for (let player of this.game.players) {
            this._renderPlayer(player);
        }

        $(`.computer-card`).addClass("bg-dark").addClass("text-white");

    }

    clearComputerHand(){
        $(`.computer-cards`).empty();
    }
    clearPlayersHand(){
        for(let player of $(".player") ){
            $(player).children(".player_hand").empty();
        }
    }

    hideWindow() {
        $(".splitter").empty();
    }

    showBettingWindow() {
        this.hideWindow();
        $(".splitter").append(
            `    <div class="form-group text-center col-12">
                    <label for="betInput" class="w-100">Amount To Bet:</label>
                    <input type="number" class="form-control" id="betInput" value="0">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary w-100 text-white bet_btn">Bet</button>
                </div>`
        );
        $(".bet_btn").click(() => {
            let bet = $("#betInput").val();
            this.controller.bet(bet);
        });
    }

    showHittingWindow() {
        this.hideWindow();
        $(".splitter").append(
            `    <select id="inputSelect" class="form-control">
                    <option value="hit">Hit</option>
                    <option value="enough">Enough</option>
                </select>
                <div class="col-12">
                    <button class="btn btn-primary w-100 text-white confirm_btn">Confirm</button>
                </div>`
        );
        $(".confirm_btn").click(() => {
            let choice = $("#inputSelect").val();
            if (choice === "hit") {
                this.controller.hit();
            } else if (choice === "enough") {
                this.controller.enough();
            }
        });
    }

    close() {
        this._clear();
        this.hideWindow();
    }

    _renderGame() {
        $(".gameView").removeClass(".disappear");
        $(".computer-status").html("Game's Status : " + this.game.status);
        $(".computer-points").html("Points : " + this.game.points);

        let cards = this.game.cards.filter((card) => {
            for (let existingCard of $(`.computer-cards`).children(".my_card")) {
                if ($(existingCard).hasClass(card)) {
                    return false;
                }
            }
            return true;

        });

        for (let card of cards) {
            $(".computer-cards").append(`<img src="${card}" class="img-fluid my_card p-1 ${card}"/>`);
        }

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
                
                    <div class="d-flex flex-wrap justify-content-center player_hand mt-2 bg-white ${player.username}-cards">
                    </div>
            </div>`;

        $(".players").append(cardDiv);

        if (player.username === this.game.user.username) {
            $(`.${player.username}-player`).addClass("text-white").addClass(`bg-primary`);
        }

        for (let card of player.cards) {
            $(`.${player.username}-cards`).append(`<img src="${card}" class="img-fluid my_card p-1  ${card} my_card""/>`);
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

            let cards = player.cards.filter((card) => {
                for (let existingCard of $(`.${player.username}-cards`).children(".my_card")) {
                    if ($(existingCard).hasClass(card)) {
                        return false;
                    }
                }
                return true;
            });
            for (let card of cards) {
                $(`.${player.username}-cards`).append(`<img src="${card}" class="img-fluid my_card p-1 ${card} my-card"/>`);
            }

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
