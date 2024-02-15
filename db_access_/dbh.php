<?php


class DBH
{

    /**
     * @var DBH
     * @access private
     * @static
     */
    private static $_instance = null;


    private \PDO $dbh;

    private $options = [
        'cost' => 12,
    ];

    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct()
    {
        $options = [
            'cost' => 12,
        ];
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new DBH();
            $dsn = 'mysql:host=localhost;dbname=jeudelavie';
            $username = 'root';
            $password = 'root';
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ];
            self::$_instance->dbh = new \PDO($dsn, $username, $password, $options);
            
        } 

        return self::$_instance;
    }


    public function createUser($user)
    {
        try {

            echo $user['password'];
            $sql = "INSERT INTO user (name, password)
                 VALUES ( ?, ?)";

            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([$user['name'], password_hash($user['password'], PASSWORD_BCRYPT, $this->options)]);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }

    public function getUserByName($name)
    {
        try {
            $sql = "SELECT * FROM user WHERE name =  ?";

            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([$name]);
            $user = $stmt->fetch(PDO::FETCH_DEFAULT);
            return $user;
        } catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        }
    }
}
