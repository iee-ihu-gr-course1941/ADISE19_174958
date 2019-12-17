$("#logoutBtn").click(function () {
    $.ajax("api/logoutAPI.php",{success:logoutFunc,type:"GET"})
});

function logoutFunc(result, status, xhr){
    document.location.href = result;
}