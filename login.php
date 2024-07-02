<?php require_once "pdo.php";
$name = "";

if(isset($_POST["trainerName"])){
    
    $name = $_POST["trainerName"];
    $name = strtolower($name);
    $stm = "SELECT * FROM player WHERE name='$name'";
    $result = $pdo->query($stm);
    $row = $result->fetchAll();



    session_start();
    if(count($row)==1){
        //return $row
        $id = $pdo->query($stm);
        $player = $id->fetch();
        $playerInfo = array('pid' => $player['pid'], 'name' => $player['name'], 'money' => $player['money'], 'highestTrainer' => $player['highestTrainer']);

        $_SESSION["player"] = json_encode($playerInfo);
        header("location: menu.php");
    }else{
        //Creates new player in database
        $make = "INSERT INTO player (name, money, highestTrainer) VALUES (?,?,?)";
        $make = $pdo->prepare($make);
        $make->execute([$name, 3000, 0]);

        //Creates session variable for holding player object as JSON
        $id = $pdo->query($stm);
        $player = $id->fetch();
        $playerInfo = array("pid" => $player["pid"], "name" => $player["name"], "money" => $player["money"], "highestTrainer" => $player["highestTrainer"]);

        $_SESSION["player"] = json_encode($playerInfo);

        //Creates player party, empty by default
        $sql = "INSERT INTO playerparty (pid, catchId1, catchId2, catchId3, catchId4, catchId5, catchId6) VALUES (?, 0, 0, 0, 0, 0, 0)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([$playerInfo['pid']]);
        header("location: menu.php");
    }
}

?>

