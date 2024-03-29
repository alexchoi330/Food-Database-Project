<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.  
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values
 
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED
  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the 
  OCILogon below to be your ORACLE username and password -->

<html>
    <head>
        <title>Nutrition and Meal</title>
    </head>

    <body>
        <h2>Reset</h2>
        <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

        <form method="POST" action="project.php">
            <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

     	<hr />
	
        <h2>Generate Data</h2>
        <form method="POST" action="project.php"> <!--refresh page when submitted-->
            <input type="hidden" id="generateData" name="generateData">
            <input type="submit" name="reset"></p>
        </form>

	

	<h2>Display Data</h2>
        <form method="GET" action="project.php"> <!--refresh page when submitted-->
            <input type="hidden" id="displayTupleRequest" name="displayTupleRequest">
            <input type="submit" name="displayTuples"></p>
        </form>

	<hr />

	<h2>List of SQL queries</h2>	
        <form method="POST" action="projectInsert.php">
            <p><input type="submit" value="Insert" name="reset"></p>
        </form>
	
        <form method="POST" action="projectDelete.php">
            <p><input type="submit" value="Delete" name="reset"></p>
        </form>
	
        <form method="POST" action="projectUpdate.php">
            <p><input type="submit" value="Update" name="reset"></p>
        </form>

        <form method="POST" action="projectSelect.php">
            <p><input type="submit" value="Selection" name="reset"></p>
        </form>
	
        <form method="POST" action="projectProject.php">
            <p><input type="submit" value="Projection" name="reset"></p>
        </form>

        <form method="POST" action="projectJoin.php">
            <p><input type="submit" value="Join" name="reset"></p>
        </form>

        <form method="POST" action="projectAggregation.php">
            <p><input type="submit" value="Aggregation" name="reset"></p>
        </form>
	
        <form method="POST" action="projectNested.php">
            <p><input type="submit" value="Nested Aggregation with group-by" name="reset"></p>
        </form>

        <form method="POST" action="projectDivision.php">
            <p><input type="submit" value="Division" name="reset"></p>
        </form>
	<hr />





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
            $db_conn = OCILogon("ora_studentIdHere", "student_number_here", 
"dbhost.students.cs.ubc.ca:1522/stu");

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
		echo "<br> Resetting data <br>";
            executePlainSQL("DROP TABLE users cascade constraints");
            executePlainSQL("DROP TABLE meal_plan cascade constraints");
            executePlainSQL("DROP TABLE gets cascade constraints");
            executePlainSQL("DROP TABLE goals cascade constraints");
            executePlainSQL("DROP TABLE has_goals cascade constraints");
            executePlainSQL("DROP TABLE has_dietary_restrictions cascade constraints");
            executePlainSQL("DROP TABLE meals cascade constraints");
            executePlainSQL("DROP TABLE recipes cascade constraints");
            executePlainSQL("DROP TABLE to_prepare cascade constraints");
            executePlainSQL("DROP TABLE ingredients cascade constraints");
            executePlainSQL("DROP TABLE nutrients cascade constraints");
            executePlainSQL("DROP TABLE contain cascade constraints");

            executePlainSQL("DROP TABLE contains cascade constraints");
	    
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

        function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM demoTable");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
            }
        }

	function handleDisplayRequest() {
	global $db_conn;
	
	$result = executePlainSQL("select * from users");
		echo "<br> Table: users <br>";
		echo "<table>";
		echo "<tr><th>email</th><th>password</th><th>name</th><th>age</th><th>b_date</th><th>gender</th><th>weight</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td><td>" . $row[5] . "</td><td>" . $row[6] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from meal_plan");
		echo "<br> Table: meal_plan <br>";
		echo "<table>";
		echo "<tr><th>id</th><th>period</th><th>calories_per_day</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from gets");
		echo "<br> Table: gets <br>";
		echo "<table>";
		echo "<tr><th>meal_name</th><th>day_of_week</th><th>time</th><th>u_email</th><th>mp_id</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from goals");
		echo "<br> Table: goals <br>";
		echo "<table>";
		echo "<tr><th>name</th><th>dateSet</th><th>deadline</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from has_goals");
		echo "<br> Table: has_goals <br>";
		echo "<table>";
		echo "<tr><th>u_email</th><th>g_name</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from has_dietary_restrictions");
		echo "<br> Table: has_dietary_restrictions <br>";
		echo "<table>";
		echo "<tr><th>u_email</th><th>dr_id</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from meals");
		echo "<br> Table: meals <br>";
		echo "<table>";
		echo "<tr><th>name</th><th>calories</th><th>percent_of_daily_nutrition</th><th>c_date</th><th>c_time</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td></tr>"; 
	}
	echo "</table>";
	
	$result = executePlainSQL("select * from recipes");
		echo "<br> Table: recipes <br>";
		echo "<table>";
		echo "<tr><th>name</th><th>prep_time</th><th>rating</th><th>calories</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from nutrients");
		echo "<br> Table: nutrients <br>";
		echo "<table>";
		echo "<tr><th>name</th><th>calories</th><th>percent_daily_need</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from contains");
		echo "<br> Table: contains <br>";
		echo "<table>";
		echo "<tr><th>mp_id</th><th>m_name</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from to_prepare");
		echo "<br> Table: to_prepare <br>";
		echo "<table>";
		echo "<tr><th>m_name</th><th>r_name</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from ingredients");
		echo "<br> Table: ingredients <br>";
		echo "<table>";
		echo "<tr><th>name</th><th>food_group</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; 
	}
	echo "</table>";

	$result = executePlainSQL("select * from contain");
		echo "<br> Table: contain <br>";
		echo "<table>";
		echo "<tr><th>portions</th><th>n_name</th><th>i_name</th><th>m_name</th><th>r_name</th></tr>";

	        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td></tr>";
	}
	echo "</table>";

	}

	function handleGenerateData() {
	global $db_conn;
	echo "<br> generating tables <br>";
	executePlainSQL("create table users (email varchar(50), password char(20), name char(20), age integer, b_date date, gender varchar(5), weight integer, PRIMARY KEY(email))");
	executePlainSQL("create table meal_plan (id char(20), period integer, calories_per_day integer, PRIMARY KEY(id))");
	executePlainSQL("create table gets (meal_name char(30), day_of_week char(20), time char(30), u_email varchar(50), mp_id char(20), PRIMARY KEY(meal_name, u_email, mp_id), FOREIGN KEY(u_email) references users, foreign key(mp_id) references meal_plan)");
	executePlainSQL("create table goals (name char(20) PRIMARY KEY, dateSet date, deadline date)");
	executePlainSQL("create table has_goals (u_email varchar(50), g_name char(20), PRIMARY KEY(u_email, g_name), FOREIGN KEY(u_email) references users(email) ON DELETE CASCADE, FOREIGN KEY(g_name) references goals(name) ON DELETE CASCADE)");
	executePlainSQL("create table has_dietary_restrictions (u_email varchar(50) PRIMARY KEY, dr_id char(20), FOREIGN KEY(u_email) references users ON DELETE CASCADE)");
	executePlainSQL("create table meals (name char(30), calories integer, percent_of_daily_nutrition integer, c_date date, c_time char(20), PRIMARY KEY (name))");
	executePlainSQL("create table recipes (name char(30), prep_time integer, rating integer, calories integer, PRIMARY KEY(name))");
	executePlainSQL("create table nutrients (name char(30), calories integer, percent_daily_need integer, PRIMARY KEY(name))");

	executePlainSQL("create table contains (mp_id char(20), m_name char(30), PRIMARY KEY (mp_id, m_name), FOREIGN KEY (mp_id) references meal_plan on delete cascade, FOREIGN KEY (m_name) references meals on delete set null)");
	executePlainSQL("create table to_prepare (m_name char(30), r_name char(30), primary key (m_name, r_name), foreign key (m_name) references meals, foreign key (r_name) references recipes)");
	executePlainSQL("create table ingredients (name char(30), food_group char(20), primary key (name))");
	executePlainSQL("create table contain (portions char(20), n_name char(30), i_name char(30), m_name char(30), r_name char(30), primary key (n_name, i_name, m_name, r_name), foreign key (n_name) references nutrients, foreign key (i_name) references ingredients, foreign key (m_name) references meals, foreign key (r_name) references recipes)");

	OCICommit($db_conn);
	}
	
	function handleData() {
	global $db_conn;
	echo "<br> generating data <br>";
	executePlainSQL("insert into users values ('sheetal@ubc.ca', '304year2021', 'Sheetal', 22, date '1998-08-02', 'F', 100)");
	executePlainSQL("insert into users values ('pedro@ubc.ca', 'easyrun', 'Pedro', 21, date '1999-05-04', 'M', 135)");
	executePlainSQL("insert into users values ('alex@ubc.ca', 'popcan92', 'Alex', 20, date '2000-03-23', 'M', 135)");
	executePlainSQL("insert into users values ('mel@gmail.com', 'lionfur34', 'Mel', 25, date '1995-01-11', 'F', 115)");
	executePlainSQL("insert into users values ('jason@gmail.com', 'a1b2c3d4e5', 'Jason', 32, date '1988-06-07', 'M', 160)");

	executePlainSQL("insert into meal_plan values ('fruit-cleanse-b21', 7, 1800)");
	executePlainSQL("insert into meal_plan values ('high-protein', 30, 2200)");
	executePlainSQL("insert into meal_plan values ('low-fat', 30, 2000)");
	executePlainSQL("insert into meal_plan values ('low-sugar', 30, 2000)");
	executePlainSQL("insert into meal_plan values ('vegetarian', 90, 2000)");

	executePlainSQL("insert into gets values ('berry-smoothie', 'Monday', '8:00', 'sheetal@ubc.ca', 'fruit-cleanse-b21')");
	executePlainSQL("insert into gets values ('shrimp-quinoa', 'Tuesday', '8:00', 'pedro@ubc.ca', 'high-protein')");
	executePlainSQL("insert into gets values ('lentil-salad', 'Thursday', '8:00', 'alex@ubc.ca', 'low-fat')");
	executePlainSQL("insert into gets values ('carrot-bite', 'Monday', '8:00', 'mel@gmail.com', 'low-sugar')");
	executePlainSQL("insert into gets values ('chickpea-pasta', 'Sunday', '8:00', 'jason@gmail.com', 'vegetarian')");

	executePlainSQL("insert into goals values ('immune-boost', date '2021-05-31', date '2021-06-07')");
	executePlainSQL("insert into goals values ('increase-protein', date '2021-05-31', date '2021-07-01')");
	executePlainSQL("insert into goals values ('decrease-fat', date '2021-05-31', date '2021-07-01')");
	executePlainSQL("insert into goals values ('decrease-sugar', date '2021-05-31', date '2021-07-01')");
	executePlainSQL("insert into goals values ('dont-eat-meat', date '2021-05-31', date '2021-08-31')");

	executePlainSQL("insert into has_goals values ('sheetal@ubc.ca', 'immune-boost')");
	executePlainSQL("insert into has_goals values ('pedro@ubc.ca', 'increase-protein')");
	executePlainSQL("insert into has_goals values ('alex@ubc.ca', 'decrease-fat')");
	executePlainSQL("insert into has_goals values ('mel@gmail.com', 'decrease-sugar')");
	executePlainSQL("insert into has_goals values ('jason@gmail.com', 'dont-eat-meat')");

	executePlainSQL("insert into has_dietary_restrictions values ('sheetal@ubc.ca', 'neg-egg')");
	executePlainSQL("insert into has_dietary_restrictions values ('pedro@ubc.ca', 'neg-peanuts')");
	executePlainSQL("insert into has_dietary_restrictions values ('alex@ubc.ca', 'none')");
	executePlainSQL("insert into has_dietary_restrictions values ('mel@gmail.com', 'none')");
	executePlainSQL("insert into has_dietary_restrictions values ('jason@gmail.com', 'neg-gluten')");

	executePlainSQL("insert into meals values ('berry-smoothie', 8, 156, date '2021-05-31', '8:00 AM')");
	executePlainSQL("insert into meals values ('shrimp-quinoa', 15, 343, date '2021-06-01', '8:00 AM')");
	executePlainSQL("insert into meals values ('lentil-salad', 15, 312, date '2021-06-03', '8:00 AM')");
	executePlainSQL("insert into meals values ('carrot-bite', 2, 48, date '2021-05-31', '8:00 AM')");
	executePlainSQL("insert into meals values ('chickpea-pasta', 17, 349, date '2021-06-05', '8:00 AM')");

	executePlainSQL("insert into recipes values ('berry-smoothie', 10, 4, 156)");
	executePlainSQL("insert into recipes values ('shrimp-quinoa', 30, 5, 343)");
	executePlainSQL("insert into recipes values ('lentil-salad', 15, 4, 312)");
	executePlainSQL("insert into recipes values ('carrot-bite', 10, 4, 48)");
	executePlainSQL("insert into recipes values ('chickpea-pasta', 30, 5, 349)");

	executePlainSQL("insert into nutrients values ('shrimp', 156, 7)");
	executePlainSQL("insert into nutrients values ('raspberry', 65, 3)");
	executePlainSQL("insert into nutrients values ('lentil', 230, 11)");
	executePlainSQL("insert into nutrients values ('chickpea', 200, 10)");
	executePlainSQL("insert into nutrients values ('carrot', 50, 2)");

	executePlainSQL("insert into contains values ('fruit-cleanse-b21', 'berry-smoothie')");
	executePlainSQL("insert into contains values ('high-protein', 'shrimp-quinoa')");
	executePlainSQL("insert into contains values ('vegetarian', 'chickpea-pasta')");
	executePlainSQL("insert into contains values ('low-sugar', 'lentil-salad')");
	executePlainSQL("insert into contains values ('low-fat', 'carrot-bite')");

	executePlainSQL("insert into to_prepare values ('berry-smoothie', 'berry-smoothie')");
	executePlainSQL("insert into to_prepare values ('shrimp-quinoa', 'shrimp-quinoa')");
	executePlainSQL("insert into to_prepare values ('lentil-salad', 'lentil-salad')");
	executePlainSQL("insert into to_prepare values ('carrot-bite', 'carrot-bite')");
	executePlainSQL("insert into to_prepare values ('chickpea-pasta', 'chickpea-pasta')");

	executePlainSQL("insert into ingredients values ('raspberry', 'fruits')");
	executePlainSQL("insert into ingredients values ('shrimp', 'protein')");
	executePlainSQL("insert into ingredients values ('lentil', 'vegetable/legume')");
	executePlainSQL("insert into ingredients values ('chickpea', 'vegetable/legume')");
	executePlainSQL("insert into ingredients values ('carrot', 'vegetable/legume')");

	executePlainSQL("insert into contain values (1, 'shrimp', 'shrimp', 'berry-smoothie', 'berry-smoothie')");
	executePlainSQL("insert into contain values (1, 'raspberry', 'raspberry', 'berry-smoothie', 'berry-smoothie')");
	executePlainSQL("insert into contain values (1, 'lentil', 'lentil', 'lentil-salad', 'lentil-salad')");
	executePlainSQL("insert into contain values (1, 'carrot', 'carrot', 'chickpea-pasta', 'chickpea-pasta')");
	executePlainSQL("insert into contain values (1, 'chickpea', 'chickpea', 'chickpea-pasta', 'chickpea-pasta')");

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
                } else if (array_key_exists('generateData', $_POST)) {
		    handleGenerateData();	
		    handleData();
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
		}

                disconnectFromDB();
            }
        }

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTupleRequest']) || isset($_GET['displayTupleRequest'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>
