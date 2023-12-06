<?php

use GQLBasicClient\GQLClient;
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    /**
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     *
     * @var GQLClient
     */
    private $gqlClient;

    /**
     * This method is called before the first test of this test class is run.
     */
    protected function setUp(): void
    {
        global $entityManager;
        global $graphqlClient;
        $this->entityManager = $entityManager;
        $this->gqlClient = $graphqlClient;
    }

    public function testLogin()
    {
        $data = $this->login();
        $this->assertNotEmpty($data["jwt"], "expect login without errors");
    }
    private function  login(): array
    {
        $query = '
      query QueryLogin($username: String!, $password: String!){
        login(username: $username,password:$password){
          jwt
          permissions {
            resource
            access
            value
            scope
          }
        }
      }
      
      ';
        $variables = [
            "username" => "p.lopez",
            "password" => "demo###"
        ];
        $result = $this->gqlClient->execute($query, $variables);
        return $result["data"]["login"];
    }
}
