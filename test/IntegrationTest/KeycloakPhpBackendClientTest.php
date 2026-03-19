<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for the PHP Backend M2M client (client_credentials grant).
 *
 * Requires both containers to be running:
 *   docker compose --profile keycloak up
 *
 * Environment variables:
 *   KEYCLOAK_BASE_URL      Keycloak base URL      (default: http://keycloak:8080)
 *   KEYCLOAK_REALM         Realm name             (default: gpdauth)
 *   KEYCLOAK_PHP_CLIENT_ID Client identifier      (default: php-backend-client)
 *   KEYCLOAK_PHP_SECRET    Client secret          (default: php-backend-secret-change-me)
 *   GQLPDSSAUTH_APP_PORT   PHP app external port  (default: 8080)
 */
class KeycloakPhpBackendClientTest extends TestCase
{
    private Client $http;
    private string $keycloakBase;
    private string $realm;
    private string $clientId;
    private string $clientSecret;
    private string $graphqlApi;

    protected function setUp(): void
    {
        $appPort      = getenv('GQLPDSSAUTH_APP_PORT')   ?: '8080';
        $this->realm        = getenv('KEYCLOAK_REALM')            ?: 'gpdauth';
        $this->clientId     = getenv('KEYCLOAK_PHP_CLIENT_ID')    ?: 'php-backend-client';
        $this->clientSecret = getenv('KEYCLOAK_PHP_SECRET')       ?: 'php-backend-secret-change-me';

        $this->keycloakBase = getenv('KEYCLOAK_BASE_URL') ?: 'http://keycloak:8080';
        $this->graphqlApi   = "http://localhost:{$appPort}/index.php/api";

        $this->http = new Client(['http_errors' => false, 'timeout' => 10]);
    }

    // -------------------------------------------------------------------------
    // Token acquisition
    // -------------------------------------------------------------------------

    public function testObtainAccessToken(): string
    {
        $token = $this->requestClientCredentialsToken();

        $this->assertNotEmpty($token, 'access_token must not be empty');

        return $token;
    }

    /**
     * @depends testObtainAccessToken
     */
    public function testTokenResponseContainsExpectedFields(string $token): string
    {
        $tokenData = $this->requestTokenData();

        $this->assertArrayHasKey('access_token', $tokenData, 'Response must contain access_token');
        $this->assertArrayHasKey('token_type', $tokenData,   'Response must contain token_type');
        $this->assertArrayHasKey('expires_in', $tokenData,   'Response must contain expires_in');
        $this->assertSame('Bearer', $tokenData['token_type'], 'token_type must be Bearer');

        return $tokenData['access_token'];
    }

    /**
     * @depends testObtainAccessToken
     */
    public function testAudienceClaimContainsClientId(string $token): string
    {
        $payload = $this->decodeJwtPayload($token);

        $this->assertArrayHasKey('sub', $payload, 'JWT payload must contain sub claim');
        $this->assertArrayHasKey('aud', $payload, 'JWT payload must contain aud claim');

        $audiences = is_array($payload['aud']) ? $payload['aud'] : [$payload['aud']];
        $this->assertContains(
            $this->clientId,
            $audiences,
            "aud claim must include '{$this->clientId}'"
        );

        return $token;
    }

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testIssuerClaimMatchesKeycloakRealm(string $token): string
    {
        $payload = $this->decodeJwtPayload($token);

        $expectedIss = "{$this->keycloakBase}/realms/{$this->realm}";
        $this->assertSame(
            $expectedIss,
            $payload['iss'] ?? '',
            "iss claim must be '{$expectedIss}'"
        );

        return $token;
    }

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testTokenIsNotExpired(string $token): void
    {
        $payload = $this->decodeJwtPayload($token);

        $this->assertArrayHasKey('exp', $payload, 'JWT must contain exp claim');
        $this->assertGreaterThan(
            time(),
            $payload['exp'],
            'Token must not be expired'
        );
    }

    // -------------------------------------------------------------------------
    // GraphQL call with M2M token
    // -------------------------------------------------------------------------

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testUnauthenticatedCallReturnsError(): void
    {
        if (!$this->isAppValidationEnabled()) {
            $this->markTestSkipped('Set KEYCLOAK_TEST_VALIDATE_APP=1 to validate protected GraphQL endpoint.');
        }

        $result = $this->callEchoProtected('Hello M2M', null);

        $this->assertArrayHasKey('errors', $result, 'Unauthenticated call must return GraphQL errors');
    }

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testAuthenticatedCallWithM2MToken(string $token): void
    {
        if (!$this->isAppValidationEnabled()) {
            $this->markTestSkipped('Set KEYCLOAK_TEST_VALIDATE_APP=1 to validate protected GraphQL endpoint.');
        }

        $message = 'Hello M2M';
        $result  = $this->callEchoProtected($message, $token);

        $this->assertArrayNotHasKey(
            'errors',
            $result,
            'Authenticated M2M call must not return GraphQL errors: ' . json_encode($result['errors'] ?? [])
        );
        $this->assertNotEmpty(
            $result['data']['msg'] ?? '',
            'echoProtected must return a non-empty response'
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function requestTokenData(): array
    {
        $tokenUrl = "{$this->keycloakBase}/realms/{$this->realm}/protocol/openid-connect/token";

        $response = $this->http->post($tokenUrl, [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Keycloak token endpoint must return 200. Body: ' . $response->getBody()
        );

        return json_decode((string) $response->getBody(), true);
    }

    private function requestClientCredentialsToken(): string
    {
        return $this->requestTokenData()['access_token'] ?? '';
    }

    private function decodeJwtPayload(string $token): array
    {
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'Token must be a valid JWT with 3 parts');

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertIsArray($payload, 'JWT payload must be a valid JSON object');

        return $payload;
    }

    private function callEchoProtected(string $message, ?string $bearerToken): array
    {
        $headers = ['Content-Type' => 'application/json'];
        if ($bearerToken !== null) {
            $headers['Authorization'] = "Bearer {$bearerToken}";
        }

        $response = $this->http->post($this->graphqlApi, [
            'headers' => $headers,
            'json'    => [
                'query'     => 'query Q($msg: String!){ msg: echoProtected(message: $msg) }',
                'variables' => ['msg' => $message],
            ],
        ]);
        $body = (string) $response->getBody();
        return json_decode($body, true) ?? [];
    }

    private function isAppValidationEnabled(): bool
    {
        $value = getenv('KEYCLOAK_TEST_VALIDATE_APP');
        return $value === '1' || strtolower((string) $value) === 'true';
    }
}
