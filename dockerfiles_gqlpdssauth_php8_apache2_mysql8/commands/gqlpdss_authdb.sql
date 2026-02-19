INSERT INTO gpd_auth_users
(id,  firstname, lastname, email, username, `algorithm`, salt, user_password, password_expiration, picture, active, last_login,created, updated)
VALUES 
(1,'Pancho','López','p.lopez@demo.local.lan','p.lopez','sha256',NULL,sha2("demo###",256) ,NULL,NULL,1,NULL,'2023-12-06 01:05:59','2023-12-06 01:05:59');

INSERT INTO `gpd_auth_resources` 
(id,  code, title, description, scopes,created, updated)
VALUES
(1,'test_resource','Test Resource','','[\"ALL\"]','2023-12-06 17:56:52','2023-12-06 17:56:52');

INSERT INTO gpd_auth_permissions
(id,  permission_access, resource_id, user_id, role_id,created, updated,)
VALUES
(1,1,1,NULL,'ALLOW','ALL',NULL,'2023-12-06 17:58:30','2023-12-06 17:58:30');

