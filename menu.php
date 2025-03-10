<?php
    session_start();
    require_once "pdo.php";

    $player = json_decode($_SESSION['player'], true);
    $pid = $player['pid'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM PlayerCollection WHERE pid = :pid");
    $stmt->execute([':pid' => $pid]);
    $collCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT catchId1, catchId2, catchId3, catchId4, catchId5, catchId6 FROM PlayerParty WHERE pid = :pid");
    $stmt->execute([':pid' => $pid]);
    $party1 = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Main Menu</title>
        <link rel="stylesheet" href="menu.css">
    </head>
    <body>
        <audio id="music" loop>
            <source src="assets/audio/menu.mp3" type="audio/mpeg">
        </audio>
        
        <div id="screen">
            <canvas id="menuCanvas"></canvas>
            
            <div id="stats" class="info">
                <h2>Trainer Name: <span id="name"></span> Money: <span id="money"></span> Pokemon Caught: <span id="hasAMon"></span></h2>
            </div>

            <div id="header" class="info">
                <h1>WELCOME, PICK AN OPTION</h1>
            </div>
            
            <div id="message" class="info">
                <h3><span id="message"></span></h3>
            </div>

            <div id="mainSelect"> 
                <button class="battleButton" id="battleMainButton" onclick="battlePage()" disabled>BATTLE</button>
                <button class="mainButton" id="teamButton" onclick="window.location.href='party.php'">TEAM</button>
                <button class="mainButton" id="gachaButton" onclick="window.location.href='gacha.php'">GACHA</button>
            </div>

            <div id="rightCornerButton"> 
                <button class="battleButton"  id="rightCornerBattleButton" onclick="battle()">BATTLE</button>
            </div>

            <div id="leftCornerButton"> 
                <button class="backButton" id="leftCornerBackButton" onclick="back()">BACK</button>
            </div>

            <div id="trainerSelect">
            
                <div class="container">
                <div class="row">
                    <div class="col-12">
                    <table class="trainerTable">
                        <thead>
                        <tr>
                            <th scope="col">Trainer</th>
                            <th scope="col">Difficulty</th>
                            <th scope="col">Reward</th>
                            <th scope="col"># of Mons</th>
                            <th scope="col">Select</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">Youngster Timmy</th>
                                <td>Lvl. 1</td>
                                <td>$2000</td>
                                <td>2</td>
                                <td>
                                <button id="1" class="btn" onclick="select(1);" >SELECT</button>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">School Boy Rex</th>
                                <td>Lvl. 2</td>
                                <td>$4000</td>
                                <td>4</td>
                                <td>
                                <button id="2" class="btn" onclick="select(2);">SELECT</button>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Lady Carol</th>
                                <td>Lvl. 3</td>
                                <td>$10000</td>
                                <td>5</td>
                                <td>
                                <button id="3" class="btn" onclick="select(3);">SELECT</button>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Champion Cynthia</th>
                                <td>Lvl. ?</td>
                                <td>$20000</td>
                                <td>6</td>
                                <td>
                                <button id="4" class="btn" onclick="select(4);">SELECT</button>
                                </td>
                            </tr>
                            </tbody>
                    </table>
                </div>
            </div>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        
        <script>
            /*
            Deals with audio on the page
            */

            //Starts playing audio
            $(document).ready(function() {
                $("#music").prop("volume", 0.1);
                $("#music").get(0).play();
            });
        </script>

        <script>
            //Gets Player Object From backend
            var player = <?php echo $_SESSION['player'] ?>;

            const canvas = $('#menuCanvas');
            const context = canvas[0].getContext('2d');
            const box = $('#screen');
            const aspectRatio = 4/3;
            const winScale = .9;
            const buttons = $('button');
            const info = $('.info');
            const tips = [
                "Pay attention to type matchups, sum the effectiveness and defences of types.", 
                "You have a normal attack with no type and a special with your 'mon's type.", 
                "Having trouble against a trainer, try rolling the gacha again!",
                "The first pokemon in you party is the first to get sent out.",
                ""
            ];

            let hasAMon = <?php echo json_encode($collCount);?>; //# of mons in collection
            let party = <?php echo json_encode($party1);?>; //1 = true player has null in party space 1
            console.log(party);
            let money = player.money;
            let name = player.name;
            let page = 1;
            let trainerPick = 1;

            console.log(hasAMon, player);

            function resize(){
                    let newWidth, newHeight;

                    if ($(window).width() / $(window).height() > aspectRatio) {
                        newHeight = winScale * $(window).height();
                        newWidth = winScale * $(window).height() * aspectRatio;
                    } else {
                        newWidth = winScale * $(window).width();
                        newHeight = winScale * $(window).width() / aspectRatio;
                    }

                    canvas.attr('width', newWidth);
                    canvas.attr('height', newHeight);
                    canvas.css({ width: newWidth, height: newHeight });
                    box.css({ width: newWidth, height: newHeight });

                    const buttonScale = newWidth / 400;
                    buttons.css({
                        'font-size': `${17 * buttonScale}px`,
                        'padding': `${7 * buttonScale}px ${25 * buttonScale}px`,
                        'border-radius': `${18 * buttonScale}px`
                    });
                    info.css({
                        'font-size': `${8 * buttonScale}px`
                    });
                    $('.btn').css({
                        'font-size': `${6 * buttonScale}px`
                    });
            }

            function battlePage() {
                page = 2;
                $('#mainSelect').hide();
                $('#message').hide();
                $('#header').hide();
                $('#rightCornerButton').show();
                $('#trainerSelect').show();
                $('#1').prop('disabled', true);
            }

            function back(){
                if (page == 1){
                    window.location.href='proj3.html'
                } else if (page == 2) {
                    $('#rightCornerButton').hide();
                    $('#trainerSelect').hide();
                    $('#mainSelect').show();
                    $('#message').show();
                    $('#header').show();
                    page = 1;
                }
            }
            // Conditions. No pokemon in party. Rank based message (champion). Otherwise Random Trainer Tips
            function message() {
                if (hasAMon == 0){
                    $('#message').text("Click GACHA to get your first mon!");
                } else if (!(party.catchId1||party.catchId2||party.catchId3||party.catchId4||party.catchId5||party.catchId6)) {
                    $('#message').text("Your Party is empty! Click TEAM to manage your party");
                } else if (player.highestTrainer == 0) {
                    $('#message').text("Looks like you're all set, " + player.name + ". Go get 'em tiger!");
                } else if (player.highestTrainer == 4) {
                    $('#message').text("CONGRATS CHAMPION!!!");
                } else {
                    Math.floor(Math.random() * tips.length);
                    $('#message').text(tips[Math.floor(Math.random() * tips.length)]);
                }

            } 
            
            function select(i) {
                $('.btn').prop('disabled', false);
                $('.btn').css("background-color: white");
                $('#'+i).prop('disabled', true);
                $('#'+i).css("background-color: grey");
                

                trainerPick = i;
            }

            function battle() {
                var input = {
                    trainer: trainerPick
                };
                $.ajax({
                    url: 'battle.php',
                    method: 'POST',
                    data: input,
                    success: function(response) {
                        console.log('Session variable set successfully');
                        // Optionally handle success response from server
                    },
                    error: function(xhr, status, error) {
                        console.error('Error setting session variable:', error);
                        // Optionally handle error response
                    }
                });


                //console.log(.);
                window.location.href='battle.php'
            }

            resize();
            message();
            $('#rightCornerButton').hide();
            $('#trainerSelect').hide();
            $('#hasAMon').text(hasAMon); //displays # of mons in collection
            $('#money').text(money); 
            $('#name').text(name.toUpperCase()); 
            
            if(hasAMon != 0 && (party.catchId1||party.catchId2||party.catchId3||party.catchId4||party.catchId5||party.catchId6)){
                $('#battleMainButton').prop('disabled', false);
            }

        </script>