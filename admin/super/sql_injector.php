<?php
session_start();
include "../../connection.php";

if (isset($_POST['sql_query'])) {

    $sql_query = trim($_POST['sql_query']);
    $result = mysqli_query($con, $sql_query);

    if ($result === false) {
        echo "SQL Error: " . mysqli_error($con);
        exit;
    }

    $query_type = strtoupper(strtok($sql_query, " "));

    if (in_array($query_type, ["SELECT", "SHOW", "DESCRIBE"])) {

        if (mysqli_num_rows($result) > 0) {

            echo "Query executed successfully!<br><br>";
            echo "<table border='1'><tr>";

            $columns = mysqli_fetch_fields($result);

            foreach ($columns as $col) {
                echo "<th>" . htmlspecialchars($col->name) . "</th>";
            }

            echo "</tr>";

            // ✅ safer primary key detection (works for userID, itemID, postID, etc.)
            $primaryKey = null;
            foreach ($columns as $col) {
                if (str_ends_with(strtolower($col->name), 'id')) {
                    $primaryKey = $col->name;
                    break;
                }
            }

            while ($row = mysqli_fetch_assoc($result)) {

                echo "<tr>";

                foreach ($columns as $col) {

                    $name = $col->name;
                    $value = $row[$name];

                    echo "<td>";

                    if ($value === null) {
                        echo "<i>NULL</i>";
                    }

                    // IMAGE HANDLING FIXED
                    elseif (
                        strtolower($name) === "imagedata" ||
                        strtolower($name) === "profilepicture"
                    ) {

                        if (!empty($value) && $primaryKey && isset($row[$primaryKey])) {

                            // decide correct parameter name
                            $param = (strtolower($primaryKey) === 'userid') ? 'userID' : 'id';

                            echo "<img src='../../image.php?$param=" . (int)$row[$primaryKey] . "' 
                                  style='max-height:100px; width:auto;'/><br>";

                            echo "<small>Blob data (" . strlen($value) . " bytes)</small>";
                        } else {
                            echo "<i>NULL</i>";
                        }
                    }

                    else {
                        echo htmlspecialchars($value);
                    }

                    echo "</td>";
                }

                echo "</tr>";
            }

            echo "</table>";

        } else {
            echo "Query executed successfully, but no results found.";
        }

    } else {

        $affected = mysqli_affected_rows($con);
        echo "Query successful! Rows affected: " . $affected;
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <form action="sql_injector.php" method="POST">
        <input type="text" name="sql_query" placeholder="Enter SQL Query" style="width:300px;">
        <button type="submit">Execute</button>
    </form>
</body>
</html>