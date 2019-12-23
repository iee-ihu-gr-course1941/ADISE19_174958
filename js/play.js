$("#playButton").click(function () {
    $.ajax("api/engine.php/join",{
        type: "GET",
        success:join()
    });

});

function join(response, status, xhr){
    setInterval(updateTables,500);
}

function updateTables(){
    
}
