<?php
session_start();
include 'pdo.php';

// Assume the player is logged in and their ID is stored in the session
$pid = $_SESSION['player_id'];

// Fetch party Pokémon
$stmt = $pdo->prepare("SELECT * FROM PlayerParty WHERE pid = ?");
$stmt->execute([$pid]);
$party = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch collection Pokémon
$stmt = $pdo->prepare("SELECT * FROM PlayerCollection JOIN Pokemon ON PlayerCollection.dexid = Pokemon.dexid WHERE pid = ?");
$stmt->execute([$pid]);
$collection = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Party Manager</title>
    <style>
        body {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .container {
            width: 45%;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container" id="party-container">
        <h2>Party</h2>
        <ul id="party">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <?php if (!empty($party["catchId$i"])): ?>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM Pokemon WHERE dexid = ?");
                    $stmt->execute([$party["catchId$i"]]);
                    $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <li onclick="selectPokemon('party', <?php echo $i; ?>)"><?php echo $pokemon['name']; ?></li>
                <?php else: ?>
                    <li onclick="selectPokemon('party', <?php echo $i; ?>)">Empty Slot</li>
                <?php endif; ?>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="container" id="collection-container">
        <h2>Collection</h2>
        <ul id="collection">
            <?php foreach ($collection as $pokemon): ?>
                <li onclick="selectPokemon('collection', <?php echo $pokemon['catchId']; ?>)"><?php echo $pokemon['name']; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        let selectedPartyIndex = null;
        let selectedCollectionId = null;

        function selectPokemon(type, index) {
            if (type === 'party') {
                selectedPartyIndex = index;
            } else if (type === 'collection') {
                selectedCollectionId = index;
            }

            if (selectedPartyIndex !== null && selectedCollectionId !== null) {
                window.location.href = `swap.php?partyIndex=${selectedPartyIndex}&collectionId=${selectedCollectionId}`;
            }
        }
    </script>
</body>
</html>
