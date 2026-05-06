<?php
    session_start();

    include '../connection.php';
    include '../functions.php';

    $user_data = check_login($con);

    if(!$user_data || is_null($user_data['isAdmin']) || $_SESSION['2fa_verified'] !== true) {
        forbidden();
    }

	function get_users($con) {
		$query = "SELECT * FROM user WHERE isAdmin IS NULL";
		return mysqli_query($con, $query);
	}

	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['deleteUser']))
	{
		$userID = $_POST['userID'];
		if(!empty($userID) && is_numeric($userID)) {
			$query = "DELETE FROM user WHERE userID = ?";
			$stmt = mysqli_prepare($con, $query);
			mysqli_stmt_bind_param($stmt, 'i', $userID);
			mysqli_stmt_execute($stmt);
		}
	}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Manage Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel='stylesheet' href='https://netdna.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'>
	<script src='https://netdna.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js'></script>
    <style>
    	body{margin: 0;
			 background-color: #b22222;}


		/* USER LIST TABLE */
		.user-list tbody td > img {
    		position: relative;
			max-width: 50px;
			float: left;
			margin-right: 15px;
		}
		.user-list tbody td .user-link {
			display: block;
			font-size: 1.25em;
			padding-top: 3px;
			margin-left: 60px;
		}
		.user-list tbody td .user-subhead {
			font-size: 0.875em;
			font-style: italic;
		}

		/* TABLES */
		.table {
    		border-collapse: separate;
		}
		.table-hover > tbody > tr:hover > td,
		.table-hover > tbody > tr:hover > th {
			background-color: #eee;
		}
		.table thead > tr > th {
			border-bottom: 1px solid #aec2fc;
			padding-bottom: 0;
			background: #fcab47;
		}
		.table tbody > tr > td {
			font-size: 0.875em;
			background: #aec2fc;
			border-top: 10px solid #fcab47;
			vertical-align: middle;
			padding: 12px 8px;
		}
		.table tbody > tr > td:first-child,
		.table thead > tr > th:first-child {
			padding-left: 20px;
		}
		.table thead > tr > th span {
			border-bottom: 2px solid #aec2fc;
			display: inline-block;
			padding: 0 5px;
			padding-bottom: 5px;
			font-weight: normal;
		}
		.table thead > tr > th > a span {
			color: #344644;
		}
		.table thead > tr > th > a span:after {
			content: "\f0dc";
			font-family: FontAwesome;
			font-style: normal;
			font-weight: normal;
			text-decoration: inherit;
			margin-left: 5px;
			font-size: 0.75em;
		}
		.table thead > tr > th > a.asc span:after {
			content: "\f0dd";
		}
		.table thead > tr > th > a.desc span:after {
			content: "\f0de";
		}
		.table thead > tr > th > a:hover span {
			text-decoration: none;
			color: #2bb6a3;
			border-color: #2bb6a3;
		}
		.table.table-hover tbody > tr > td {
			-webkit-transition: background-color 0.15s ease-in-out 0s;
			transition: background-color 0.15s ease-in-out 0s;
		}
		.table tbody tr td .call-type {
			display: block;
			font-size: 0.75em;
			text-align: center;
		}
		.table tbody tr td .first-line {
			line-height: 1.5;
			font-weight: 400;
			font-size: 1.125em;
		}
		.table tbody tr td .first-line span {
			font-size: 0.875em;
			color: #969696;
			font-weight: 300;
		}
		.table tbody tr td .second-line {
			font-size: 0.875em;
			line-height: 1.2;
		}
		.table a.table-link {
			margin: 0 5px;
			font-size: 1.125em;
		}
		.table a.table-link:hover {
			text-decoration: none;
			color: #2aa493;
		}
		.table a.table-link.danger {
			color: #fe635f;
		}
		.table a.table-link.danger:hover {
			color: #dd504c;
		}

		.table-products tbody > tr > td {
			background: none;
			border: none;
			border-bottom: 1px solid #ebebeb;
			-webkit-transition: background-color 0.15s ease-in-out 0s;
			transition: background-color 0.15s ease-in-out 0s;
			position: relative;
		}
		.table-products tbody > tr:hover > td {
			text-decoration: none;
			background-color: #f6f6f6;
		}
		.table-products .name {
			display: block;
			font-weight: 600;
			padding-bottom: 7px;
		}
		.table-products .price {
			display: block;
			text-decoration: none;
			width: 50%;
			float: left;
			font-size: 0.875em;
		}
		.table-products .price > i {
			color: #8dc859;
		}
		.table-products .warranty {
			display: block;
			text-decoration: none;
			width: 50%;
			float: left;
			font-size: 0.875em;
		}
		.table-products .warranty > i {
			color: #f1c40f;
		}
		.table tbody > tr.table-line-fb > td {
			background-color: #9daccb;
			color: #262525;
		}
		.table tbody > tr.table-line-twitter > td {
			background-color: #9fccff;
			color: #262525;
		}
		.table tbody > tr.table-line-plus > td {
			background-color: #eea59c;
			color: #262525;
		}
		.table-stats .status-social-icon {
			font-size: 1.9em;
			vertical-align: bottom;
		}
		.table-stats .table-line-fb .status-social-icon {
			color: #556484;
		}
		.table-stats .table-line-twitter .status-social-icon {
			color: #5885b8;
		}
		.table-stats .table-line-plus .status-social-icon {
			color: #a75d54;
		}

    </style>
  </head>
  <body>
	<?php set_header(); ?>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
	<div class="container">
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<div class="table-responsive">
					<table class="table user-list">
						<thead>
							<tr>
								<th><span>User</span></th>
								<th><span>Created</span></th>
								<th><span>Email</span></th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php $users = get_users($con);
							while($user = mysqli_fetch_assoc($users)): ?>
							<tr>
								<td>
									<img src="../image.php?userID=<?= $user['userID'] ?>" alt=""><!-- Placeholder image -->
									<a href="../profile.php?userID=<?= $user['userID'] ?>" class="user-link"><?= $user['fname']." ".$user['lname'] ?></a>
									<span class="user-subhead"><?= $user['username'] ?></span><br>
									<span class="user-subhead"><?php if($user['isAdmin'] == 0){ echo " (Admin)"; }elseif($user['isAdmin'] == 1){ echo " (SuperAdmin)"; }else{ echo " (User)"; } ?></span>
								</td>
								<td>
									<?= date("Y/m/d", strtotime($user['createdOn'])) ?>
								</td>
							
								<td>
									<a href="mailto:<?= $user['email'] ?>"><?= $user['email'] ?></a>
								</td>
								<td style="width: 20%;">
									<a href="../profile.php?userID=<?= $user['userID'] ?>" class="table-link">
										<span class="fa-stack">
											<i class="fa fa-square fa-stack-2x"></i>
											<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
										</span>
									</a>
										<a href="#" class="table-link"><!-- Edit functionality not implemented yet -->
										<span class="fa-stack">
											<i class="fa fa-square fa-stack-2x"></i>
											<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
										</span>
									</a>
									<form action="manageUsers.php" method="post" style="display:contents;">
										<input type="hidden" name="userID" value="<?= $user['userID'] ?>">
										<button type="submit" name="deleteUser" style="border:none; background:none; padding:0; margin:0;">
											<a href="#" class="table-link danger">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
												</span>
											</a>
										</button>
									</form>
								</td>
							</tr>
							<?php endwhile; ?>
						</tbody>
					</table>
				</div>
				
			</div>
		</div>
	</div>
	</div>

  </body>
</html>
