<?php
	session_start();

	include 'connection.php';
	include 'functions.php';

	$user_data = check_login($con);

	if(!$user_data) {
		forbidden();
	}

	//SELECT u.userID, u.username, COALESCE(unread.unreadCount, 0) AS unreadCount FROM user u JOIN (SELECT recipID AS uid FROM messages WHERE authorID = $senderID UNION SELECT authorID AS uid FROM messages WHERE recipID = $senderID LEFT JOIN (SELECT authorID AS uid, COUNT(*) AS unreadCount FROM messages WHERE recipID = $senderID AND isRead = 0 GROUP BY authorID) unread ON unread.uid = u.userID;

	//SELECT body, authorID, recipID, date, CASE  WHEN authorID = $senderID THEN 1 ELSE 0 END AS isSentByMe FROM messages WHERE (authorID = $senderID AND recipID = $recipientID) OR (authorID = $senderID AND recipID = $recipientID) ORDER BY date DESC;

	//UPDATE messages SET isRead = 1 WHERE recipID = $recipientID AND authorID = $senderID;

	function get_contacts() {
		global $con, $user_data;

		$senderID = $user_data['userID'];

		$query = "SELECT 
						u.userID,
						u.username,
						COALESCE(unread.unreadCount, 0) AS unreadCount
					FROM user u
					JOIN (
						SELECT recipID AS uid
						FROM messages
						WHERE authorID = $senderID

						UNION

						SELECT authorID AS uid
						FROM messages
						WHERE recipID = $senderID
					) t ON u.userID = t.uid

					LEFT JOIN (
						SELECT 
							authorID AS uid,
							COUNT(*) AS unreadCount
						FROM messages
						WHERE recipID = $senderID
						AND isRead = 0
						GROUP BY authorID
					) unread ON unread.uid = u.userID;
					";

		$result = mysqli_query($con, $query);

		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) {
				echo '<a href="message.php?userID=' . $row['userID'] . '" class="list-group-item list-group-item-action border-0">';
				if ($row['unreadCount'] > 0) {
					echo '<div class="badge bg-success float-right">' . $row['unreadCount'] . '</div>';
				}
				echo '<div class="d-flex align-items-start">';
				echo '<img src="https://bootdey.com/img/Content/avatar/avatar2.png" class="rounded-circle mr-1" alt="' . htmlspecialchars($row['username']) . '" width="40" height="40">';
				echo '<div class="flex-grow-1 ml-3">' . htmlspecialchars($row['username']) . '</div>';
				echo '</div>';
				echo '</a>';
			}
		} else {
			alert("Error fetching contacts: " . mysqli_error($con));
		}
	}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Awesome Messages</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css'>
	<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js'></script>
    <style>
      body{
		  margin-top:20px;
	  }

		.chat-online {
    		color: #34ce57
		}

		.chat-offline {
    		color: #e4606d
		}

		.chat-messages {
    		display: flex;
    		flex-direction: column;
    		max-height: 800px;
    		overflow-y: scroll
		}

		.chat-message-left,
		.chat-message-right {
    		display: flex;
    		flex-shrink: 0
		}

		.chat-message-left {
    		margin-right: auto
		}

		.chat-message-right {
    		flex-direction: row-reverse;
    		margin-left: auto
		}
		.py-3 {
    		padding-top: 1rem!important;
    		padding-bottom: 1rem!important;
		}
		.px-4 {
    		padding-right: 1.5rem!important;
    		padding-left: 1.5rem!important;
		}
		.flex-grow-0 {
    		flex-grow: 0!important;
		}
		.border-top {
    		border-top: 1px solid #dee2e6!important;
		}
		main {
			background-color: lightblue;
		}
    </style>
  </head>
  <body>
    <?php //set_header(); ?>
	<br>
    <main class="content">
      <div class="container p-0">

		    <h1 class="h3 mb-3">Messages</h1>

		    <div class="card">
			    <div class="row g-0">
				    <div class="col-12 col-lg-5 col-xl-3 border-right">

					    <div class="px-4 d-none d-md-block">
						    <div class="d-flex align-items-center">
							    <div class="flex-grow-1">
								    <input type="text" class="form-control my-3" placeholder="Search...">
							    </div>
						    </div>
					    </div>
						<?php get_contacts(); ?>

					    <hr class="d-block d-lg-none mt-1 mb-0">
				    </div>
				    <div class="col-12 col-lg-7 col-xl-9">
					    <div class="py-2 px-4 border-bottom d-none d-lg-block">
						    <div class="d-flex align-items-center py-1">
							    <div class="position-relative">
								    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
							    </div>
							    <div class="flex-grow-1 pl-3">
								    <strong>Sharon Lessman</strong>
							    </div>
						    </div>
					    </div>

					    <div class="position-relative">
						    <div class="chat-messages p-4">

							    <?php
								    // Fetch messages between the logged-in user and the selected contact
								    // Mark messages as read if they were sent by the contact and not yet read
								    // Display messages in the chat area

									if (isset($_GET['userID'])) {
									    $recipientID = intval($_GET['userID']);
									    $senderID = $user_data['userID'];

										$contact_query = "SELECT * FROM user WHERE userID = $recipientID";
										$contact_result = mysqli_query($con, $contact_query);
										if ($contact_result && mysqli_num_rows($contact_result) > 0) {
										    $contact_data = mysqli_fetch_assoc($contact_result);
									    } else {
										    alert("Contact not found.");
										    exit;
									    }
									    // Mark messages as read
									    $updateQuery = "UPDATE messages
															SET isRead = 1
															WHERE recipID = $senderID
															AND authorID = $recipientID
															AND isRead = 0;";
									    mysqli_query($con, $updateQuery);

									    // Fetch messages
									    $query =   "SELECT 
														body,
														authorID,
														recipID,
														date,
														CASE  
															WHEN authorID = $senderID THEN 1 
															ELSE 0 
														END AS isSentByMe
													FROM messages
													WHERE 
														(authorID = $senderID AND recipID = $recipientID)
														OR
														(authorID = $recipientID AND recipID = $senderID)
													ORDER BY date DESC;";
									    $result = mysqli_query($con, $query);

									    if ($result) {
										    while ($row = mysqli_fetch_assoc($result)) {
											    if($row['isSentByMe'] == 1) {
												    // Message sent by the logged-in user
													echo '<div class="chat-message-right mb-4">
														<div>
															<img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="'.$user_data['username'].'" width="40" height="40">
															<div class="text-muted small text-nowrap mt-2">'.$row['date'].'</div>
														</div>
														<div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
															<div class="font-weight-bold mb-1">You</div>
															'.$row['body'].'
														</div>
													</div>';

											    } else {
												    // Message sent by the contact
												    echo '<div class="chat-message-left pb-4">
														<div>
															<img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="'.$contact_data['username'].'" width="40" height="40">
															<div class="text-muted small text-nowrap mt-2">'.$row['date'].'</div>
														</div>
														<div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
															<div class="font-weight-bold mb-1">'.$contact_data['username'].'</div>
															'.$row['body'].'
														</div>
													</div>';
											    }
										    }
									    }else{
											echo "<p>Be the first to send a message!</p>";
										}
								    } else {
									    echo '<div class="text-center m-5">
										    <p>Select a contact to start chatting</p>
									    </div>';
								    }
								?>

						    </div>
					    </div>

					    <div class="flex-grow-0 py-3 px-4 border-top">
						    <div class="input-group">
							    <input type="text" class="form-control" placeholder="Type your message">
							    <button class="btn btn-primary">Send</button>
						    </div>
					    </div>

				    </div>
			    </div>
		    </div>
	    </div>
    </main>
  </body>
</html>
