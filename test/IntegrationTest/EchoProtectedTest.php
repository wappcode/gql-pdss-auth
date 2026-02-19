<?php

use GQLBasicClient\GQLClientException;
use PHPUnit\Framework\TestCase;

class EchoProtectedTest extends TestCase
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

  public function testUnauthenticated()
  {
    $message = "Hola";

    try {
      $data = $this->echo($message);
    } catch (GQLClientException $ex) {
      $data = $ex->getContext();
    }
    $this->assertNotEmpty($data["errors"], "Check error unauthenticated session");
  }
  public function testAuthenticated()
  {
    $message = "Hola";
    $username = 'p.lopez';
    $password = 'demo###';

    try {
      $data = $this->echoPlusLogin($message, $username, $password);
    } catch (GQLClientException $ex) {
      $data = $ex->getContext();
    }
    $this->assertEquals($data["data"]["msg"], "{$message} -> Usuario: {$username}", "Check authenticated session");
  }

  private function  echo(string $message)
  {
    $query = '
      query QueryEcho($message: String!){
        msg: echoProtected(message:$message)
      }
      
      ';
    $variables = [
      "message" => $message,
    ];

    $result = $this->gqlClient->execute($query, $variables);
    return $result;
  }
  private function  echoPlusLogin(string $message, string $username, string $password)
  {
    $query = '
      query QueryEcho($message: String!){
        login(username: "' . $username . '", password: "' . $password . '"){
          fullName
        }
        msg: echoProtected(message:$message)
      }
      
      ';
    $variables = [
      "message" => $message,
    ];

    $result = $this->gqlClient->execute($query, $variables);
    return $result;
  }
}
