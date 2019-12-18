$("#playButton").click(function () {
    $.ajax("api/engine.php/join",{
        type: "GET",
        success:join()
    });

});

function join(game, status, xhr){
    prepareTable();
}

function prepareTable(){
    
}
