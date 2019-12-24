class View {

    constructor(game) {
        this.game = game;
    }

    render(){
        this._clear();
        this._renderGame();

        for (let card of this.game.cards) {
            $(".computer-cards").append(`<img src="${card}" class="img-fluid my_card p-1"/>`);
        }

        for (let player of this.game.players) {
            this._renderPlayer(player);
        }

    }

    close(){
        this._clear();
    }

    _renderGame(){
        $(".gameView").removeClass(".disappear");
        $(".computer-status").html("Game's Status : " + this.game.status);
        $(".computer-points").html("Points : " + this.game.points);
    }

    _clear(){
        $(".computer-status").empty();
        $(".computer-points").empty();
        $(".computer-cards").empty();
        $(".players").empty();
    }

    _renderPlayer(player){
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
            $(`${player.username}-status`).html("Status : "+player.status);
            $(`${player.username}-money`).html("Money : "+player.balance);
            $(`${player.username}-points`).html("Money : "+player.points);
        }

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