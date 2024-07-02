<?php
require_once "pdo.php";
session_start();

if(isset($_POST['num'], $_POST['tier']))
{
    if($_POST['tier'] == 1)
    {
        $num = $_POST['num'];
        $sql = "SELECT * FROM pokemon WHERE dexid=$num";
        $result = $pdo->query($sql);
        $row = $result->fetch();
        $pokeData = array('dexid' => $row['dexid'], 'name' => $row['name'], 'type1' => $row['type1'], 'type2' => $row['type2'], 'statTotal' => $row['statTotal'], 
        'hp' => $row['hp'], 'atk' => $row['atk'], 'def' => $row['def'], 'spatk' => $row['spatk'], 'spdef' => $row['spdef'], 'spd' => $row['spd'],
        'gen' => $row['gen'], 'legendary' => $row['legendary']);
        echo json_encode($pokeData);
    }
}

if(isset($_POST['pokedexNum'], $_POST['playerId']))
{
    $pid = $_POST['playerId'];
    $dexid = $_POST['pokedexNum'];
    $sql = "INSERT INTO playercollection (pid, dexid) VALUES (?, ?)";
    $stmt= $pdo->prepare($sql);
    $stmt->execute([$pid, $dexid]);
}

if(isset($_POST['newMoney'], $_POST['id']))
{
    $amount = $_POST['newMoney'];
    $playerId = $_POST['id'];
    $sql = "UPDATE player SET money=? WHERE pid=?";
    $stmt= $pdo->prepare($sql);
    $stmt->execute([$amount, $playerId]);

    $newPlayer = json_decode($_SESSION['player']);
    $newPlayer->money= $amount;
    $_SESSION['player'] = json_encode($newPlayer);
    echo $_SESSION['player'];
}
?>