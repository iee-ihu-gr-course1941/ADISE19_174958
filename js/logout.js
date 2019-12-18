$("#logoutBtn").click(function () {
    $.ajax("api/engine.php/logout",{success:logoutFunc,type:"GET"})
});

function logoutFunc(result, status, xhr){
    document.location.href = result;
}