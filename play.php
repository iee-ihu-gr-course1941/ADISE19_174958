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
                    <div class="d-flex flex-wrap justify-content-center mt-2 bg-white computer-cards"></div>
                </div>
            </div>
        </div>

        <div class="row splitter d-flex justify-content-center ml-0 mt-2 ">

        </div>

        <div class="row players card-deck mt-5 players">

        </div>
    </div>
</div>

<script src="js/play.js" ></script>

<?php
require_once "footer.php"
?>
