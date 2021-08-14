<!-- reference to Test Oracle file for UBC CPSC304 2018 Winter Term 1
 -->

 <html>
    <head>
        <title>Nested Aggregation</title>
    </head>

    <body>

        
        <h2>Aggregation</h2>
        <h3>Useful informations to refer to when trying to insert data</h3>
		users (email varchar(50), password char(20), name char(20), age integer, b_date date, gender varchar(5), weight integer, PRIMARY KEY(email)<br/>
		meal_plan (id char(20), period integer, calories_per_day integer, PRIMARY KEY(id)<br/>
		gets (meal_name char(30), day_of_week char(20), time char(30), u_email varchar(50), mp_id char(20), PRIMARY KEY(meal_name, u_email, mp_id))<br/>
		goals (name char(20) PRIMARY KEY, dateSet date, deadline date) <br/>
		has_goals (u_email varchar(50), g_name char(20), PRIMARY KEY(u_email, g_name))<br/>
		has_dietary_restrictions (u_email varchar(50) PRIMARY KEY, dr_id char(20)) <br/>
		meals (name char(30), calories integer, percent_of_daily_nutrition integer, c_date date, c_time char(20), PRIMARY KEY (name))<br/>
		recipes (name char(30), prep_time integer, rating integer, calories integer, PRIMARY KEY(name))<br/>
		nutrients (name char(30), calories integer, percent_daily_need integer, PRIMARY KEY(name))<br/>
		contains (mp_id char(20), m_name char(30), PRIMARY KEY (mp_id, m_name))<br/>
		to_prepare (m_name char(30), r_name char(30), primary key (m_name, r_name))<br/>
		ingredients (name char(30), food_group char(20), primary key (name))<br/>
		contain (portions char(20), n_name char(30), i_name char(30), m_name char(30), r_name char(30), primary key (n_name, i_name, m_name, r_name))<br/><br/>


	<hr />
    <h2> Analyze a Column</h2>
	<form method="POST" action="#">
    
            Type of Analysis: <input type="text" name="agType"> Note: Enter capitalized aggregation method (for example: SUM, COUNT ...) <br /><br />
            Nested Type of Analysis: <input type="text" name="agNestedType"> Note: Enter capitalized aggregation method (for example: SUM, COUNT ...) <br /><br />
            Column for group by: <input type="text" name="agColumn"> Note: Column Name <br /><br />
            Table that specifed column is in: <input type="text" name="agTable"> <br /><br />
            <p><input type="submit" value = "Calculate" id="avgAgeQueryRequest" name="avgAgeQueryRequest" ></p>
        </form>
        
	<h2>Back to main page</h2>
	<form method="POST" action="project.php">
            <p><input type="submit" value="Back" name="reset"></p>
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

        
        function handleAvgAgeRequest() {
            global $db_conn;
            //$table = $_POST['insTable'];
	    //$value = $_POST['insValue'];
        $type = $_POST['agType'];
        $nestedType = $_POST['agNestedType'];
        $column = $_POST['agColumn'];
        $table = $_POST['agTable'];
         $sql = executePlainSQL("SELECT $column AS avgval FROM Users WHERE $type(Age) <= ALL (SELECT $nestedType(u2.age) FROM Users u2) GROUP BY $column");
         console_log($sql);
         console_log("hello");
        //executePlainSQL("SELECT AVG(age) FROM users");
	    //echo "<br> Average age is $sql ";
        while ($row = OCI_Fetch_Array($sql, OCI_BOTH)) {
            echo "$type of $column in $table is: ";
            echo "<p>" . $row["AVGVAL"] . "</p>";
            console_log($row["AVGVAL"]);
            }
            OCICommit($db_conn);	
	    
	
        }
        function console_log( $data ){
            echo '<script>';
            echo 'console.log('. json_encode( $data ) .')';
            echo '</script>';
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
                } 
                else if (array_key_exists('avgAgeQueryRequest', $_POST)) {
                    //console_log("hello");
                    handleAvgAgeRequest();
                    //echo "Average age is 0";
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

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['avgAgeQueryRequest'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTupleRequest'])) {
            handleGETRequest();
	} else if (isset($_GET['displayTupleRequest'])) {
    	    handleGETRequest();
        } else if (isset($_GET['deleteTupleRequest'])) {
    	    handleGETRequest();
        }
		?>
	</body>
</html>

