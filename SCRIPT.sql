DROP TABLES FOR RESET
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
	  

CREATING TABLES
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

INSERTING EXAMPLES
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
