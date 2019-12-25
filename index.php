<?php
    require_once "header.php"
?>


<div class="row justify-content-center content gameView disappear">
    <div class="col-12 table mt-2">
        <div class="row computer ">
            <div class="col-12 d-flex justify-content-center" >
                <div class="card w-25 computer-card">
                    <div class="card-header">
                        Computer
                    </div>
                    <div class="card-body">
                        <p class="text-left computer-status">Game Status:</p>
                        <p class="text-left computer-points">Computer Points:</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row splitter">

        </div>
        <div class="row players card-deck mt-5 players">
            <div class="card disappear player">
                <div class="card-header text-center player_name">
                    Player1
                </div>
                <div class="card-body ">
                    <p class="text-left status">Status:</p>
                    <p class="text-left money">Money:</p>
                    <p class="text-left points">Points:</p>
                </div>
            </div>
            <div class="card disappear player">
                <div class="card-header text-center player_name">
                    Player1
                </div>
                <div class="card-body ">
                    <p class="text-left status">Status:</p>
                    <p class="text-left money">Money:</p>
                    <p class="text-left points">Points:</p>
                </div>
            </div>
            <div class="card disappear player">
                <div class="card-header text-center player_name">
                    Player1
                </div>
                <div class="card-body ">
                    <p class="text-left status">Status:</p>
                    <p class="text-left money">Money:</p>
                    <p class="text-left points">Points:</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    require_once "footer.php"
?>
