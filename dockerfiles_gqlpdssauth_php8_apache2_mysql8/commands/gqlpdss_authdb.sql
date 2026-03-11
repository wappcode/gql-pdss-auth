INSERT INTO gpd_auth_users
(id,  firstname, lastname, email, username, `algorithm`, salt, user_password, password_expiration, picture, active, last_login,created, updated)
VALUES 
(1,'Pancho','López','p.lopez@demo.local.lan','p.lopez','sha256',NULL,sha2("demo###",256) ,NULL,NULL,1,NULL,'2023-12-06 01:05:59','2023-12-06 01:05:59');

INSERT INTO `gpd_auth_resources` 
(id,  code, title, description, scopes,created, updated)
VALUES
(1,'test_resource','Test Resource','','[\"ALL\"]','2023-12-06 17:56:52','2023-12-06 17:56:52');

INSERT INTO gqlpdss_authdb.gpd_auth_permissions
(id, created, updated, permission_access, permision_value, `scope`, resource_id, user_id, role_id)
VALUES(1, now(), now(), 'ALLOW', 'ALL', 'ALL', 1, 1, null);

INSERT INTO gqlpdss_authdb.gpd_auth_trusted_issuers
(id, created, updated, issuer, jwks_url, alg, status, name, description)
VALUES('bro4dd40617b0cae5b7587c0a89d4086ab2', now(), now(), 'http://localhost:8081/realms/miempresa', 'http://keycloak:8080/realms/miempresa/protocol/openid-connect/certs', 'RS256', 'active', 'Keycloakmiempresa', 'Keycloakmiempresa Id Provider');

INSERT INTO gqlpdss_authdb.gpd_auth_trusted_issuer_audiences
(id, created, updated, audience, status, trusted_issuer_id)
VALUES('qdh73b7a19d30568c595dfce2e83a884786', now(), now(), 'http://localhost:4200/', 'active', 'bro4dd40617b0cae5b7587c0a89d4086ab2');

-- -------------------------------------------------
-- Keycloak realm: gpdauth
-- -------------------------------------------------

-- Trusted Issuer: Keycloak gpdauth realm
INSERT INTO gqlpdss_authdb.gpd_auth_trusted_issuers
(id, created, updated, issuer, jwks_url, alg, status, name, description)
VALUES(
    'kc01gpdauth0000000000000000000001', now(), now(),
    'http://localhost:8081/realms/gpdauth',
    'http://keycloak:8080/realms/gpdauth/protocol/openid-connect/certs',
    'RS256', 'active',
    'Keycloak gpdauth',
    'Keycloak Identity Provider - realm gpdauth'
);

-- Audiences: deben coincidir con el claim "aud" que Keycloak emite por clientId
INSERT INTO gqlpdss_authdb.gpd_auth_trusted_issuer_audiences
(id, created, updated, audience, status, trusted_issuer_id)
VALUES
('kca01phpbck00000000000000000002', now(), now(), 'php-backend-client',      'active', 'kc01gpdauth0000000000000000000001'),
('kca01angfrt00000000000000000003', now(), now(), 'angular-frontend-client', 'active', 'kc01gpdauth0000000000000000000001');

-- Role mappings: Keycloak realm roles → roles internos de la aplicación
INSERT INTO gqlpdss_authdb.gpd_auth_trusted_issuer_roles
(created, updated, trusted_issuer_id, external_role_code, internal_role_code)
VALUES
(now(), now(), 'kc01gpdauth0000000000000000000001', 'app-user',        'ROLE_USER'),
(now(), now(), 'kc01gpdauth0000000000000000000001', 'app-admin',       'ROLE_ADMIN'),
(now(), now(), 'kc01gpdauth0000000000000000000001', 'backend-service',  'ROLE_BACKEND');

-- API Consumer local M2M (la app actúa como IdP, independiente de Keycloak)
-- Secret en texto plano: php-backend-secret-change-me  → hash sha256
INSERT INTO gqlpdss_authdb.gpd_auth_api_consumers
(id, created, updated, identifier, name, secret_hash, status)
VALUES(
    'gac01phpbck00000000000000000004', now(), now(),
    'php-backend-client',
    'PHP Backend M2M Client',
    'ada50b3b086b0c52de192b5e900862106624c341e50d376288f0073b9db7dadd',
    'active'
);

-- Role mappings del API consumer local
INSERT INTO gqlpdss_authdb.gpd_auth_api_consumer_roles
(created, updated, api_consumer_id, external_role_code, internal_role_code)
VALUES
(now(), now(), 'gac01phpbck00000000000000000004', 'backend-service', 'ROLE_BACKEND'),
(now(), now(), 'gac01phpbck00000000000000000004', 'app-admin',       'ROLE_ADMIN');

-- Permiso sobre test_resource para el API consumer local
INSERT INTO gqlpdss_authdb.gpd_auth_api_permissions
(created, updated, name, resource_code, value, description, granted_at, consumer_id)
VALUES(
    now(), now(),
    'php-backend-test-resource-all',
    'test_resource',
    'ALL',
    'Full access to test_resource for PHP backend M2M client',
    now(),
    'gac01phpbck00000000000000000004'
);