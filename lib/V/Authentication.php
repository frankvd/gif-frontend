<?php
namespace V;

use PDO;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;

class Authentication {
    /**
     * @var \Lcobucci\JWT\Builder
    **/
    protected $builder;

    /**
     * @var \PDO
    **/
    protected $db;

    /**
     * @var string
    **/
    protected $secret;

    /**
     * @var \Lcobucci\JWT\Signer
    **/
    protected $signer;

    /**
     * @var \Lcobucci\JWT\Parser
    **/
    protected $parser;

    /**
     * Constructor
     *
     * @param PDO $db
     * @param \Lcobucci\JWT\Signer $signer
     * @param \Lcobucci\JWT\Builder $builder
     * @param \Lcobucci\JWT\Parser $parser
     * @param string $secret
    **/
    public function __construct(PDO $db, Signer $signer, Builder $builder, Parser $parser, $secret) {
        $this->db = $db;
        $this->signer = $signer;
        $this->builder = $builder;
        $this->parser = $parser;
        $this->secret = $secret;
    }

    /**
     * Create JWT token
     *
     * Returns a new JWT token for the given user
     *
     * @param string $username Username
     *
     * @return \Lcobucci\JWT\Token
    **/
    public function createTokenForUser($username) {
        $token = $this->builder
            ->setIssuer($username) // Configures the issuer (iss claim)
            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
            ->setExpiration(time() + 3600) // Configures the expiration time of the token (exp claim)
            ->sign($this->signer, $this->secret) // creates a signature using "testing" as key
            ->getToken(); // Retrieves the generated token

        return $token;
    }

    /**
     * Login
     *
     * Attempts to login a user with the given username and password
     *
     * @param string $username Username
     * @param string $passsword Password
     *
     * @return bool
    **/
    public function login($username, $password) {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE username = ?');
        $stmt->execute([$username]);

        $row = $stmt->fetchObject();

        if (!$row) {
            return false;
        }

        if (password_verify($password, $row->password)) {
            return true;
        }

        return false;
    }

    /**
     * Register a new user
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     **/
    public function register($username, $password) {
        $stmt = $this->db->prepare('INSERT INTO user VALUES(:username, :password)');
        $stmt->execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Verify JWT token
     *
     * Verifies the given JWT token
     *
     * @param string $token
     *
     * @return bool
    **/
    public function verifyToken($token) {
        try {
            $token = $this->parser->parse($token);
        } catch (\Exception $e) {
            return false;
        }
        if (!$token->verify($this->signer, $this->secret)) {
            return false;
        }

        return true;
    }
}