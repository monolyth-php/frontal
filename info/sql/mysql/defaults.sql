 
INSERT INTO monolyth_auth_group VALUES (
    NULL, NULL, 'Anonymous', 'anonymous', 1,
    'Just visitors - a fake group so rights may be assigned.', 0
);
INSERT INTO monolyth_auth_group VALUES (
    NULL, NULL, 'Users', 'users', 1,
    'Regular users, i.e. everyone listed in auth (except root).', 0
);
INSERT INTO monolyth_auth_group VALUES (
    NULL, 2, 'Monad', 'monad', 1,
    'Users with access to MonAd.', 0
);
INSERT INTO monolyth_auth_group VALUES (
    NULL, 3, 'Administrators', 'administrators', 1,
    'Users with administrative privileges for MonAd.', 0
);

INSERT INTO monolyth_auth VALUES (
    1, 'root', CONCAT('md5:', md5('admin')), NULL,
    'root@localhost', NOW(), NULL, NULL, NULL,
    NULL, NULL, 0, 0, NULL
);

INSERT INTO monolyth_auth_link_auth_group VALUES (1, 3), (1, 4);

INSERT INTO monolyth_acl_resource VALUES (NULL, NULL, '*', NULL);
INSERT INTO monolyth_acl VALUES (NULL, 1, NULL, 4, 63);
INSERT INTO monolyth_acl_resource VALUES (NULL, NULL, 'monad', NULL);
INSERT INTO monolyth_acl VALUES (NULL, 2, NULL, 3, 63);

INSERT INTO monolyth_counters VALUES
    ('people_online', 0), ('users_online', 0), ('users', 0);

INSERT INTO monolyth_language_all VALUES
    (NULL, 'en', 'en_EN', 'English', NULL),
    (NULL, 'nl', 'nl_NL', 'Nederlands', NULL),
    (NULL, 'fr', 'fr_FR', 'Fran&ccedil;ais', NULL),
    (NULL, 'de', 'de_DE', 'Deutsch', NULL),
    (NULL, 'es', 'es_ES', 'Espanyol', NULL);
INSERT INTO monolyth_language VALUES (1, 'English', 1, 1);

ALTER TABLE monolyth_auth_group ADD CONSTRAINT FOREIGN KEY (owner)
    REFERENCES monolyth_auth(id) ON DELETE CASCADE;

