var updater = null;

$(function () {
    $(".content").removeClass("disappear");
    $.ajax("api/engine.php/token", {
        type: "GET",
        success: assignToken
    });
});

function assignToken(response, status, xhr) {
    let token = JSON.parse(response);
    let game = getGame(token);
    let controller = new Controller(token, game);
    updater = setInterval(function () {
        $.ajax("api/engine.php/game",
            {
                success: (response) => {
                    let game = JSON.parse(response);
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

        if (this._needToExit()) {
            this.view.close();
            stopUpdater();
            return;
        }

        this.view.update(this.game);

        this._play();
    }

    hit() {
        $.ajax("api/engine.php/hit", {
            beforeSend: (xhr) => {
                xhr.setRequestHeader("TOKEN", this.token);
            }
        })
    }

    bet(bet) {
        $.ajax("api/engine.php/bet", {
            type: "POST",
            data: {
                amount: bet
            },
            beforeSend: (xhr) => xhr.setRequestHeader("TOKEN", this.token)
        });
    }

    canBet(bet){
        return bet > 0 && bet <= this.game.user.balance;
    }

    enough() {
        $.ajax("api/engine.php/enough", {
            beforeSend: (xhr) => {
                xhr.setRequestHeader("TOKEN", this.token);
            }
        })
    }

    _needToExit() {
        return this.game.user === null;
    }

    _play() {
        if (this.game.user.status === "betting" && this.game.status === "Betting" && this.bettingWindowOn === false) {
            this._tryBetting();
        } else if (this.game.user.status === "hitting" && this.hittingWindowOn === false) {
            this._tryHitting()
        } else if (this.game.user.status !== "hitting" && this.game.user.status !== "betting" && ( this.hittingWindowOn === true || this.bettingWindowOn === true )) {
            this.hittingWindowOn = false;
            this.bettingWindowOn = false;
            this.view.hideWindows();
        }

    }

    _tryBetting() {
        this.bettingWindowOn = true;
        this.view.showBettingWindow();
    }

    _tryHitting() {
        this.hittingWindowOn = true;
        this.view.showHittingWindow();
    }


}

class View {

    constructor(controller) {
        this.game = controller.game;
        this.controller = controller;
    }

    render() {
        this._renderGame();

        for (let player of this.game.players) {
            this._renderPlayer(player);
        }

        $(`.computer-card`).addClass("bg-dark").addClass("text-white");

    }

    showBettingWindow() {
        $(`.hit_btn.${this.controller.game.user.username}`).hide(1000);
        $(`.enough_btn.${this.controller.game.user.username}`).hide(1000);
        $(`.bet_btn.${this.controller.game.user.username}`).show(1000);
        $(`.bet_input.${this.controller.game.user.username}`).show(1000);
    }

    showHittingWindow() {
        $(`.bet_btn.${this.controller.game.user.username}`).hide(1000);
        $(`.bet_input.${this.controller.game.user.username}`).hide(1000);
        $(`.hit_btn.${this.controller.game.user.username}`).show(1000);
        $(`.enough_btn.${this.controller.game.user.username}`).show(1000);
    }

    hideWindows(){
        $(`.hit_btn.${this.controller.game.user.username}`).hide(1000);
        $(`.enough_btn.${this.controller.game.user.username}`).hide(1000);
        $(`.bet_btn.${this.controller.game.user.username}`).hide(1000);
        $(`.bet_input.${this.controller.game.user.username}`).hide(1000);
    }

    _renderGame() {
        $(".gameView").removeClass(".disappear");
        $(".computer-status").html("Game's Status : " + this.game.status);
        $(".computer-points").html("Points : " + this.game.points);

        let result = this._filterElements(this.controller.game.cards,this._getCards("computer"));

        for (let card of result.toRender) {
            $(".computer-cards").append(`<img src="${card}" class="img-fluid my_card p-1 ${card}"/>`);
        }

        for (let card of result.toRemove) {
            let removedImage = document.getElementsByClassName("computer-cards")[0].getElementsByClassName(`${card}`)[0];
            $(removedImage).fadeOut(1000,function () {
                removedImage.parentNode.removeChild(removedImage);
            })
        }

    }

    close() {
        $(".computer-status").empty();
        $(".computer-points").empty();
        $(".computer-cards").empty();
        $(".players").empty();
        $(".gameView").addClass(".disappear");
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
                    <div class="d-flex flex-wrap justify-content-center">
                        <button class="hit_btn ${player.username} btn btn-success">Hit</button>
                        <button class="enough_btn ${player.username} ml-3 btn btn-danger">Enough</button>
                    </div>   
                    <div class="d-flex flex-wrap justify-content-center mt-2">
                        <input type="number" class="bet_input ${player.username}" placeholder="Put the amount you're willing to bet.">
                        <button class="bet_btn ${player.username} ml-3 btn btn-dark">Bet</button>
                    </div>
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

        $(`.bet_btn.${player.username}`).click(() => {
            let bet = $(`.bet_input.${player.username}`).val();
            if (this.controller.canBet(bet)) {
                this.controller.bet(bet);
            }
        });

        $(`.hit_btn.${player.username}`).click(()=>{
            this.controller.hit();
        });

        $(`.enough_btn.${player.username}`).click(()=>{
            this.controller.enough();
        });

        $(`.bet_btn.${player.username},.hit_btn.${player.username},.enough_btn.${player.username},.bet_input.${player.username}`).hide();
    }

    update(game) {
        let oldPlayers = this.game.players;
        this.game = game;
        this._renderGame();

        let playersResult = this._filterElements(this.game.players.map( (player) => {return player.username}) , oldPlayers.map( (oldPlayer)=>{return oldPlayer.username} ) );

        let newPlayers = playersResult.toRender.map((username)=>{
            for (let player of this.game.players) {
                if (player.username === username) {
                    return player;
                }
            }
        });

        for (let player of newPlayers) {
            this._renderPlayer(player);
        }

        for (let username of playersResult.toRemove) {
            $(`.${username}-player`).remove();
        }

        for (let player of this.game.players) {
            let cardsResult = this._filterElements(player.cards,this._getCards(player.username));

            for (let cardToRemove of cardsResult.toRemove) {
                let removedImage = document.getElementsByClassName(`${player.username}-cards`)[0].getElementsByClassName(`${cardToRemove}`)[0];
                $(removedImage).fadeOut(1000,function () {
                    removedImage.parentNode.removeChild(removedImage);
                }) }

            $(`.${player.username}-status`).html("Status : " + player.status);
            $(`.${player.username}-money`).html("Money : " + player.balance);
            $(`.${player.username}-points`).html("Points : " + player.points);

            for (let card of cardsResult.toRender) {
                $(`.${player.username}-cards`).append(`<img src="${card}" class="img-fluid my_card p-1 disappear ${card} my-card"/>`);

                $(".showCardArea").append(`<img src="${card}" class="img-fluid my_card p-1 ${card} my-card"/>`);

                let image = $(".showCardArea").find(`.my-card`);

                let y = $(".showCardArea").offset().top;
                let x = $(".showCardArea").offset().left/4;

                $(image).animate({
                    top:y,
                    left:x
                },2000,null,()=>{//500millis is important because of the timing of ajax requests.
                    $(image).remove();
                    let images = $("img");
                    for (let img of images) {
                        if ($(img).hasClass("disappear")) {
                            $(img).removeClass("disappear");
                        }
                    }
                });


            }

        }

    }

    _getCards(cssClazz){
        let cards = new Array();

        let images = document.getElementsByClassName(`${cssClazz}-cards`)[0].children;

        for (let img of images) {
            let classes = img.className.split(" ");

            cards.push( classes.filter((clazz)=> {
                return clazz.match(/imgs\/.*\.png/);
            }).shift() );

        }

        return cards;

    }

    _filterElements(newElements, oldElements){
        let keep = Array();
        let remove = Array();
        let render = Array();

        for (let oldElement of oldElements) {
            let found = false;

            for(let newElement of newElements){
                if(newElement === oldElement){
                    found = true;
                    break;
                }
            }
            if (found) {
                keep.push(oldElement);
            }else{
                remove.push(oldElement);
            }
        }

        render = newElements.filter((newElement)=>{
            return !keep.some((keepElement)=>{
               return keepElement === newElement;
            });
        });

        return {
            toRender:render,
            toRemove:remove,
            toKeep:keep
        }
    }
}
