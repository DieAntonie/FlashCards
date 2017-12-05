<?php

require './core/init.php';

$topic_id = $_GET['topic_id'];
$title = $_GET['title'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . "/api/cards.php?id=" . $topic_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

$cards = curl_exec($ch);
if (curl_errno($ch)) {
	echo 'Error:' . curl_error($ch);
}

$cards = json_decode(trim($cards), true);

?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title; ?></title>
	<link href="https://fonts.googleapis.com/css?family=Roboto:200,300,400,500" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="./css/study.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
	<div id="topContainer">
		<h2 id="title"><?php echo $title; ?></h2>
		<a id="topics" href="./index.php">Back to Topics</a>
	</div>
	<div id="sideDiv">
		<p id="displayCount"></p>
		<div id="options">
			<button id="shuffle">Shuffle</button>
			<button id="studyOppositeSide">Definition First</button>
		</div>
		<button id="newCard">+</button>
	</div>
	<div id="container">
		<div id="card">
			<h2 id="cardDisplay"></h2>
		</div>
		<div id="altCard">
			<h2 id="altCardDisplay"></h2>
		</div>
		<div id="newCardDiv" style="display:none;">
			<form id="newCardForm" action="./api/insertCard.php" method="post" style="display: none;">
				<input type="text" name="title" id="titleInput" placeholder="Front Side">
				<input type="text" name="definition" id="definitionInput" placeholder="Back Side">
				<input type="text" name="topic_id" value=<?php echo $topic_id; ?> style="display: none;">
				<input type="text" name="topic_title" value=<?php echo urlencode($title); ?> style="display: none;">
				<button id="addCard">Add Card</button>
			</form>
		</div>
		<div id="cardInteract">
			<button id="nextCard" class="arrow">></button>
			<button id="previousCard" class="arrow"><</button>
			<button id="flipCard">Flip</button>
		</div>
	</div>
</body>
<script type="text/javascript">

	//counter will keep track of which index in the array is shown
	var counter = 0;
	var cards = <?php echo json_encode($cards); ?>;
	var card = document.getElementById('cardDisplay');
	var altCard = document.getElementById('altCardDisplay');
	var displayCount = document.getElementById('displayCount');
	displayCount.innerHTML = counter + 1 + "/" + cards.length;
	var startWithSide = 'title';
	card.innerHTML = cards[counter][startWithSide];
	var topCard = '#card';
	var bottomCard = '#altCard';

	//user interaction with cards through button clicks

	$('#nextCard').click(function() {
		//call incrementCard when button is clicked
		incrementCard();
	});

	$('#previousCard').click(function() {
		//call decrementcard when button is clicked
		decrementCard();
	});
		
	$('#flipCard').click(function() {
		//call flipCard when button is clicked
		flipCard();
	});

	//user interaction with cards through arrow keys
	$(document).keydown(function(e) {
		if (e.key == "ArrowRight") {
			incrementCard();
		} else if (e.key == "ArrowUp") {
			flipCard();
		} else if (e.key == "ArrowLeft") {
			decrementCard();
		} else if (e.key == "ArrowDown") {
			flipCard();
		}
	});

	/*
	* Shuffle the deck using my implementation of the Fisher-Yates shuffle
	* on an array
	*/
	$('#shuffle').click(function() {
		var backIndex = cards.length-1;
		while (backIndex > 0) {
			var randomIndex = Math.floor(Math.random() * backIndex);
			var temp = cards[backIndex];
			cards[backIndex] = cards[randomIndex];
			cards[randomIndex] = temp;
			backIndex--;
		}
		counter = 0;
		card.innerHTML = cards[counter][startWithSide];
		displayCount.innerHTML = counter + 1 + "/" + cards.length;
	});

	$('#newCard').click(function() {
		/*
		* slide both divs to the right simulaneously
		*/
		$('#newCardDiv').css('display', 'block');
		$('#newCardForm').css('display', 'block');
		$('#definitionInput').css('display', 'none');
		$('#addCard').css('display', 'none');
		$('#card').animate({
			marginLeft: "80%",
			opacity: 0,
		}, 600, function() {
			$('#card').css('display', 'none');
			$('#definitionInput').css('display', 'none');
			$('#addCard').css('display', 'none');
			$('#titleInput').focus();
		});
		$('#newCardDiv').animate({
			marginLeft: '25%',
			opacity: 1, 
		}, 600, function() {
			$('#titleInput').focus();
		});
	});

	/*
	* Toggle the starting view of cards as title side or definition side
	*/
	$('#studyOppositeSide').click(function() {
		if (startWithSide == 'title') {
			startWithSide = 'definition';
			$('#studyOppositeSide').html("Title First");
		} else {
			startWithSide = 'title';
			$('#studyOppositeSide').html("Definition First");
		}
		$(topCard + 'Display').html(cards[counter][startWithSide]);
	});

	function incrementCard() {
		/*
		* Change display to the next card in the current set
		*/
		$(topCard).animate({
			marginLeft: '50%',
			opacity: '0'
		}, 300, function() {
			//update z-indices to hide unwanted card
			$(topCard).css('z-index', '1');
			$(bottomCard).css('z-index', '2');
			$(topCard).css('margin-left', '25%');
			$(topCard).css('opacity', '1');

			//swap topCard
			[topCard, bottomCard] = [bottomCard, topCard];
			console.log('top: ' + topCard + ' bottom: ' + bottomCard);

		});

		//this code executes before the animation is finished, sets the bottom cards display
		try {
			$(bottomCard + 'Display').html(cards[++counter][startWithSide]);
		} catch(err) {
			counter = 0;
			$(bottomCard + 'Display').html(cards[counter][startWithSide]);
		}
		displayCount.innerHTML = counter + 1 + "/" + cards.length;

	}

	function decrementCard() {
		/* 
		* Change display to the previous card in the current set 
		*/ 
		try {
			card.innerHTML = cards[--counter][startWithSide];
		} catch (err) {
			counter = cards.length - 1;
			card.innerHTML = cards[counter][startWithSide];
		}
		displayCount.innerHTML = counter + 1 + "/" + cards.length;
	}

	function flipCard() {
		/*
		* Toggle which side of the card is displayed
		*/

		//need to hide bottom card for animation
		$(bottomCard).css('display', 'none');
		$(topCard).animate({
			width: '0',
			marginLeft: '50%',
			opacity: 0.2,
		}, 100, function() {
			if ($(topCard + 'Display').html() == cards[counter]['title']) {
				$(topCard + 'Display').html(card.innerHTML = cards[counter]['definition']);
			} else {
				$(topCard + 'Display').html(card.innerHTML = cards[counter]['title']);
			}
			$(topCard).animate({
				width: '50%',
				marginLeft: '25%',
				opacity: 1
			}, 100, function() {
				//bring bottom card back
				$(bottomCard).css('display', 'block');
			})
		});
		
	}











	
</script>
</html>