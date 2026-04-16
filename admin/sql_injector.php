
<!DOCTYPE html>
<html>
    <body>
        <form action="sql_injector.php" method="POST">
            <input type="text" name="sql_query" placeholder="Enter SQL Query">
            <button type="submit">Execute</button>
        </form>
        <?php
            include "/functions.php";
            include "/connection.php";
            if(isset($_POST['sql_query'])){
                $sql_query = $_POST['sql_query'];
                $result = mysqli_query($con, $sql_query);
                if($result){
                    echo "Query executed successfully!";

                    if(mysqli_num_rows($result) > 0){
                        echo "<table border='1'><tr>";
                        // Fetch and display column names
                        $columns = mysqli_fetch_fields($result);
                        foreach ($columns as $col) {
                            echo "<th>" . $col->name . "</th>";
                        }
                        echo "</tr>";

                        // Fetch and display rows
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>";
                            foreach ($columns as $col) {
                                echo "<td>" . $row[$col->name] . "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "No results found.";
                    }
                } else {
                    echo "Error executing query: " . mysqli_error($con);
                }
            }
        ?>
    </body>
</html>