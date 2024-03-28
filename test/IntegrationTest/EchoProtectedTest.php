<?php

use GQLBasicClient\GQLClient;
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

  public function testInvalidToken()
  {
    $message = "Hola";
    $jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsb2NhbGhvc3QiLCJzdWIiOiJwLmxvcGV6IiwiZXhwIjoxNzAxODg4MjkyLCJpYXQiOjE3MDE4ODcwOTIsImp0aSI6ImxvY2FsaG9zdDo6cC5sb3BleiIsIm5hbWUiOiJQYW5jaG8gTFx1MDBmM3BleiIsImdpdmVuX25hbWUiOiJQYW5jaG8iLCJmYW1pbHlfbmFtZSI6IkxcdTAwZjNwZXoiLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJwLmxvcGV6IiwiZW1haWwiOiJwLmxvcGV6QGRlbW8ubG9jYWwubGFuIiwiYXV0aF90aW1lIjoxNzAxODg3MDkyLCJleGkiOjEyMDAsImJpcnRoX2ZhbWlseV9uYW1lIjoiTFx1MDBmM3BleiIsImJpcnRoX2dpdmVuX25hbWUiOiJQYW5jaG8ifQ.nTs126usQ_vls3nWU8XdV1EaIjdEB7tbFXtL6NqE3CM";
    $data = $this->echo($message, $jwt);
    $this->assertNotEmpty($data["errors"], "Check error with invalid jwt");
  }
  public function testValidToken()
  {
    $message = "Hola";
    $jwt = $this->getValidJWT();
    $data = $this->echo($message, $jwt);
    $this->assertNotEmpty($data["data"], "Check data with valid jwt");
    $this->assertStringContainsString($message, $data["data"]["msg"], "Check echo message with valid jwt");
  }
  private function  echo(string $message, string $jwt)
  {
    $query = '
      query QueryEcho($message: String!){
        msg: echoProtected(message:$message)
      }
      
      ';
    $variables = [
      "message" => $message,
    ];
    $headers = [
      "Authorization: Bearer " . $jwt
    ];
    $result = $this->gqlClient->execute($query, $variables, $headers);
    return $result;
  }
  private function  getValidJWT(): string
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
    return $result["data"]["login"]["jwt"];
  }
}
