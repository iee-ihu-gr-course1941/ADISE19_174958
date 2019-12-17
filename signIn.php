<?php

    include_once "header.php"

?>

<div class="message alert text-center row w-100">
</div>
<div class="row w-100 justify-content-center">
    <form class="w-50 mt-2">
        <div class="form-group">
            <label for="user_name">Username:</label>
            <input id="user_name" class="form-control" placeholder="JohnDoe1000">
        </div>
        <div class="form-group">
            <label for="pass_word">Password:</label>
            <input id="pass_word" type="password" class="form-control" placeholder="***********">
        </div>
        <button id="signInBtn" class="w-100 btn btn-primary" type="button">Sign In</button>
    </form>
</div>


<script src="js/signIn.js">
</script>

<?php

include_once "footer.php"

?>