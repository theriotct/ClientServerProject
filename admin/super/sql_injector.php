<?php
session_start();
include "../../connection.php";


if (isset($_POST['sql_query'])) {
    $sql_query = trim($_POST['sql_query']);

    $result = mysqli_query($con, $sql_query);

    if ($result === false) {
        // Syntax or SQL error
        echo "SQL Error: " . mysqli_error($con);
    } else {
        // Determine query type
        $query_type = strtoupper(strtok($sql_query, " "));

        if ($query_type === "SELECT" || $query_type === "SHOW" || $query_type === "DESCRIBE") {

            if (mysqli_num_rows($result) > 0) {
                echo "Query executed successfully!<br><br>";
                echo "<table border='1'><tr>";

                // Column headers
                $columns = mysqli_fetch_fields($result);
                foreach ($columns as $col) {
                    echo "<th>" . htmlspecialchars($col->name) . "</th>";
                }
                echo "</tr>";

                $primaryKey = null;
                foreach ($columns as $col) {
                    if (strtolower($col->name) === 'itemid') {
                        $primaryKey = 'itemID';
                        break;
                    }
                }

                // Rows
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    foreach ($columns as $col) {
                        $name = $col->name;
                        $value = $row[$name];

                        echo "<td>";

                        if ($value === null) {
                            echo "<i>NULL</i>";
                        }
                        elseif ($name === "ImageData") {
                            // ONLY treat this column as image/blob

                            if (!empty($value)) {
                                echo "<img src='../../image.php?id=" . $row[$primaryKey] . "' style='max-height:100px; width:auto;'/><br>";
                                echo "<small>Blob data (" . strlen($value) . " bytes)</small>";
                            } else {
                                echo "<i>NULL</i>";
                            }
                        }
                        else {
                            // EVERYTHING ELSE IS NORMAL TEXT
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
            // INSERT, UPDATE, DELETE, etc.
            $affected = mysqli_affected_rows($con);
            echo "Query successful! Rows affected: " . $affected;
        }
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