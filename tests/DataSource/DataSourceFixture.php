<?php
namespace Atlas\Table\DataSource;

use Atlas\Pdo\Connection;

class DataSourceFixture
{
    protected $connection;

    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }

    public function exec()
    {
        if ($this->connection === null) {
            $this->connection = Connection::new('sqlite::memory:');
        }

        $this->employees();
        $this->nopkeys();
        $this->courses();

        // return the connection used
        return $this->connection;
    }

    protected function employees()
    {
        $this->connection->query("CREATE TABLE employees (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            name     VARCHAR(10) NOT NULL UNIQUE,
            building INTEGER,
            floor    INTEGER
        )");

        $stm = "INSERT INTO employees (name, building, floor) VALUES (?, ?, ?)";
        $rows = [
            ['Anna',  1, 1],
            ['Betty', 1, 2],
            ['Clara', 1, 3],
            ['Donna', 1, 1],
            ['Edna',  1, 2],
            ['Fiona', 1, 3],
            ['Gina',  2, 1],
            ['Hanna', 2, 2],
            ['Ione',  2, 3],
            ['Julia', 2, 1],
            ['Kara',  2, 2],
            ['Lana',  2, 3],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }

    // no primary keys
    public function nopkeys()
    {
        $this->connection->query("CREATE TABLE nopkeys (
            name VARCHAR(255),
            email VARCHAR(255)
        )");
    }

    // composite keys
    protected function courses()
    {
        $this->connection->query("CREATE TABLE courses (
            course_subject CHAR(4),
            course_number INT,
            title VARCHAR(20),
            PRIMARY KEY (course_subject, course_number)
        )");

        $stm = "INSERT INTO courses (course_subject, course_number, title) VALUES (?, ?, ?)";
        $rows = [
            ['ENGL', 100, 'Composition'],
            ['ENGL', 200, 'Creative Writing'],
            ['ENGL', 300, 'Shakespeare'],
            ['ENGL', 400, 'Dickens'],
            ['HIST', 100, 'World History'],
            ['HIST', 200, 'US History'],
            ['HIST', 300, 'Victorian History'],
            ['HIST', 400, 'Recent History'],
            ['MATH', 100, 'Algebra'],
            ['MATH', 200, 'Trigonometry'],
            ['MATH', 300, 'Calculus'],
            ['MATH', 400, 'Statistics'],
        ];
        foreach ($rows as $row) {
            $this->connection->perform($stm, $row);
        }
    }
}
