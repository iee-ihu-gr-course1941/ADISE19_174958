class Game{

    constructor(status,players,points,cards) {
        this._status = status;
        this._players = players;
        this._cards = cards;
        this._points = points;
    }

    get status() {
        return this._status;
    }

    get players() {
        return this._players;
    }


    get points() {
        return this._points;
    }

    get cards() {
        return this._cards;
    }

    isPlayer(username) {
        for (let player of this.players) {
            if (player === this.players.username) {
                return true;
            }
        }
        return false;
    }

}