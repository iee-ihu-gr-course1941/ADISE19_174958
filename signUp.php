<?php

include_once "header.php"

?>

<div class="message alert text-center row w-100">
</div>

<div class="row w-100 justify-content-center">
    <form class="w-50 mt-2">
        <div class="form-group">
            <label for="user_name">
                Username:
            </label>
            <input placeholder="JohnDoe1000" id="user_name" class="form-control"/>
        </div>
        <div class="form-group">
            <label for="pass_word">
                Password:
            </label>
            <input type="password" id="pass_word" class="form-control" placeholder="**********">
        </div>
        <div class="form-group">
            <label for="pass_word2">
                Password(Again):
            </label>
            <input type="password" id="pass_word2" class="form-control" placeholder="**********">
        </div>
        <button id="signUpBtn" class="w-100 btn btn-primary" type="button">Sign Up</button>
    </form>
</div>

<script src="js/signUp.js">
</script>

<?php

include_once "footer.php"

?>
