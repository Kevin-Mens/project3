<html>
    <head>
        <title>Pokemon</title>
        <link rel="stylesheet" href="gacha.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>
    <body>
        <audio id="music" loop muted>
            <source src="audio/gacha.mp3" type="audio/mpeg">
        </audio>
        <canvas id="gachaCanvas"></canvas>
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

            var canvas = document.getElementById('gachaCanvas');
            canvas.height = window.innerHeight * .9;
            canvas.width = window.innerWidth * .95;
            var context = canvas.getContext('2d');
            context.imageSmoothingEnabled = false; //Makes image clear

            //Creates image objects for the background and pokeballs
            var background = new Image();
            var pokeball = new Image();
            background.src = "images/gachaBackground.png";
            pokeball.src = "images/pokeball.png";

            //Draws the background image
            background.onload = function(){
            context.drawImage(background, 0, 0, canvas.width, canvas.height);
            }
            
            //Object constructor for pokeball objects
            function Ball(startX, startY, xVelocity, yVelocity, maxDistance)
            {
                this.x = startX;
                this.y = startY;
                this.xVel = xVelocity;
                this.yVel = yVelocity;
                this.xMax = maxDistance;
                this.frame = 0;
            }
            ball1 = new Ball(300, canvas.height, 8, 30, 800);
            ball2 = new Ball(1000, canvas.height, -8, 30, 500);
            ball3 = new Ball(500, canvas.height, 8, 30, 1000);
            ball4 = new Ball(800, canvas.height, -8, 30, 300);
            ball5 = new Ball(900, canvas.height, 8, 30, 1400);
            ball6 = new Ball(1400, canvas.height, -8, 30, 900);
            ball7 = new Ball(1100, canvas.height, 8, 30, 1600);
            ball8 = new Ball(1600, canvas.height, -8, 30, 1100);
            ball9 = new Ball(1200, canvas.height, 8, 30, 1700);
            ball10 = new Ball(500, canvas.height, -8, 30, 0);
            var ballArray = [ball1, ball2, ball3, ball4, ball5, ball6, ball7, ball8, ball9, ball10];

            //Function for throwing poke balls in the air
            function ballArc(ballArray)
            {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(background, 0, 0, canvas.width, canvas.height);
                for(let i = 0; i < 10; i++)
                {
                    context.drawImage(pokeball, 0, ballArray[i].frame, 50, 50, ballArray[i].x, ballArray[i].y, 50, 50);
                }

                if(ball1.x != ball1.xMax)
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
                    let timeout = setTimeout(ballArc, 20, ballArray);
                }
            }
            
            //Throws pokeballs in air when rolling for new pokemon
            pokeball.onload = function()
            {
                ballArc(ballArray);
            }

            function nextButtons()
            {
                let currButtons = document.getElementById("buttons");
                currButtons.innerHTML = "<button id='buttonLow'>Low Tier: PRICE</button><button id='buttonMid'>Mid Tier: PRICE</button><button id='buttonHigh'>High Tier: PRICE</button>"
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