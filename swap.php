<?php
session_start();
include 'pdo.php';

$pid = $_SESSION['player_id'];
$partyIndex = $_GET['partyIndex'];
$collectionId = $_GET['collectionId'];

// Fetch the selected Pokémon from the collection
$stmt = $pdo->prepare("SELECT dexid FROM PlayerCollection WHERE catchId = ? AND pid = ?");
$stmt->execute([$collectionId, $pid]);
$collectionPokemon = $stmt->fetch(PDO::FETCH_ASSOC)['dexid'];

// Get the current Pokémon in the selected party slot
$stmt = $pdo->prepare("SELECT catchId$partyIndex FROM PlayerParty WHERE pid = ?");
$stmt->execute([$pid]);
$currentPartyPokemon = $stmt->fetch(PDO::FETCH_ASSOC)["catchId$partyIndex"];

// Swap the Pokémon
$stmt = $pdo->prepare("UPDATE PlayerParty SET catchId$partyIndex = ? WHERE pid = ?");
$stmt->execute([$collectionId, $pid]);

if ($currentPartyPokemon !== null) {
    // If there was a Pokémon in the party slot, move it to the collection
    $stmt = $pdo->prepare("UPDATE PlayerCollection SET dexid = ? WHERE catchId = ?");
    $stmt->execute([$currentPartyPokemon, $collectionId]);
} else {
    // If the slot was empty, remove the Pokémon from the collection
    $stmt = $pdo->prepare("DELETE FROM PlayerCollection WHERE catchId = ?");
    $stmt->execute([$collectionId]);
}

// Redirect back to the main page
header('Location: party.php');
exit;
?>
