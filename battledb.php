<?php
    require_once "pdo.php";
    session_start();

    if(isset($_POST['id']))
    {
        $id = $_POST['id'];
        $sql = "SELECT * FROM playerparty WHERE pid=$id";
        $result = $pdo->query($sql);
        $row = $result->fetch();
        $tempids = array($row['catchId1'], $row['catchId2'], $row['catchId3'], $row['catchId4'], $row['catchId5'], $row['catchId6']);

        $catchids = array();
        for($i = 0; $i < count($tempids); $i++)
        {
            if($tempids[$i] != null)
            {
                array_push($catchids, $tempids[$i]);
            }
        }

        $pokeArray = array(); //might not need but don't want to break by removing
        for($i = 0; $i < count($catchids); $i++)
        {
            $sql = "SELECT * FROM playercollection WHERE catchId=$catchids[$i]";
            $result =$pdo->query($sql);
            $row = $result->fetch();
            $num = $row['dexid'];

            $sql = "SELECT * FROM pokemon WHERE dexid=$num";
            $result = $pdo->query($sql);
            $row = $result->fetch();
            $pokeData = array('dexid' => $row['dexid'], 'name' => $row['name'], 'type1' => $row['type1'], 'type2' => $row['type2'], 'statTotal' => $row['statTotal'], 
            'hp' => $row['hp'], 'atk' => $row['atk'], 'def' => $row['def'], 'spatk' => $row['spatk'], 'spdef' => $row['spdef'], 'spd' => $row['spd'],
            'gen' => $row['gen'], 'legendary' => $row['legendary'], 'catchid' => $catchids[$i]);

            echo json_encode($pokeData) . " ";
        }
    }

    if(isset($_POST['needComp']))
    {
        $statTotalMin = 0;
        $statTotalMax = 0;
        $numPokemon = 1;

        if($_SESSION['trainer'] == 1)
        {
            $statTotalMin = 100;
            $statTotalMax = 340;
            $numPokemon = 2;
        }
        else if($_SESSION['trainer'] == 2)
        {
            $statTotalMin = 345;
            $statTotalMax = 455;
            $numPokemon = 4;
        }
        else if($_SESSION['trainer'] == 3)
        {
            $statTotalMin = 460;
            $statTotalMax = 520;
            $numPokemon = 5;
        }
        else if($_SESSION['trainer'] == 4)
        {
            $statTotalMin = 525;
            $statTotalMax = 700;
            $numPokemon = 6;
        }

        $sql = "SELECT * FROM pokemon WHERE statTotal >= $statTotalMin AND statTotal <= $statTotalMax";
        $result = $pdo->query($sql);
        $rowArray = $result->fetchAll();

        for($i = 0; $i < $numPokemon; $i++)
        {
            $arrayIndex = rand(0, count($rowArray) - 1);

            $pokeData = array('dexid' => $rowArray[$arrayIndex]['dexid'], 'name' => $rowArray[$arrayIndex]['name'], 
            'type1' => $rowArray[$arrayIndex]['type1'], 'type2' => $rowArray[$arrayIndex]['type2'], 'statTotal' => $rowArray[$arrayIndex]['statTotal'], 
            'hp' => $rowArray[$arrayIndex]['hp'], 'atk' => $rowArray[$arrayIndex]['atk'], 'def' => $rowArray[$arrayIndex]['def'], 'spatk' => $rowArray[$arrayIndex]['spatk'], 
            'spdef' => $rowArray[$arrayIndex]['spdef'], 'spd' => $rowArray[$arrayIndex]['spd'],
            'gen' => $rowArray[$arrayIndex]['gen'], 'legendary' => $rowArray[$arrayIndex]['legendary']);

            echo json_encode($pokeData) . " ";
        }

    }

    if(isset($_POST['trainerDefeated'], $_POST['id']))
    {
        $reward = 0;
        $pid = $_POST['id'];
        $trainer = $_POST['trainerDefeated'];

        if($trainer == 1)
        {
            $reward = 2000;
        }
        else if($trainer == 2)
        {
            $reward = 4000;
        }
        else if($trainer == 3)
        {
            $reward = 10000;
        }
        else if($trainer == 4)
        {
            $reward = 20000;
        }

        $newPlayer = json_decode($_SESSION['player']);
        $newPlayer->money += $reward;
        if($newPlayer->highestTrainer < $trainer)
        {
            $newPlayer->highestTrainer = $trainer;
        }
        $_SESSION['player'] = json_encode($newPlayer);

        $totalMoney = $newPlayer->money;
        $sql = "UPDATE player SET money=?, highestTrainer=? WHERE pid=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$totalMoney, $newPlayer->highestTrainer,$pid]);
    }
?>