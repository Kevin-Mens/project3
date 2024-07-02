<?php
    session_start();
    require_once "pdo.php";

    // getting player object
    $player = json_decode($_SESSION['player'], true);
    $pid = $player['pid'];
    $sql = "SELECT * FROM playerparty WHERE pid=$pid";
    $party = $pdo->query($sql);
    $row = $party->fetch();
    $catchids = array($row['catchId1'], $row['catchId2'], $row['catchId3'], $row['catchId4'], $row['catchId5'], $row['catchId6']);

    // Handle form submission for swapping Pokémon
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['partyIndex']) && isset($_POST['collectionId'])) {
        $partyIndex = $_POST['partyIndex'];
        $collectionId = $_POST['collectionId'];

        // Find the first empty slot in the party to add the new Pokémon
        $stmt = $pdo->prepare("UPDATE playerparty SET catchId{$partyIndex} = ? WHERE pid = ?");
        $stmt->execute([$collectionId, $pid]);
        header('Location: party.php');
    }

    // composes array of the party pokemon
    $partyData = array();
    for($i = 0; $i < count($catchids); $i++)
    {
        if ($catchids[$i] != 0){
            $sql = "SELECT * FROM playercollection WHERE catchId=$catchids[$i]";
            $result = $pdo->query($sql);
            $row = $result->fetch();
            $num = $row['dexid'];

            $sql = "SELECT * FROM pokemon WHERE dexid=$num";
            $result = $pdo->query($sql);
            $row = $result->fetch();
            $pokeData = array('dexid' => $row['dexid'], 'name' => $row['name']);
            array_push($partyData, $pokeData);
        }else array_push($partyData, NULL);
    }

    // composes array of full collection
    $sql = $pdo->prepare("
        SELECT PlayerCollection.catchId, Pokemon.name 
        FROM PlayerCollection JOIN Pokemon ON 
        PlayerCollection.dexid = Pokemon.dexid WHERE PlayerCollection.pid = ?
        AND PlayerCollection.catchId NOT IN (?, ?, ?, ?, ?, ?)");
    $sql->execute([$pid, $catchids[0], $catchids[1], $catchids[2], 
                         $catchids[3], $catchids[4], $catchids[5]]);
    $collection = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Party Selection</title>
    <link rel="stylesheet" href="party.css">
</head>
<body>
    <a href="menu.php" class="back-button">Back to Menu</a>
    <form method="post" action="">
        <div class="flex-container">
            <div class="container" id="party-container">
                <h2>Party</h2>
                <select name="partyIndex" required>
                    <option value="">Select Party Pokémon</option>
                    <?php foreach ($partyData as $index => $pokemon): 
                        if ($pokemon):?>
                            <option value="<?php echo $index + 1; ?>"><?php echo $pokemon['name']; ?></option>
                        <?php else: ?>
                            <option value="<?php echo $index + 1; ?>">Empty Slot</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="container" id="collection-container">
                <h2>Collection</h2>
                <select name="collectionId" required>
                    <option value="">Select Collection Pokémon</option>
                    <?php foreach ($partyData as $index => $pokemon): 
                        if ($pokemon):?>
                            <option value="<?php echo $index + 1; ?>"><?php echo $pokemon['name']; ?></option>
                        <?php else: ?>
                            <option value="<?php echo $index + 1; ?>">Empty Slot</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit">Swap</button>
    </form>
</body>
</html>
