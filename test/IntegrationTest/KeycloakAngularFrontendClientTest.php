<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for the Angular Frontend client (Resource Owner Password grant).
 *
 * directAccessGrantsEnabled is set to true in the realm JSON specifically to
 * allow automated testing of the user login flow without a browser.
 *
 * Requires both containers to be running:
 *   docker compose --profile keycloak up
 *
 * Environment variables:
 *   KEYCLOAK_BASE_URL          Keycloak base URL        (default: http://keycloak:8080)
 *   KEYCLOAK_REALM             Realm name               (default: gpdauth)
 *   KEYCLOAK_ANGULAR_CLIENT_ID Angular client id        (default: angular-frontend-client)
 *   KEYCLOAK_APP_USER          Test user username       (default: appuser)
 *   KEYCLOAK_APP_PASSWORD      Test user password       (default: appuser123)
 *   GQLPDSSAUTH_APP_PORT       PHP app external port    (default: 8080)
 */
class KeycloakAngularFrontendClientTest extends TestCase
{
    private Client $http;
    private string $keycloakBase;
    private string $realm;
    private string $clientId;
    private string $username;
    private string $password;
    private string $graphqlApi;

    protected function setUp(): void
    {
        $appPort      = getenv('GQLPDSSAUTH_APP_PORT')        ?: '8080';
        $this->realm    = getenv('KEYCLOAK_REALM')                ?: 'gpdauth';
        $this->clientId = getenv('KEYCLOAK_ANGULAR_CLIENT_ID')    ?: 'angular-frontend-client';
        $this->username = getenv('KEYCLOAK_APP_USER')             ?: 'appuser';
        $this->password = getenv('KEYCLOAK_APP_PASSWORD')         ?: 'appuser123';

        $this->keycloakBase = getenv('KEYCLOAK_BASE_URL') ?: 'http://keycloak:8080';
        $this->graphqlApi   = "http://localhost:{$appPort}/index.php/api";

        $this->http = new Client(['http_errors' => false, 'timeout' => 10]);
    }

    // -------------------------------------------------------------------------
    // Token acquisition
    // -------------------------------------------------------------------------

    public function testObtainUserAccessToken(): string
    {
        $token = $this->requestPasswordGrantToken();

        $this->assertNotEmpty($token, 'access_token must not be empty');

        return $token;
    }

    /**
     * @depends testObtainUserAccessToken
     */
    public function testTokenResponseContainsExpectedFields(string $token): string
    {
        $tokenData = $this->requestTokenData();

        $this->assertArrayHasKey('access_token', $tokenData,  'Response must contain access_token');
        $this->assertArrayHasKey('token_type', $tokenData,    'Response must contain token_type');
        $this->assertArrayHasKey('expires_in', $tokenData,    'Response must contain expires_in');
        $this->assertArrayHasKey('refresh_token', $tokenData, 'Response must contain refresh_token');
        $this->assertSame('Bearer', $tokenData['token_type'],  'token_type must be Bearer');

        return $tokenData['access_token'];
    }

    /**
     * @depends testObtainUserAccessToken
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
    public function testSubjectClaimMatchesUsername(string $token): string
    {
        $payload = $this->decodeJwtPayload($token);

        // Keycloak puts the internal UUID in 'sub'; preferred_username holds the login name
        $this->assertArrayHasKey('preferred_username', $payload, 'JWT must contain preferred_username');
        $this->assertSame(
            $this->username,
            $payload['preferred_username'],
            "preferred_username must be '{$this->username}'"
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
        $this->assertGreaterThan(time(), $payload['exp'], 'Token must not be expired');
    }

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testUserHasAppUserRole(string $token): void
    {
        $payload = $this->decodeJwtPayload($token);

        $realmRoles = $payload['realm_access']['roles'] ?? [];
        $this->assertContains(
            'app-user',
            $realmRoles,
            "User must have the 'app-user' realm role"
        );
    }

    // -------------------------------------------------------------------------
    // GraphQL calls with user token
    // -------------------------------------------------------------------------

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testUnauthenticatedCallReturnsError(): void
    {
        if (!$this->isAppValidationEnabled()) {
            $this->markTestSkipped('Set KEYCLOAK_TEST_VALIDATE_APP=1 to validate protected GraphQL endpoint.');
        }

        $result = $this->callEchoProtected('Hello Angular', null);

        $this->assertArrayHasKey('errors', $result, 'Unauthenticated call must return GraphQL errors');
    }

    /**
     * @depends testAudienceClaimContainsClientId
     */
    public function testAuthenticatedCallWithUserToken(string $token): void
    {
        if (!$this->isAppValidationEnabled()) {
            $this->markTestSkipped('Set KEYCLOAK_TEST_VALIDATE_APP=1 to validate protected GraphQL endpoint.');
        }

        $message = 'Hello Angular';
        $result  = $this->callEchoProtected($message, $token);

        $this->assertArrayNotHasKey(
            'errors',
            $result,
            'Authenticated user call must not return GraphQL errors: ' . json_encode($result['errors'] ?? [])
        );
        $this->assertNotEmpty(
            $result['data']['msg'] ?? '',
            'echoProtected must return a non-empty response'
        );
    }

    /**
     * The response message should contain the authenticated username.
     *
     * @depends testAuthenticatedCallWithUserToken
     */
    public function testResponseMessageContainsUsername(): void
    {
        if (!$this->isAppValidationEnabled()) {
            $this->markTestSkipped('Set KEYCLOAK_TEST_VALIDATE_APP=1 to validate protected GraphQL endpoint.');
        }

        $token   = $this->requestPasswordGrantToken();
        $message = 'Hello Angular';
        $result  = $this->callEchoProtected($message, $token);

        $this->assertStringContainsString(
            $this->username,
            $result['data']['msg'] ?? '',
            "echoProtected response must contain the username '{$this->username}'"
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
                'grant_type' => 'password',
                'client_id'  => $this->clientId,
                'username'   => $this->username,
                'password'   => $this->password,
                'scope'      => 'openid',
            ],
        ]);

        $status = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true) ?? [];
        if ($status !== 200 && (($body['error'] ?? '') === 'unauthorized_client')) {
            $this->markTestSkipped(
                'angular-frontend-client does not allow direct access grants yet. '
                    . 'Reimport Keycloak realm (docker compose --profile keycloak down -v && up) '
                    . 'or enable directAccessGrantsEnabled=true in client config.'
            );
        }

        $this->assertSame(
            200,
            $status,
            'Keycloak token endpoint must return 200. Body: ' . json_encode($body)
        );

        return $body;
    }

    private function requestPasswordGrantToken(): string
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
