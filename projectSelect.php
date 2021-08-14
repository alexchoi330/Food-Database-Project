<!-- reference to Test Oracle file for UBC CPSC304 2018 Winter Term 1
 -->

<html>
    <head>
        <title>Insert Data</title>
    </head>

    <body>

        <h2>Insert values into any table you'd like to select</h2>
        <form method="POST" action="projectSelect.php"> <!--refresh page when submitted-->
  
            <input type="hidden" id="selectQueryRequest" name="selectQueryRequest">
            <label id="tables">Choose a table:</label>

            <select name="s_table">
                <option value= ></option>

           <?php 

                connectToDB();
                global $db_conn;
                $result = executePlainSQL("select table_name from user_tables");
                while ($row = oci_fetch_array($result, OCI_NUM+OCI_RETURN_NULLS)) {
                    foreach ($row as $item)
                    echo '<option value="'. $item .'">'. $item.'</option>'; 
                }
                disconnectFromDB();

            ?>

            </select>

              <input type="submit" value="Select" name="selectSubmit"></p>


        </form>


        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr); 
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection. 
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResult($result) { //prints results from a select statement
            echo "<br>Retrieved data from table demoTable:<br>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]" 
            }

            echo "</table>";
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example, 
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_alexc330", "a43949767", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }


        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function handleUpdateRequest() {
            global $db_conn;

            $old_name = $_POST['oldName'];
            $new_name = $_POST['newName'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
            OCICommit($db_conn);
        }

        function handleResetRequest() {
            global $db_conn;
            // Drop old table
            executePlainSQL("DROP TABLE demoTable");

            // Create new table
            echo "<br> creating new table <br>";
            executePlainSQL("CREATE TABLE demoTable (id int PRIMARY KEY, name char(30))");
            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['insNo'],
                ":bind2" => $_POST['insName']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into demoTable values (:bind1, :bind2)", $alltuples);
            OCICommit($db_conn);
	
        }

	function handleDeleteRequest() {
	    global $db_conn;
            $tuple = array ();
	    $alltuples = array (
                $tuple
            );
	    //Getting the values from user and delete that row data from  the table
             $delete_number = $_GET['delNo']; //
            executeBoundSQL("DELETE FROM demoTable WHERE id = '".$delete_number."'", $alltuples);
            echo "Successfully deleted";
	    OCICommit($db_conn);
        }

        function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM demoTable");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
            }
        }

        function handleDisplayRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT id, name FROM demoTable");

            printResult($result);
            
        }

        function handleSelectRequest() {
            global $db_conn;

            $selected_table = $_POST['s_table'];

            $result = executePlainSQL("SELECT column_name FROM all_tab_columns where table_name = " . "'$selected_table'");

            // echo '<table border="1">';
            // while ($row = oci_fetch_array($result, OCI_NUM+OCI_RETURN_NULLS)) {
            //     echo "<tr>";
            //     foreach ($row as $item)
            //     echo "<td>".htmlentities($item)."</td>";
            //     echo "</tr>";
            // }
            // echo "</table>"; 

            // echo '<option value="'. $item .'">'. $item.'</option>'; 
            //echo '<div class="frmCntr">';

            echo  '<h3>Retrieve records from ' . $selected_table . ' where:</h3>';

            global $querySpec;

            echo "<form method='post' action='projectSelect.php'>";
            echo '<input type="hidden" id="selectTuplesRequest" name="selectTuplesRequest">';
            echo "<table>";
             while ($row = oci_fetch_array($result, OCI_NUM+OCI_RETURN_NULLS)) {
                foreach ($row as $item)
                    $item = strtolower($item);
                    echo '<label for= "'.$item.'"> '. $item.' </label>';
                    echo '<select name= "operator">';
                    echo '<option value=""> -- no constraints -- </option>';
                    echo '<option value="=">equal to</option>';
                    echo '<option value="!=">not equal to</option>';
                    echo '<option value=">">greater than</option>';
                    echo '<option value=">=">greater than or equal to</option>';
                    echo '<option value="<">less than </option>';
                    echo '<option value="<=">less than or equal to</option>';
                    echo '<option value="<=">like</option>';
                    echo '</select>';
                    echo '<input type="text" id=constraint name="constraint"><br>';
          
            }
                    echo '<td></td><td></td><td style="text-align:right>"><input type="submit" value="Submit" name="selectTuples"></td></tr>';
             echo "</table>";




            echo "</form>";

        }


        function handleTuplesRequest() {
            global $db_conn;
            echo $querySpec;
            


        }



        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                } else if (array_key_exists('selectQueryRequest', $_POST)) {
                    handleSelectRequest();
                } else if (array_key_exists('selectTuplesRequest', $_POST)) {
                    handleTuplesRequest();
                } 
                
                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countTuples', $_GET)) {
                    handleCountRequest();
                } else if (array_key_exists('displayTuples', $_GET)) {
		              handleDisplayRequest();
		       } else if (array_key_exists('deleteTuple', $_GET)) {
		               handleDeleteRequest();
		  }

                disconnectFromDB();
            }
        }

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['selectSubmit']) || isset($_POST['selectTuples'])) {
            handlePOSTRequest();
            } else if (isset($_GET['countTupleRequest'])) {
            handleGETRequest();
	} else if (isset($_GET['displayTupleRequest'])) {
    	    handleGETRequest();
        } else if (isset($_GET['deleteTupleRequest'])) {
    	    handleGETRequest();
        }

		?>

        <hr />

       <h2>Back to main page</h2>
       <form method="POST" action="project.php">
            <p><input type="submit" value="Back" name="reset"></p>
        </form>


	</body>
</html>

