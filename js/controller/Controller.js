class Controller {

    constructor(token, username, game) {
        this.game = game;
        this.view = new View(game);
        this.token = token;
        this.username = username;
        this.view.render();
        this.updater = setInterval(this._startUpdater);
    }

    _startUpdater(){
        $.ajax("api/engine.php/game",{success:this._updateGame})
    }

    _stopUpdater(){
        clearInterval(this.updater);
    }

    _updateGame(response){
        this.game = new Game(response.status, response.players, response.points, response.cards);
        this.view.update(this.game);
        if (!this.game.isPlayer(this.username)) {
            this.view.close();
            this._stopUpdater();
        }
        this._play();
    }

    _play(){
        switch (this.game.status) {
            case "betting":
                this._tryBetting();
                break;
            case "players_turn":
                this._tryHitting();
                break;
        }
    }

    _tryBetting(){

    }

    _tryHitting(){

    }

}