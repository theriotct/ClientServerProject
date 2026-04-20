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
				echo '<a href="#" class="list-group-item list-group-item-action border-0">';
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

							    <div class="chat-message-right pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:33 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
									    <div class="font-weight-bold mb-1">You</div>
									    Lorem ipsum dolor sit amet, vis erat denique in, dicunt prodesset te vix.
								    </div>
							    </div>

							    <div class="chat-message-left pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:34 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
									    <div class="font-weight-bold mb-1">Sharon Lessman</div>
									    Sit meis deleniti eu, pri vidit meliore docendi ut, an eum erat animal commodo.
								    </div>
							    </div>

							    <div class="chat-message-right mb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:35 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
									    <div class="font-weight-bold mb-1">You</div>
									    Cum ea graeci tractatos.
								    </div>
							    </div>

							    <div class="chat-message-left pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:36 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
									    <div class="font-weight-bold mb-1">Sharon Lessman</div>
									    Sed pulvinar, massa vitae interdum pulvinar, risus lectus porttitor magna, vitae commodo lectus mauris et velit.
									    Proin ultricies placerat imperdiet. Morbi varius quam ac venenatis tempus.
								    </div>
							    </div>

							    <div class="chat-message-left pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:37 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
									    <div class="font-weight-bold mb-1">Sharon Lessman</div>
									    Cras pulvinar, sapien id vehicula aliquet, diam velit elementum orci.
								    </div>
							    </div>

							    <div class="chat-message-right mb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:38 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
									    <div class="font-weight-bold mb-1">You</div>
									    Lorem ipsum dolor sit amet, vis erat denique in, dicunt prodesset te vix.
								    </div>
							    </div>

							    <div class="chat-message-left pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:39 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
									    <div class="font-weight-bold mb-1">Sharon Lessman</div>
									    Sit meis deleniti eu, pri vidit meliore docendi ut, an eum erat animal commodo.
								    </div>
							    </div>

							    <div class="chat-message-right mb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:40 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
									    <div class="font-weight-bold mb-1">You</div>
									    Cum ea graeci tractatos.
								    </div>
							    </div>

							    <div class="chat-message-right mb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:41 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
									    <div class="font-weight-bold mb-1">You</div>
									    Morbi finibus, lorem id placerat ullamcorper, nunc enim ultrices massa, id dignissim metus urna eget purus.
								    </div>
							    </div>

							    <div class="chat-message-left pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:42 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
									    <div class="font-weight-bold mb-1">Sharon Lessman</div>
									    Sed pulvinar, massa vitae interdum pulvinar, risus lectus porttitor magna, vitae commodo lectus mauris et velit.
									    Proin ultricies placerat imperdiet. Morbi varius quam ac venenatis tempus.
								    </div>
							    </div>

							    <div class="chat-message-right mb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:43 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
									    <div class="font-weight-bold mb-1">You</div>
									    Lorem ipsum dolor sit amet, vis erat denique in, dicunt prodesset te vix.
								    </div>
							    </div>

							    <div class="chat-message-left pb-4">
								    <div>
									    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
									    <div class="text-muted small text-nowrap mt-2">2:44 am</div>
								    </div>
								    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
									    <div class="font-weight-bold mb-1">Sharon Lessman</div>
									    Sit meis deleniti eu, pri vidit meliore docendi ut, an eum erat animal commodo.
								    </div>
							    </div>

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
