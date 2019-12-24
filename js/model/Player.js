class Player{

    constructor(username, cards, points, status, balance) {
        this._username = username;
        this._cards = cards;
        this._points = points;
        this._status = status;
        this._balance = balance;
    }


    get username() {
        return this._username;
    }

    get cards() {
        return this._cards;
    }

    get points() {
        return this._points;
    }

    get status() {
        return this._status;
    }

    get balance() {
        return this._balance;
    }
}