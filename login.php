<?php require_once "pdo.php";
$name = "";

if(isset($_POST["trainerName"])){
    
    $name = $_POST["trainerName"];
    $name = strtolower($name);
    $stm = "SELECT * FROM player WHERE name ='$name'";
    
    $result = $pdo->query($stm);
    
    $row = $result->fetchAll();

    session_start();
    if(count($row)==1){

        $id = $pdo->query($stm);
        $player = $id->fetch();
        
        $playerInfo = array("pid" => $player["pid"], 
                            "name" => $player["name"], 
                            "money" => $player["money"], 
                            "highestTrainer" => $player["highestTrainer"]);

        $_SESSION["player"] = json_encode($playerInfo);
        header("location: menu.php");
        echo $_SESSION["player"];

    }else{
        $make = "INSERT INTO player (name, money, highestTrainer) VALUES (?,?,?)";
        $make = $pdo->prepare($make);
        $make->execute([$name, 0, 0]);

        $id = $pdo->query($stm);
        $player = $id->fetch();
        
        $playerInfo = array("pid" => $player["pid"], 
                            "name" => $player["name"], 
                            "money" => $player["money"], 
                            "highestTrainer" => $player["highestTrainer"]);

        $_SESSION["player"] = json_encode($playerInfo);
        header("location: menu.php");
        echo $_SESSION["player"];
    }


}

?>

