INSERT INTO monolyth_auth_group (parent, name, slug, owner, description) VALUES
    (NULL, 'Anonymous', 'anonymous', 1,
        'Just visitors - a fake group so rights may be assigned.'
    ),
    (NULL, 'Users', 'users', 1,
        'Regular users, i.e. everyone listed in auth (except root).'
    ),
    ((SELECT id FROM monolyth_auth_group WHERE slug = 'user'),
        'Monad', 'monad', 1, 'Users with access to MonAd.'
    ),
    ((SELECT id FROM monolyth_auth_group WHERE slug = 'monad'),
        'Administrators', 'administrators', 1,
        'Users with administrative privileges for MonAd.'
    );
INSERT INTO monolyth_auth (name, pass, salt, email) VALUES
    ('root', 'admin', RANDOM() * 1000000, 'root@localhost');
UPDATE monolyth_auth SET pass = 'md5:' || md5(pass || salt) WHERE name = 'root';
ALTER TABLE monolyth_auth_group ADD FOREIGN KEY(owner)
    REFERENCES monolyth_auth(id);
INSERT INTO monolyth_auth_link_auth_group (auth, auth_group) VALUES (1, 3), (1, 4);
INSERT INTO monolyth_acl_resource (parent, name, pk) VALUES
    (NULL, '*', NULL), (NULL, 'monad', NULL);
INSERT INTO monolyth_acl (acl_resource, owner, auth_group, action) VALUES
    ((SELECT id FROM monolyth_acl_resource WHERE name = '*'), NULL,
        (SELECT id FROM monolyth_auth_group WHERE slug = 'administrators'), 63),
    ((SELECT id FROM monolyth_acl_resource WHERE name = 'monad'), NULL,
        (SELECT id FROM monolyth_auth_group WHERE slug = 'monad'), 63);

INSERT INTO monolyth_counters VALUES
    ('people_online', 0), ('users_online', 0), ('users', 0);

INSERT INTO monolyth_language_all (code, lc_code, title) VALUES
    ('en', 'en_EN', 'English'),
    ('nl', 'nl_NL', 'Nederlands'),
    ('fr', 'fr_FR', 'Fran&ccedil;ais'),
    ('de', 'de_DE', 'Deutsch'),
    ('es', 'es_ES', 'Espanyol');
INSERT INTO monolyth_language (id, title, sortorder, is_default) VALUES
    ((SELECT id FROM monolyth_language_all WHERE code = 'en'),
        'English', 1, TRUE
    );

