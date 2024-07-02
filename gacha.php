<?php
    session_start();
?>

<html>
    <head>
        <title>Pokemon</title>
        <link rel="stylesheet" href="gacha.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>
    <body>
        <audio id="music" loop muted>
            <source src="assets/audio/gacha.mp3" type="audio/mpeg">
        </audio>
        <canvas id="gachaCanvas"></canvas>
        <button id="backButton" onclick="mainMenu()">Back</button>
        <h2 id="moneyAmount"></h2>
        <div id="pokeAlert">
        </div>
        <div id="buttons">
            <button id="startButton" onclick="nextButtons()">Enter Shop</button>
        </div>

        <script>
            /*
            Deals with audio on the page
            */

            //Starts playing audio
            let playing = false
            sound = document.getElementById("music");
            if(playing == false)
            {
                document.onmousedown = function(){
                    sound.volume = .9;
                    sound.play();
                    sound.muted = false;
                    playing = true;
                }
            }
        </script>

        <script>
            //Reminders:
            //context.drawImage(pokeball, 0, 0, 50, 50, 0, 0, 50, 50); //First pokeball on the sheet, +40 for different ball
            //window.location.assign("NEW_PAGE_URL"); can be used to redirect to another page
            //window.location.replace("NEW_PAGE_URL"); haven't tried but better if it also works because it does not remember history

            //Gets Player Object From backend
            var player = <?php echo $_SESSION['player'] ?>;
        
            console.log(player.name);
            console.log(player.pid);
            console.log(player.money);

            var canvas = document.getElementById('gachaCanvas');
            canvas.height = window.innerHeight * .9;
            canvas.width = window.innerWidth * .95;
            var context = canvas.getContext('2d');
            context.imageSmoothingEnabled = false; //Makes image clear

            //Creates image objects for the background and pokeballs
            var background = new Image();
            var pokeball = new Image();
            background.src = "assets/images/gachaBackground.png";
            pokeball.src = "assets/images/pokeball.png";

            //Draws the background image
            background.onload = function(){
            context.drawImage(background, 0, 0, canvas.width, canvas.height);
            }
            
            //Object constructor for pokeball objects; Creates necessary objects afterwards
            function Ball(startX, startY, xVelocity, yVelocity, maxDistance)
            {
                this.x = startX;
                this.y = startY;
                this.xVel = xVelocity;
                this.yVel = yVelocity;
                this.xMax = maxDistance;
                this.frame = 0;
            }
            ball1 = new Ball(300, canvas.height, 5, 30, 800);
            ball2 = new Ball(1000, canvas.height, -5, 30, 500);
            ball3 = new Ball(500, canvas.height, 5, 30, 1000);
            ball4 = new Ball(800, canvas.height, -5, 30, 300);
            ball5 = new Ball(900, canvas.height, 5, 30, 1400);
            ball6 = new Ball(1400, canvas.height, -5, 30, 900);
            ball7 = new Ball(1100, canvas.height, 5, 30, 1600);
            ball8 = new Ball(1600, canvas.height, -5, 30, 1100);
            ball9 = new Ball(1200, canvas.height, 5, 30, 1700);
            ball10 = new Ball(500, canvas.height, -5, 30, 0);
            var ballArray = [ball1, ball2, ball3, ball4, ball5, ball6, ball7, ball8, ball9, ball10];

            //Function for throwing poke balls in the air
            function ballArc(ballArray, ballType)
            {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(background, 0, 0, canvas.width, canvas.height);
                for(let i = 0; i < 10; i++)
                {
                    context.drawImage(pokeball, ballType, ballArray[i].frame, 50, 50, ballArray[i].x, ballArray[i].y, 50, 50);
                }

                if(ballArray[0].x != ballArray[0].xMax)
                {
                    for(let i = 0; i < 10; i++)
                    {
                        ballArray[i].x += ballArray[i].xVel; ballArray[i].y -= ballArray[i].yVel; ballArray[i].yVel -= 1;
                        if(ballArray[i].frame == 320)
                        {
                            ballArray[i].frame = 0;
                        }
                        else
                        {
                            ballArray[i].frame += 40;
                        }
                    }
                    let timeout = setTimeout(ballArc, 20, ballArray, ballType);
                }
            }
            
            //Displays shop buttons after entering shop
            function nextButtons()
            {
                let currButtons = document.getElementById("buttons");
                currButtons.innerHTML = "<button id='buttonLow' onclick='lowRoll()'>Low Tier: $1000</button><button id='buttonMid' onclick='midRoll()'>Mid Tier: $5000</button><button id='buttonHigh' onclick='highRoll()'>High Tier: $10000</button>"
                //Sets the money display to show player's money
                document.getElementById('moneyAmount').innerHTML = "Money: $" + player.money;
            }

            //Hides buttons when necessary
            function hideButtons()
            {
                let currButtons = document.getElementById("buttons");
                currButtons.innerHTML = "";
            }

            //The following functions handle rolling for new pokemon based on chosen tier
            function lowRoll()
            {
                if(player.money < 1000)
                {
                    context.clearRect(0, 0, canvas.width, canvas.height)
                    context.drawImage(background, 0, 0, canvas.width, canvas.height);
                    document.getElementById("pokeAlert").innerHTML = "<h1 id=moneyAlert>Not Enough Money</h1>";
                    setTimeout(function(){document.getElementById("pokeAlert").innerHTML = "";}, 1000)
                }
                else
                {
                    player.money -= 1000;
                    document.getElementById('moneyAmount').innerHTML = "Money: $" + player.money;

                    hideButtons();
                    document.getElementById("pokeAlert").innerHTML = "";

                    let ballType = 120;

                    //Throws pokeballs in air when rolling for new pokemon then resets ball positions
                    ballArc(ballArray, ballType);
                    ball1 = new Ball(300, canvas.height, 5, 30, 800);
                    ball2 = new Ball(1000, canvas.height, -5, 30, 500);
                    ball3 = new Ball(500, canvas.height, 5, 30, 1000);
                    ball4 = new Ball(800, canvas.height, -5, 30, 300);
                    ball5 = new Ball(900, canvas.height, 5, 30, 1400);
                    ball6 = new Ball(1400, canvas.height, -5, 30, 900);
                    ball7 = new Ball(1100, canvas.height, 5, 30, 1600);
                    ball8 = new Ball(1600, canvas.height, -5, 30, 1100);
                    ball9 = new Ball(1200, canvas.height, 5, 30, 1700);
                    ball10 = new Ball(500, canvas.height, -5, 30, 0);
                    ballArray = [ball1, ball2, ball3, ball4, ball5, ball6, ball7, ball8, ball9, ball10];

                    setTimeout(drawPokemonLow, 3000,"");
                    setTimeout(nextButtons, 6000);
                }
            }
            function midRoll()
            {
                if(player.money < 5000)
                {
                    context.clearRect(0, 0, canvas.width, canvas.height)
                    context.drawImage(background, 0, 0, canvas.width, canvas.height);
                    document.getElementById("pokeAlert").innerHTML = "<h1 id=moneyAlert>Not Enough Money</h1>";
                    setTimeout(function(){document.getElementById("pokeAlert").innerHTML = "";}, 1000)
                }
                else
                {
                    player.money -= 5000;
                    document.getElementById('moneyAmount').innerHTML = "Money: $" + player.money;

                    hideButtons();
                    document.getElementById("pokeAlert").innerHTML = "";

                    let ballType = 80;

                    //Throws pokeballs in air when rolling for new pokemon then resets ball positions
                    ballArc(ballArray, ballType);
                    ball1 = new Ball(300, canvas.height, 5, 30, 800);
                    ball2 = new Ball(1000, canvas.height, -5, 30, 500);
                    ball3 = new Ball(500, canvas.height, 5, 30, 1000);
                    ball4 = new Ball(800, canvas.height, -5, 30, 300);
                    ball5 = new Ball(900, canvas.height, 5, 30, 1400);
                    ball6 = new Ball(1400, canvas.height, -5, 30, 900);
                    ball7 = new Ball(1100, canvas.height, 5, 30, 1600);
                    ball8 = new Ball(1600, canvas.height, -5, 30, 1100);
                    ball9 = new Ball(1200, canvas.height, 5, 30, 1700);
                    ball10 = new Ball(500, canvas.height, -5, 30, 0);
                    ballArray = [ball1, ball2, ball3, ball4, ball5, ball6, ball7, ball8, ball9, ball10];

                    setTimeout(drawPokemonMid, 3000,"");
                    setTimeout(nextButtons, 6000);
                }
            }
            function highRoll()
            {
                if(player.money < 10000)
                {
                    context.clearRect(0, 0, canvas.width, canvas.height)
                    context.drawImage(background, 0, 0, canvas.width, canvas.height);
                    document.getElementById("pokeAlert").innerHTML = "<h1 id=moneyAlert>Not Enough Money</h1>";
                    setTimeout(function(){document.getElementById("pokeAlert").innerHTML = "";}, 1000)
                }
                else
                {
                    player.money -= 10000;
                    document.getElementById('moneyAmount').innerHTML = "Money: $" + player.money;

                    hideButtons();
                    document.getElementById("pokeAlert").innerHTML = "";

                    let ballType = 0;

                    //Throws pokeballs in air when rolling for new pokemon then resets ball positions
                    ballArc(ballArray, ballType);
                    ball1 = new Ball(300, canvas.height, 5, 30, 800);
                    ball2 = new Ball(1000, canvas.height, -5, 30, 500);
                    ball3 = new Ball(500, canvas.height, 5, 30, 1000);
                    ball4 = new Ball(800, canvas.height, -5, 30, 300);
                    ball5 = new Ball(900, canvas.height, 5, 30, 1400);
                    ball6 = new Ball(1400, canvas.height, -5, 30, 900);
                    ball7 = new Ball(1100, canvas.height, 5, 30, 1600);
                    ball8 = new Ball(1600, canvas.height, -5, 30, 1100);
                    ball9 = new Ball(1200, canvas.height, 5, 30, 1700);
                    ball10 = new Ball(500, canvas.height, -5, 30, 0);
                    ballArray = [ball1, ball2, ball3, ball4, ball5, ball6, ball7, ball8, ball9, ball10];

                    setTimeout(drawPokemonHigh, 3000,"");
                    setTimeout(nextButtons, 6000);
                }
            }
            
            //Animation for won pokemon appearing
            function growPokemon(currSize)
            {
                if(currSize != 201)
                {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(background, 0, 0, canvas.width, canvas.height);
                    context.drawImage(pokeImage, 810, 250, currSize, currSize);
                    setTimeout(growPokemon, 2, currSize + 1);
                }
            }

            //Queries database for pokemon corresponding to random pokedex id generated from roll
            function requestPokemon(pokeNum, tier)
            {
                $.post("gachadb.php", {num:pokeNum, tier:1}, function(result){
                    let pokemon = JSON.parse(result);
                    
                    if(tier == 1)
                    {
                        drawPokemonLow(pokemon);
                    }
                    else if(tier == 2)
                    {
                        drawPokemonMid(pokemon);
                    }
                    else if(tier == 3)
                    {
                        drawPokemonHigh(pokemon);
                    }
                });
            }

            //Adds awarded pokemon to player's collection
            function addCollection(dexid, pid)
            {
                console.log(dexid);
                console.log(pid);
                $.post("gachadb.php", {pokedexNum:dexid, playerId:pid});
            }

            //Following functions deal with drawing the won pokemon
            //Each function corresponds to a different tier
            function drawPokemonLow(pokemon)
            {
                let pokeNum = Math.floor(Math.random() * 151) + 1;
                if(pokemon == "")
                {
                    requestPokemon(pokeNum, 1);
                }
                else if(pokemon.statTotal > 320)
                {
                    requestPokemon(pokeNum, 1);
                }
                else
                {
                
                    pokeImage = new Image();
                    pokeImage.onload = function(){
                        growPokemon(1);
                    }
                    pokeImage.src = "assets/images/spritesFront/" + pokemon.dexid + ".png";
                    document.getElementById('pokeAlert').innerHTML = "<h1>You got " + pokemon.name + "!</h1>"

                    addCollection(pokemon.dexid, player.pid);
                }
            }

            function drawPokemonMid(pokemon)
            {
                let pokeNum = Math.floor(Math.random() * 151) + 1;
                if(pokemon == "")
                {
                    requestPokemon(pokeNum, 2);
                }
                else if(pokemon.statTotal > 489 || pokemon.statTotal < 321)
                {
                    requestPokemon(pokeNum, 2);
                }
                else
                {
                
                    pokeImage = new Image();
                    pokeImage.onload = function(){
                        growPokemon(1);
                    }
                    pokeImage.src = "assets/images/spritesFront/" + pokemon.dexid + ".png";
                    document.getElementById('pokeAlert').innerHTML = "<h1>You got " + pokemon.name + "!</h1>"
                    addCollection(pokemon.dexid, player.pid);
                }
            }

            function drawPokemonHigh(pokemon)
            {
                let pokeNum = Math.floor(Math.random() * 151) + 1;
                if(pokemon == "")
                {
                    requestPokemon(pokeNum, 3);
                }
                else if(pokemon.statTotal < 490)
                {
                    requestPokemon(pokeNum, 3);
                }
                else
                {
                
                    pokeImage = new Image();
                    pokeImage.onload = function(){
                        growPokemon(1);
                    }
                    pokeImage.src = "assets/images/spritesFront/" + pokemon.dexid + ".png";
                    document.getElementById('pokeAlert').innerHTML = "<h1>You got " + pokemon.name + "!</h1>"
                    addCollection(pokemon.dexid, player.pid);
                }
            }

            function mainMenu()
            {
                $.post("gachadb.php", {newMoney:player.money, id:player.pid});
                window.location.replace("menu.php");
            }

            //Resizes game if window size changes
            window.onresize = function() {
            canvas.width = window.innerWidth * .95;
            canvas.height = window.innerHeight * .9;
            context.imageSmoothingEnabled = false;
            context.drawImage(background, 0, 0, canvas.width, canvas.height);
            }
        </script>   
    </body>
</html>