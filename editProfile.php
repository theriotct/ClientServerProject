<?php
    session_start();
    include "connection.php";
    include "functions.php";

    $user_data = check_login($con);

    if (!$user_data) {
        header("Location: login.php");
        die;
    }

    $sessionUserID = (int)$user_data['userID'];
    $errors        = [];

    // Fetch current profile data — always use session userID, never URL param
    $stmt = mysqli_prepare($con,
        "SELECT userID, fname, lname, displayName, username, biography, signature,
                profilePicture, location, major, interests
         FROM user WHERE userID = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $sessionUserID);
    mysqli_stmt_execute($stmt);
    $profile = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$profile) {
        not_found();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fname          = trim($_POST['fname']          ?? '');
        $lname          = trim($_POST['lname']          ?? '');
        $displayName    = trim($_POST['displayName']    ?? '');
        $username       = trim($_POST['username']       ?? '');
        $biography      = trim($_POST['biography']      ?? '');
        $signature      = trim($_POST['signature']      ?? '');

        $profilePicture = null;
        if (isset($_FILES['imageData']) && $_FILES['imageData']['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['imageData']['tmp_name']);

            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $errors[] = "Invalid image type.";
            } else {
                $profilePicture = file_get_contents($_FILES['imageData']['tmp_name']);
            }

            finfo_close($finfo);
        }

        $location       = trim($_POST['location']       ?? '');
        $major          = trim($_POST['major']          ?? '');
        $interests      = trim($_POST['interests']      ?? '');

        if ($username === '')               $errors[] = 'Username is required.';
        if (mb_strlen($fname)          > 50)   $errors[] = 'First name must be 50 characters or fewer.';
        if (mb_strlen($lname)          > 50)   $errors[] = 'Last name must be 50 characters or fewer.';
        if (mb_strlen($displayName)    > 75)   $errors[] = 'Display name must be 75 characters or fewer.';
        if (mb_strlen($username)       > 25)   $errors[] = 'Username must be 25 characters or fewer.';
        if (mb_strlen($biography)      > 1000) $errors[] = 'Biography must be 1000 characters or fewer.';
        if (mb_strlen($signature)      > 100)  $errors[] = 'Signature must be 100 characters or fewer.';
        if (mb_strlen($location)       > 100)  $errors[] = 'Location must be 100 characters or fewer.';
        if (mb_strlen($major)          > 100)  $errors[] = 'Major must be 100 characters or fewer.';
        if (mb_strlen($interests)      > 500)  $errors[] = 'Interests must be 500 characters or fewer.';

        if (empty($errors)) {
            $dupStmt = mysqli_prepare($con,
                "SELECT userID FROM user WHERE username = ? AND userID != ? LIMIT 1");
            mysqli_stmt_bind_param($dupStmt, 'si', $username, $sessionUserID);
            mysqli_stmt_execute($dupStmt);
            if (mysqli_num_rows(mysqli_stmt_get_result($dupStmt)) > 0) {
                $errors[] = 'That username is already taken.';
            }
            mysqli_stmt_close($dupStmt);
        }

        if(empty($errors)) {
            if ($profilePicture !== null) {
                $updateStmt = mysqli_prepare($con,
                    "UPDATE user
                    SET fname=?, lname=?, displayName=?, username=?, biography=?,
                        signature=?, profilePicture=?, location=?, major=?, interests=?
                    WHERE userID=?"
                );

                mysqli_stmt_bind_param(
                    $updateStmt,
                    'ssssssssssi',
                    $fname, $lname, $displayName, $username, $biography,
                    $signature, $profilePicture, $location, $major, $interests,
                    $sessionUserID
                );

            } else {
                $updateStmt = mysqli_prepare($con,
                    "UPDATE user
                    SET fname=?, lname=?, displayName=?, username=?, biography=?,
                        signature=?, location=?, major=?, interests=?
                    WHERE userID=?"
                );

                mysqli_stmt_bind_param(
                    $updateStmt,
                    'sssssssssi',
                    $fname, $lname, $displayName, $username, $biography,
                    $signature, $location, $major, $interests,
                    $sessionUserID
                );
            }

            mysqli_stmt_execute($updateStmt);

            mysqli_stmt_close($updateStmt);

            header("Location: profile.php");
            exit;
        }

        // Only reached when $errors is non-empty (success path exits above); repopulate form values
        $profile = array_merge($profile, compact(
            'fname', 'lname', 'displayName', 'username', 'biography',
            'signature', 'location', 'major', 'interests'
        ));
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: lightblue; }
        .form-card {
            background-color: white;
            border-radius: 12px;
            border: 2px solid #ff5c00;
            padding: 30px;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .form-card h4 { color: #ff5c00; margin-bottom: 20px; }
        .btn-orange { background-color: #ff5c00; color: white; border: none; }
        .btn-orange:hover { background-color: #dc4f00; color: white; }
        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #ff5c00;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ffdac8;
            padding-bottom: 4px;
        }
    </style>
</head>
<body>
    <?php set_header(); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="mt-3">
                    <a href="profile.php" class="text-decoration-none" style="color:#ff5c00;">← Back to Profile</a>
                </div>

                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>

                <div class="form-card">
                    <h4>Edit Profile</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="section-label">Identity</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First name</label>
                                <input type="text" name="fname" class="form-control" maxlength="50"
                                       value="<?php echo htmlspecialchars($profile['fname'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last name</label>
                                <input type="text" name="lname" class="form-control" maxlength="50"
                                       value="<?php echo htmlspecialchars($profile['lname'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display name <small class="text-muted">(shown instead of full name if set)</small></label>
                            <input type="text" name="displayName" class="form-control" maxlength="75"
                                   value="<?php echo htmlspecialchars($profile['displayName'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" maxlength="25" required
                                   value="<?php echo htmlspecialchars($profile['username'] ?? ''); ?>">
                        </div>

                        <div class="section-label">About</div>
                        <div class="mb-3">
                            <label class="form-label">Biography</label>
                            <textarea name="biography" class="form-control" rows="4"
                                      maxlength="1000"><?php echo htmlspecialchars($profile['biography'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interests</label>
                            <textarea name="interests" class="form-control" rows="3"
                                      maxlength="500"><?php echo htmlspecialchars($profile['interests'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Signature</label>
                            <input type="text" name="signature" class="form-control" maxlength="100"
                                   value="<?php echo htmlspecialchars($profile['signature'] ?? ''); ?>">
                        </div>

                        <div class="section-label">Details</div>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input 
                                type="file" 
                                class="form-control" 
                                name="imageData" 
                                accept="image/*"
                            >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" maxlength="100"
                                   value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Major / Area of study</label>
                            <input type="text" name="major" class="form-control" maxlength="100"
                                   value="<?php echo htmlspecialchars($profile['major'] ?? ''); ?>">
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-orange px-4">Save Changes</button>
                            <a href="profile.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
