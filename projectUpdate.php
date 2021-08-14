<!-- reference to Test Oracle file for UBC CPSC304 2018 Winter Term 1
 -->

<html>
    <head>
        <title>Update Data</title>
    </head>

    <body>

        <h2>Insert values into any table you'd like to update</h2>
        <form method="POST" action="projectUpdate.php"> <!--refresh page when submitted-->
            <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">

		<h3>Useful informations to refer to when trying to update data</h3>
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

	    
            Table: <input type="text" name="updTable"> Example: ingredients<br /><br />
            Column: <input type="text" name="updColumn"> Example: name<br /><br />
            Condition: <input type="text" name="updCondition"> Example: name='raspberry'<br /><br />
            NewValue: <input type="text" name="updNewValue"> Example: 'UpdatedValue'<br /><br />
		Don't forget to put apostrophes for value for condition and NewValue<br /><br />

            <input type="submit" value="Update" name="updateSubmit"></p>
        </form>


	<hr />

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


            $table = $_POST['updTable'];
            $column = $_POST['updColumn'];
            $condition = $_POST['updCondition'];
            $newVal = $_POST['updNewValue'];

            // you need the wrap the old name and new name values with single quotations

            executePlainSQL("UPDATE {$table} set {$column}={$newVal} where {$condition}");
            OCICommit($db_conn);
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

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
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

