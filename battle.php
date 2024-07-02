<?php
    session_start();

    if(isset($_POST['trainer']))
    {
        $_SESSION['trainer'] = $_POST['trainer'];
    }
?>

<html>
    <head>
        <title>Pokemon</title>
        <link rel="stylesheet" href="battle.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>

    <body>
        <canvas id="battleCanvas"></canvas>
        <div id="startBattle">
            <button id="start" onclick="startBattle()">Begin Battle</button>
        </div>
        <div id="userInterface">
            <div id="clickedMenu"></div>
            <div id="controlButtons"></div>
            <div id="buttonInfo"></div>
            <div id="healthInfo"></div>
        </div>
        
        <script>
            //Reminders
            //Base position for player pokemon: context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
            //Base position for computer pokemon: context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);
            var trainerChallenged = <?php echo $_SESSION['trainer'] ?>; //The current trainer that the player is challenging
            supereffective = new Audio();
            normaleffective = new Audio();
            noteffective = new Audio();
            supereffective.src = "assets/audio/supereffective.mp3"
            normaleffective.src = "assets/audio/normaleffective.mp3"
            noteffective.src = "assets/audio/noteffective.mp3"

            var canvas = document.getElementById('battleCanvas');
            canvas.height = window.innerHeight * .9;
            canvas.width = window.innerWidth * .95;
            var context = canvas.getContext('2d');
            context.imageSmoothingEnabled = false; //Makes image clear

            //Creates image objects for the background
            var background = new Image();

            function drawBackground()
            {
                context.drawImage(background, 0, 0, canvas.width / 1.25 , canvas.height);
                context.fillStyle = "rgb(171,171,171,100)";
                context.fillRect(canvas.width / 1.25, 0, canvas.width, canvas.height);
            }

            //Draws the background image, leaving room for controls on the right side
            background.onload = function(){
                drawBackground();
            }
            //Unique background for champion
            if(trainerChallenged != 4)
            {
                background.src = "assets/images/battleBackground/" + (Math.floor(Math.random()*10) + 2)+ ".png";
            }
            else
            {
                background.src = "assets/images/battleBackground/1.png";
            }

            //Some important variables
            var player = <?php echo $_SESSION['player'] ?>;             //Player object containing player's info
            var playerParty = [];                                       //Pokemon in player's party
            var playerPartyTotal = 0;                                   //Total pokemon in party, decreases when fainting
            var playerCurrPokemon = "";                                 //The current Pokemon on the field
            var compParty = [];                                         //Rest are comps euqivalent
            var compPartyTotal = 0;
            var compCurrNum = 0;                                        //Number of opponents pokemon defeated
            var compCurrPokemon = "";
            var switchedAlready = false;                                //Determines if the player has switched pokemon this turn already

            //Gets player and opponent parties
            function generateParties()
            {
                $.post("battledb.php", {id:player.pid}, function(result){
                    playerParty = result.split(" ");
                    playerParty.pop();
                    playerPartyTotal = playerParty.length;

                    for(i = 0; i < playerPartyTotal; i++)
                    {
                        playerParty[i] = JSON.parse(playerParty[i]);
                        playerParty[i].currHealth = Math.floor(0.01 * (2 * playerParty[i].hp) * 50) + 50 + 10;
                        playerParty[i].hp = playerParty[i].currHealth;
                        playerParty[i].atk = (Math.floor(0.01 * (2 * playerParty[i].atk) * 50) + 5);
                        playerParty[i].def = (Math.floor(0.01 * (2 * playerParty[i].def) * 50) + 5);
                        playerParty[i].spatk = (Math.floor(0.01 * (2 * playerParty[i].spatk) * 50) + 5);
                        playerParty[i].spdef = (Math.floor(0.01 * (2 * playerParty[i].spdef) * 50) + 5);
                        playerParty[i].spd = (Math.floor(0.01 * (2 * playerParty[i].spd) * 50) + 5); 
                    }

                });

                $.post("battledb.php", {needComp:true}, function(result){
                    compParty = result.split(" ");
                    compParty.pop();
                    compPartyTotal = compParty.length;

                    for(i = 0; i < compPartyTotal; i++)
                    {
                        compParty[i] = JSON.parse(compParty[i]);
                        compParty[i].currHealth = Math.floor(0.01 * (2 * compParty[i].hp) * 50) + 50 + 10;
                        compParty[i].hp = compParty[i].currHealth;
                        compParty[i].atk = (Math.floor(0.01 * (2 * compParty[i].atk) * 50) + 5);
                        compParty[i].def = (Math.floor(0.01 * (2 * compParty[i].def) * 50) + 5);
                        compParty[i].spatk = (Math.floor(0.01 * (2 * compParty[i].spatk) * 50) + 5);
                        compParty[i].spdef = (Math.floor(0.01 * (2 * compParty[i].spdef) * 50) + 5);
                        compParty[i].spd = (Math.floor(0.01 * (2 * compParty[i].spd) * 50) + 5); 
                    }
                });
            }

            var pokemonPlayer = new Image();
            var pokemonComp = new Image();
            //Starts the battle, starts playing music and first pokemon are thrown out
            function startBattle()
            {
                //Gets rid start button and shows controls on righthand side
                document.getElementById('startBattle').innerHTML="";
                document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";

                //Starts playing battle music
                song = new Audio();

                //Plays different music when fighting champion
                if(trainerChallenged != 4)
                {
                    song.src = "assets/audio/battle_" + (Math.floor(Math.random() * 5) + 1)+ ".mp3";
                }
                else
                {
                    song.src = "assets/audio/battle_" + 6 + ".mp3";
                }
                song.loop = true;
                song.volume = .8;
                song.play();

                //Pulls player party from backend and generates oponnent party
                generateParties();

                setTimeout(function(){
                pokemonPlayer.onload = function(){
                context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                }

                pokemonComp.onload = function(){
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);
                }
                
                setTimeout(function(){
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerParty[0].dexid+".png";
                    pokemonComp.src = "assets/images/spritesFront/"+compParty[0].dexid+".png";

                    playerCurrPokemon = playerParty[0];
                    compCurrPokemon = compParty[0];

                    console.log(playerParty); //For Testing
                    console.log(compParty); //For Testing

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";
                }, 300);

                }, 5);
            }

            //Deals with displaying control button info
            function attackInfo()
            {
                let buttoDiv = document.getElementById('buttonInfo');
                buttoDiv.innerHTML = "<h2>Choose an attack for the current pokemon</h2>"
            }
            function switchInfo()
            {
                let buttoDiv = document.getElementById('buttonInfo');
                buttoDiv.innerHTML = "<h2>Switch your currently deployed pokemon</h2>"
            }
            function dismissInfo()
            {
                let buttoDiv = document.getElementById('buttonInfo');
                buttoDiv.innerHTML = ""
            }

            //Options for attacking
            function attackOptions()
            {
                //Displays attacking options and confirmation button
                let menu = document.getElementById('clickedMenu');
                menu.innerHTML = "";
                menu.innerHTML = 
                "<h2>Attack Options</h2>"+
                "<label for=normal>Normal Attack:</label>"+
                "<input type=radio name=attackType value=Normal Attack checked=checked><br>"+
                "<label for=special>Special Attack (" + playerCurrPokemon.type1 +" " + playerCurrPokemon.type2 + "):</label>"+
                "<input type=radio name=attackType value=Special Attack><br>"+
                "<button id=launchAttack onclick=launchAttack()>Launch Attack</button>";
            }

            //Sorry for the mountain of conditionals, I hate it as well
            //Function for determining type mathcup damage bonus
            //Capped at 4x/.25x due to very specific pokemon matchups being unbalanced
            function typeMatchup(attacker, defender)
            {
                let playerTypeWeakness = 1;

                if(attacker.type1 == "Fire" || attacker.type2 == "Fire")
                {
                    if(defender.type1 == "Fire" || defender.type2 == "Fire")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Water" || defender.type2 == "Water")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Dragon" || defender.type2 == "Dragon")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Ice" || defender.type2 == "Ice")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")defender
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    
                }
                if(attacker.type1 == "Water" || attacker.type2 == "Water")
                {
                    if(defender.type1 == "Water" || defender.type2 == "Water")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Dragon" || defender.type2 == "Dragon")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Fire" || defender.type2 == "Fire")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Ground" || defender.type2 == "Ground")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Electric" || attacker.type2 == "Electric")
                {
                    if(defender.type1 == "Electric" || defender.type2 == "Electric")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Dragon" || defender.type2 == "Dragon")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Water" || defender.type2 == "Water")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Flying" || defender.type2 == "Flying")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Grass" || attacker.type2 == "Grass")
                {
                    if(defender.type1 == "Fire" || defender.type2 == "Fire")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Poison" || defender.type2 == "Poison")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Flying" || defender.type2 == "Flying")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Dragon" || defender.type2 == "Dragon")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Water" || defender.type2 == "Water")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Ground" || defender.type2 == "Ground")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Ice" || attacker.type2 == "Ice")
                {
                    if(defender.type1 == "Water" || defender.type2 == "Water")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Ice" || defender.type2 == "Ice")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Ground" || defender.type2 == "Ground")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Flying" || defender.type2 == "Flying")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Dragon" || defender.type2 == "Dragon")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Fighting" || attacker.type2 == "Fighting")
                {
                    if(defender.type1 == "Poison" || defender.type2 == "Poison")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Flying" || defender.type2 == "Flying")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Psychic" || defender.type2 == "Psychic")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Normal" || defender.type2 == "Normal")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Ice" || defender.type2 == "Ice")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Poison" || attacker.type2 == "Poison")
                {
                    if(defender.type1 == "Poison" || defender.type2 == "Poison")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Ground" || defender.type2 == "Ground")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Ghost" || defender.type2 == "Ghost")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Ground" || attacker.type2 == "Ground")
                {
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Fire" || defender.type2 == "Fire")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Electric" || defender.type2 == "Electric")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Poison" || defender.type2 == "Poison")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Flying" || attacker.type2 == "Flying")
                {
                    if(defender.type1 == "Electric" || defender.type2 == "Electric")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Rock" || defender.type2 == "Rock")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Fighting" || defender.type2 == "Fighting")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Psychic" || attacker.type2 == "Psychic")
                {
                    if(defender.type1 == "Psychic" || defender.type2 == "Psychic")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Fighting" || defender.type2 == "Fighting")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Poison" || defender.type2 == "Poison")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Bug" || attacker.type2 == "Bug")
                {
                    if(defender.type1 == "Fire" || defender.type2 == "Fire")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Fighting" || defender.type2 == "Fighting")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Flying" || defender.type2 == "Flying")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Ghost" || defender.type2 == "Ghost")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Grass" || defender.type2 == "Grass")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Poison" || defender.type2 == "Poison")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Psychic" || defender.type2 == "Psychic")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Rock" || attacker.type2 == "Rock")
                {
                    if(defender.type1 == "Fighting" || defender.type2 == "Fighting")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Ground" || defender.type2 == "Ground")
                    {
                        playerTypeWeakness = playerTypeWeakness * .5;
                    }
                    if(defender.type1 == "Fire" || defender.type2 == "Fire")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Ice" || defender.type2 == "Ice")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Flying" || defender.type2 == "Flying")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                    if(defender.type1 == "Bug" || defender.type2 == "Bug")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Ghost" || attacker.type2 == "Ghost")
                {
                    if(defender.type1 == "Ghost" || defender.type2 == "Ghost")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }
                if(attacker.type1 == "Dragon" || attacker.type2 == "Dragon")
                {
                    if(defender.type1 == "Dragon" || defender.type2 == "Dragon")
                    {
                        playerTypeWeakness = playerTypeWeakness * 2;
                    }
                }

                return playerTypeWeakness;
            }

            function victoryReturn()
            {
                $.post("battledb.php", {trainerDefeated:trainerChallenged, id:player.pid});
                window.location.replace("menu.php");
            }

            function defeatReturn()
            {
                window.location.replace("menu.php");
            }
            //Will display victory screen with prompt to return to main menu
            function victoryScreen()
            {
                let reward = "";
                if(trainerChallenged == 1)
                {
                    reward = "$2000"
                }
                else if(trainerChallenged == 2)
                {
                    reward = "$4000"
                }
                else if(trainerChallenged == 3)
                {
                    reward = "$10000"
                }
                else if(trainerChallenged == 4)
                {
                    reward = "$20000"
                }

                document.getElementById('userInterface').innerHTML = "";
                document.getElementById('startBattle').style.backgroundColor = "white";
                document.getElementById('startBattle').style.padding = "20px";
                document.getElementById('startBattle').innerHTML = "<h1>Victory!</h1><h1>Reward: " + reward + "</h1><button class=returnButton onclick=victoryReturn()>Return to Main Menu</button>"

            }

            //Will display defeat screen with prompt to return to main menu
            function defeatScreen()
            {
                document.getElementById('userInterface').innerHTML = "";
                document.getElementById('startBattle').innerHTML = "<h1>Defeat</h1><button class=returnButton onclick=defeatReturn()>Return to Main Menu</button>";
            }

            //Lets the player choose a new pokemon if theirs faints
            function playerFaintSwitch()
            {
                document.getElementById('controlButtons').innerHTML = "";
                switchOptions();
            }

            //Switches computer pokemon when fainting
            function compFaintSwitch()
            {
                compCurrPokemon = compParty[compCurrNum];

                context.clearRect(0, 0, canvas.width, canvas.height);
                drawBackground();
                context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);

                pokemonComp.onload = function(){
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);
                }
                pokemonComp.src = "assets/images/spritesFront/"+compCurrPokemon.dexid+".png";

            }

            //Animation for player attacking
            //context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600); starting player position
            function playerAttackAnimation(xStart, yStart, returning)
            {
                if(xStart != 100 || (xStart == 100 && returning == false))
                {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, xStart, yStart, 600, 600);

                    if(returning == true)
                    {
                        setTimeout(playerAttackAnimation, 25, xStart - 10, yStart + 1, returning);
                    }
                    else if(xStart > 149 && returning == false)
                    {
                        setTimeout(playerAttackAnimation, 25, xStart + 10, yStart - 1, true);
                    }
                    else if(returning == false)
                    {
                        setTimeout(playerAttackAnimation, 25, xStart + 10, yStart - 1, returning);
                    }
                }
                else
                {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);
                }
            }

            //Animation for comp attacking
            //context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400); starting comp position
            function compAttackAnimation(xStart, yStart, returning)
            {
                if(xStart != 900 || (xStart == 900 && returning == false))
                {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    context.drawImage(pokemonComp, 0, 0, 96, 90, xStart, yStart, 400, 400);

                    if(returning == true)
                    {
                        setTimeout(compAttackAnimation, 25, xStart + 20, yStart - 2, returning);
                    }
                    else if(xStart < 799 && returning == false)
                    {
                        setTimeout(compAttackAnimation, 25, xStart - 20, yStart + 2, true);
                    }
                    else if(returning == false)
                    {
                        setTimeout(compAttackAnimation, 25, xStart - 20, yStart + 2, returning);
                    }
                }
                else
                {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);
                }
            }

            //Attacks opposing pokemon. Whoever goes first is based on speed stat
            function launchAttack()
            {
                console.log(compPartyTotal); // testing

                //Hides Controls on right side when attack is launched
                document.getElementById("healthInfo").innerHTML = "";
                document.getElementById("controlButtons").innerHTML = "";
                //Allows player to switch again next turn
                switchedAlready = false;
                
                //Sees if users picked normal or special attack option and adjusts attack power accordingly
                let attackOptions = document.getElementsByName("attackType");
                let attackType = "normal";

                let powerPlayer = 50;
                if(attackOptions[1].checked == true)
                {
                    attackType = "special";
                    powerPlayer = 90;
                }

                let powerComp = 50;
                if(Math.floor(Math.random() * 10) + 1 >= 6)
                {
                    powerComp = 90;
                }

                document.getElementById('clickedMenu').innerHTML = "";

                //All pokemon levels will be set to 50
                let level = 50;

                //gets attack/defense stats to use based on which is better for the current pokemon
                let attackStatPlayer = 0;
                let attackStatComp = 0;
                let defenseStatPlayer = 0;
                let defenseStatComp = 0;
                if(playerCurrPokemon.atk >= playerCurrPokemon.spatk)
                {
                    attackStatPlayer = playerCurrPokemon.atk;
                    defenseStatComp = compCurrPokemon.def;
                }
                else
                {
                    attackStatPlayer = playerCurrPokemon.spatk;
                    defenseStatComp = compCurrPokemon.spdef;
                }

                if(compCurrPokemon.atk >= compCurrPokemon.spatk)
                {
                    attackStatComp = compCurrPokemon.atk;
                    defenseStatPlayer = playerCurrPokemon.def;
                }
                else
                {
                    attackStatComp = compCurrPokemon.spatk;
                    defenseStatPlayer = playerCurrPokemon.spdef;
                }

                //decides if the attacks will crit
                let critPlayer = 1;
                let critComp = 1;
                let critRollPlayer = Math.floor(Math.random() * 100) + 1;
                let critRollComp = Math.floor(Math.random() * 100) + 1;
                if(critRollPlayer >= 95)
                {
                    critPlayer = 2;
                }
                if(critRollComp >= 95)
                {
                    critComp = 2;
                }

                //Deals with type weakness matchups, simplified a little from regular pokemon
                let playerTypeWeakness = 1;
                if(attackType == "special")
                {
                    playerTypeWeakness = typeMatchup(playerCurrPokemon, compCurrPokemon);
                }

                let compTypeWeakness = 1;
                if(powerComp == 90)
                {
                    compTypeWeakness = typeMatchup(compCurrPokemon, playerCurrPokemon);
                }

                if(playerTypeWeakness > 4)
                {
                    playerTypeWeakness = 4;
                }
                else if(playerTypeWeakness < .25)
                {
                    playerTypeWeakness = .25;
                }

                if(compTypeWeakness > 4)
                {
                    compTypeWeakness = 4;
                }
                else if(compTypeWeakness < .25)
                {
                    compTypeWeakness = .25;
                }

                //Adds variation to the attack damage
                let playerVariation = (Math.floor(Math.random() * 16) + 85) / 100;
                let compVariation = (Math.floor(Math.random() * 16) + 85) / 100;

                //Uses gathered stats to have pokemon attack eachother
                if(playerCurrPokemon.spd >= compCurrPokemon.spd)
                {
                    let playerDamage = Math.floor((((((2 * level * critPlayer) / 5) * powerPlayer * (attackStatPlayer / defenseStatComp)) / 50) + 2) * playerTypeWeakness * playerVariation);
                    let compDamage = Math.floor((((((2 * level * critComp) / 5) * powerComp * (attackStatComp / defenseStatPlayer)) / 50) + 2) * compTypeWeakness * compVariation);

                    playerAttackAnimation(100, 350, false);
                    //attack sound effect
                    if(playerTypeWeakness > 1)
                    {
                        supereffective.play();
                    }
                    else if(playerTypeWeakness < 1)
                    {
                        noteffective.play();
                    }
                    else
                    {
                        normaleffective.play();
                    }

                    compCurrPokemon.currHealth -= playerDamage;
                    if(compCurrPokemon.currHealth <= 0)
                    {
                        compCurrPokemon.currHealth = 0;
                        compCurrNum += 1;

                        //Plays music if nearing victory when fighting the champion
                        if(trainerChallenged == 4 && compCurrNum == 5)
                        {
                            nearVictoryMusic = new Audio();
                            nearVictoryMusic.src = "assets/audio/tera_raid.mp3"
                            nearVictoryMusic.loop = true;
                            nearVictoryMusic.volume = .8;
                            song.pause();
                            nearVictoryMusic.play();
                        }

                        if(compCurrNum == compPartyTotal)
                        {
                            victoryScreen();
                        }
                        else
                        {
                            compFaintSwitch();
                        }
                    }
                    else
                    {
                        setTimeout(function(){
                            compAttackAnimation(900, 190, false);
                            if(compTypeWeakness > 1)
                            {
                                supereffective.play();
                            }
                            else if(compTypeWeakness < 1)
                            {
                                noteffective.play();
                            }
                            else
                            {
                                normaleffective.play();
                            }

                            playerCurrPokemon.currHealth -= compDamage;
                            if(playerCurrPokemon.currHealth <= 0)
                            {
                                playerCurrPokemon.currHealth = 0;
                                playerPartyTotal -= 1;
                            }

                            if(playerPartyTotal == 0)
                            {
                                defeatScreen();
                            }
                            else if(playerCurrPokemon.currHealth == 0)
                            {
                                setTimeout(playerFaintSwitch, 700);
                            }
                        }, 2000);
                    }
                }
                else
                {
                    let playerDamage = Math.floor((((((2 * level * critPlayer) / 5) * powerPlayer * (attackStatPlayer / defenseStatComp)) / 50) + 2) * playerTypeWeakness * playerVariation);
                    let compDamage = Math.floor((((((2 * level * critComp) / 5) * powerComp * (attackStatComp / defenseStatPlayer)) / 50) + 2) * compTypeWeakness * compVariation);

                    //First deals damage to player
                    compAttackAnimation(900, 190, false);
                    if(compTypeWeakness > 1)
                    {
                        supereffective.play();
                    }
                    else if(compTypeWeakness < 1)
                    {
                        noteffective.play();
                    }
                    else
                    {
                        normaleffective.play();
                    }

                    playerCurrPokemon.currHealth -= compDamage;
                    if(playerCurrPokemon.currHealth <= 0)
                    {
                        playerPartyTotal -= 1;
                        playerCurrPokemon.currHealth = 0;
                    }

                    //If player faints, switches pokemon; attacks otherwise
                    if(playerPartyTotal == 0)
                    {
                        defeatScreen()
                    }
                    else if(playerCurrPokemon.currHealth == 0)
                    {
                        setTimeout(playerFaintSwitch, 2600);
                    }
                    else
                    {
                        setTimeout(function(){
                            playerAttackAnimation(100, 350, false);
                            if(playerTypeWeakness > 1)
                            {
                                supereffective.play();
                            }
                            else if(playerTypeWeakness < 1)
                            {
                                noteffective.play();
                            }
                            else
                            {
                                normaleffective.play();
                            }

                            compCurrPokemon.currHealth -= playerDamage;
                            if(compCurrPokemon.currHealth <= 0)
                            {
                                
                                compCurrPokemon.currHealth = 0;
                                compCurrNum += 1;

                                //Plays music if nearing victory when fighting the champion
                                if(trainerChallenged == 4 && compCurrNum == 5)
                                {
                                    nearVictoryMusic = new Audio();
                                    nearVictoryMusic.src = "assets/audio/tera_raid.mp3"
                                    nearVictoryMusic.loop = true;
                                    nearVictoryMusic.volume = .8;
                                    song.pause();
                                    nearVictoryMusic.play();
                                }

                                if(compCurrNum == compPartyTotal)
                                {
                                    victoryScreen();
                                }
                                else
                                {
                                    compFaintSwitch();
                                }
                            }
                        }, 2000);
                    }
                }
                
                
                setTimeout(function(){
                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById("controlButtons").innerHTML = "<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";
            
                },2500);
                //testing
                console.log("player type adv: "+ playerTypeWeakness);
                console.log("comp type adv: "+ compTypeWeakness);
            }


            //Functions for switching active pokemon
            function switch1()
            {
                if(switchedAlready == true)
                {
                    document.getElementById('clickedMenu').innerHTML = "<h1>You've already switched this turn!</h1>";
                }
                else
                {
                    playerCurrPokemon = playerParty[0];

                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();

                    pokemonPlayer.onload = function(){
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    }
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerCurrPokemon.dexid+".png";
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById('clickedMenu').innerHTML = "";

                    //Makes sure if switching from fainting buttons reappear
                    document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";
                    switchedAlready = true;
                }

            }
            function switch2()
            {
                if(switchedAlready == true)
                {
                    document.getElementById('clickedMenu').innerHTML = "<h1>You've already switched this turn!</h1>";
                }
                else
                {
                    playerCurrPokemon = playerParty[1];

                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    
                    pokemonPlayer.onload = function(){
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    }
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerCurrPokemon.dexid+".png";
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById('clickedMenu').innerHTML = "";

                    //Makes sure if switching from fainting buttons reappear
                    document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";

                    switchedAlready = true;
                }
            }
            function switch3()
            {
                if(switchedAlready == true)
                {
                    document.getElementById('clickedMenu').innerHTML = "<h1>You've already switched this turn!</h1>";
                }
                else
                {
                    playerCurrPokemon = playerParty[2];

                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    
                    pokemonPlayer.onload = function(){
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    }
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerCurrPokemon.dexid+".png";
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById('clickedMenu').innerHTML = "";

                    //Makes sure if switching from fainting buttons reappear
                    document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";

                    switchedAlready = true;
                }
            }
            function switch4()
            {
                if(switchedAlready == true)
                {
                    document.getElementById('clickedMenu').innerHTML = "<h1>You've already switched this turn!</h1>";
                }
                else
                {
                    playerCurrPokemon = playerParty[3];

                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    
                    pokemonPlayer.onload = function(){
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    }
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerCurrPokemon.dexid+".png";
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById('clickedMenu').innerHTML = "";

                    //Makes sure if switching from fainting buttons reappear
                    document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";

                    switchedAlready = true;
                }
            }
            function switch5()
            {
                if(switchedAlready == true)
                {
                    document.getElementById('clickedMenu').innerHTML = "<h1>You've already switched this turn!</h1>";
                }
                else
                {
                    playerCurrPokemon = playerParty[4];

                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    
                    pokemonPlayer.onload = function(){
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    }
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerCurrPokemon.dexid+".png";
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById('clickedMenu').innerHTML = "";

                    //Makes sure if switching from fainting buttons reappear
                    document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";

                    switchedAlready = true;
                }
            }
            function switch6()
            {
                if(switchedAlready == true)
                {
                    document.getElementById('clickedMenu').innerHTML = "<h1>You've already switched this turn!</h1>";
                }
                else
                {
                    playerCurrPokemon = playerParty[5];

                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawBackground();
                    
                    pokemonPlayer.onload = function(){
                    context.drawImage(pokemonPlayer, 0, 0, 96, 90, 100, 350, 600, 600);
                    }
                    pokemonPlayer.src = "assets/images/spritesBack/"+playerCurrPokemon.dexid+".png";
                    context.drawImage(pokemonComp, 0, 0, 96, 90, 900, 190, 400, 400);

                    document.getElementById("healthInfo").innerHTML =
                    "<h1>" + playerCurrPokemon.name +": " + playerCurrPokemon.currHealth + "\/" + playerCurrPokemon.hp + " HP</h1><h1>" 
                    + compCurrPokemon.name +": " + compCurrPokemon.currHealth + "\/" + compCurrPokemon.hp + " HP</h1>";

                    document.getElementById('clickedMenu').innerHTML = "";

                    //Makes sure if switching from fainting buttons reappear
                    document.getElementById('controlButtons').innerHTML="<button id=attack onclick=attackOptions() onmouseover=attackInfo() onmouseout=dismissInfo()>Attack</button><button id=switch onclick=switchOptions() onmouseover=switchInfo() onmouseout=dismissInfo()>Pokemon</button>";

                    switchedAlready = true;
                }
            }

            //Options for switching
            function switchOptions()
            {
                let menu = document.getElementById('clickedMenu');
                let menuhtml = ""

                for(i = 0; i < playerParty.length; i++)
                {
                    menuhtml += "<h4>" + playerParty[i].name + ": " + playerParty[i].currHealth + "\/" + playerParty[i].hp;
                    if(playerParty[i] != playerCurrPokemon && playerParty[i].currHealth != 0)
                    {
                        menuhtml += " HP<button id=switch" + (i + 1) + " onclick=switch" + (i + 1) + "()>Switch</button><h4>";
                    }
                    else
                    {
                        menuhtml += " HP<h4>";
                    }
                }
                menu.innerHTML = menuhtml;
            }
        </script>
    </body>
</html>