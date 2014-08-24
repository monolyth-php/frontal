
-- {{{ v4.5.0
CREATE TABLE monolyth_auth (
    id serial PRIMARY KEY,
    name varchar(32),
    pass varchar(255),
    salt varchar(255),
    email varchar(255),
    datecreated timestamp with time zone NOT NULL default NOW(),
    ipcreated inet,
    datemodified timestamp with time zone,
    ipmodified inet,
    dateactive timestamp with time zone,
    ipactive inet,
    status integer not null default 0,
    feature integer not null default 0,
    media integer
);
CREATE UNIQUE INDEX monolyth_auth_name_key ON monolyth_auth(LOWER(name));
CREATE UNIQUE INDEX monolyth_auth_email_key ON monolyth_auth(LOWER(email));
CREATE INDEX monolyth_auth_datecreated_key ON monolyth_auth(datecreated);
CREATE INDEX monolyth_auth_datemodified_key ON monolyth_auth(datemodified);
CREATE INDEX monolyth_auth_status_key ON monolyth_auth(status);
CREATE INDEX monolyth_auth_feature_key ON monolyth_auth(feature);
CREATE INDEX monolyth_auth_media_key ON monolyth_auth(media);

CREATE TABLE monolyth_group (
    id serial PRIMARY KEY,
    parent integer REFERENCES monolyth_group(id) ON DELETE CASCADE,
    name varchar(255) NOT NULL,
    owner integer NOT NULL,
    description text,
    members integer NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX monolyth_auth_group_name_key ON monolyth_auth_group(LOWER(name));
CREATE INDEX monolyth_auth_group_parent_key ON monolyth_auth_group(parent);
CREATE INDEX monolyth_auth_group_owner_key ON monolyth_auth_group(owner);

CREATE TABLE monolyth_auth_group (
    auth integer NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    auth_group integer NOT NULL REFERENCES monolyth_auth_group(id) ON DELETE CASCADE,
    PRIMARY KEY(auth, auth_group)
);

-- a backup table for deleted user names
CREATE TABLE monolyth_auth_deleted (
    id serial PRIMARY KEY,
    name varchar(255),
    datecreated timestamp with time zone NOT NULL DEFAULT NOW()
);
CREATE INDEX monolyth_auth_deleted_name_key ON monolyth_auth_deleted(name);

CREATE TABLE monolyth_session (
    id varchar(32) PRIMARY KEY,
    randomid integer not null,
    userid integer REFERENCES monolyth_auth(id) ON DELETE SET NULL,
    ip inet,
    ipforward inet,
    user_agent varchar(255),
    datecreated timestamp with time zone not null default NOW(),
    dateactive timestamp with time zone not null default NOW(),
    checksum varchar(32) not null,
    data text
);
CREATE INDEX monolyth_session_dateactive_key ON monolyth_session(dateactive);
CREATE INDEX monolyth_session_randomid_key ON monolyth_session(randomid);
CREATE UNIQUE INDEX monolyth_session_userid_key ON monolyth_session(userid) WHERE userid IS NOT NULL;

CREATE TABLE monolyth_session_log (
    id serial PRIMARY KEY,
    userid integer,
    ip inet,
    user_agent varchar(255),
    datecreated timestamp with time zone not null,
    datedeleted timestamp with time zone not null default NOW()
);

CREATE TABLE monolyth_counters (
    name varchar(32) PRIMARY KEY,
    value integer not null default 0
);
CREATE UNIQUE INDEX monolyth_counters_name_key ON monolyth_counters(LOWER(name));

CREATE TABLE monolyth_confirm (
    owner integer NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    hash varchar(6) NOT NULL,
    datecreated timestamp with time zone NOT NULL DEFAULT NOW(),
    datevalid timestamp with time zone NOT NULL,
    tablename varchar(255) NOT NULL,
    fieldname varchar(255) NOT NULL,
    newvalue varchar(255),
    operation varchar(4) NOT NULL DEFAULT '=',
    conditional varchar(255) NOT NULL,
    PRIMARY KEY(owner, hash, datecreated, tablename, fieldname, newvalue)
);
CREATE INDEX monolyth_confirm_owner_key ON monolyth_confirm(owner);
CREATE INDEX monolyth_confirm_hash_key ON monolyth_confirm(hash);
CREATE INDEX monolyth_confirm_datecreated_key ON monolyth_confirm(datecreated);
CREATE INDEX monolyth_confirm_tablename_fieldname_newvalue_key ON monolyth_confirm(tablename, fieldname, newvalue);

CREATE TABLE monolyth_folder (
    id serial PRIMARY KEY,
    parent integer REFERENCES monolyth_folder(id) ON DELETE SET NULL,
    name varchar(64) NOT NULL,
    owner integer NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE
);
CREATE INDEX monolyth_folder_owner_key ON monolyth_folder(owner);
CREATE INDEX monolyth_folder_parent_key ON monolyth_folder(parent);
CREATE UNIQUE INDEX monolyth_folder_parent_name_key ON monolyth_folder(parent, name);

CREATE TABLE monolyth_media (
    id serial PRIMARY KEY,
    owner integer NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    data bytea,
    filename text NOT NULL,
    originalname text NOT NULL,
    md5 varchar(32) NOT NULL,
    filesize integer NOT NULL,
    mimetype varchar(32) NOT NULL,
    folder integer REFERENCES monolyth_folder(id) ON DELETE SET NULL
);
CREATE INDEX monolyth_media_md5_key ON monolyth_media(md5);
CREATE UNIQUE INDEX monolyth_media_md5_owner_filesize_mimetype_key ON monolyth_media(md5, owner, filesize, mimetype);
CREATE INDEX monolyth_media_owner_key ON monolyth_media(owner);
CREATE INDEX monolyth_media_folder_key ON monolyth_media(folder);
ALTER TABLE monolyth_auth ADD FOREIGN KEY(media) REFERENCES monolyth_media(id) ON DELETE SET NULL;

CREATE TABLE monolyth_language_all (
    id serial PRIMARY KEY,
    code varchar(5) NOT NULL,
    lc_code varchar(10) NOT NULL,
    title varchar(64) NOT NULL,
    fallback integer REFERENCES monolyth_language_all(id) ON DELETE SET NULL
);
CREATE UNIQUE INDEX monolyth_language_all_code_key ON monolyth_language_all(LOWER(code));
CREATE UNIQUE INDEX monolyth_language_all_lc_code_key ON monolyth_language_all(LOWER(lc_code));

CREATE TABLE monolyth_language (
    id integer NOT NULL PRIMARY KEY REFERENCES monolyth_language_all(id) ON DELETE CASCADE,
    title varchar(64) NOT NULL REFERENCES monolyth_language_all(title) ON DELETE CASCADE ON UPDATE CASCADE,
    sortorder integer,
    is_default boolean NOT NULL DEFAULT false
);
CREATE INDEX monolyth_language_sortorder_key ON monolyth_language(sortorder);
CREATE INDEX monolyth_language_is_default_key ON monolyth_language(is_default);

CREATE TABLE monolyth_country (
    id serial PRIMARY KEY,
    code VARCHAR(2) NOT NULL,
    status integer NOT NULL DEFAULT 1
);
CREATE UNIQUE INDEX monolyth_country_code_key ON monolyth_country(LOWER(code));
CREATE INDEX monolyth_country_status_key ON monolyth_country(status);

CREATE TABLE monolyth_country_i18n (
    id integer NOT NULL REFERENCES monolyth_country(id) ON DELETE CASCADE,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    title varchar(128) NOT NULL,
    PRIMARY KEY(id, language)
);

CREATE TABLE monolyth_country_language (
    country integer NOT NULL REFERENCES monolyth_country(id) ON DELETE CASCADE,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    PRIMARY KEY(id, language)
);

CREATE TABLE monolyth_city (
    id serial PRIMARY KEY,
    country integer NOT NULL REFERENCES monolyth_country(id) ON DELETE CASCADE,
    language integer REFERENCES monolyth_language(id) ON DELETE SET NULL,
    name VARCHAR(64)
);
CREATE INDEX monolyth_city_country_key ON monolyth_city(country);
CREATE INDEX monolyth_city_language_key ON monolyth_city(language);
CREATE INDEX monolyth_city_country_language_name_key ON monolyth_city(country, language, name);

CREATE TABLE monolyth_text (
    id varchar(64) NOT NULL PRIMARY KEY,
    status integer NOT NULL DEFAULT 0
);

CREATE TABLE monolyth_text_i18n (
    id varchar(64) NOT NULL REFERENCES monolyth_text(id) ON DELETE CASCADE ON UPDATE CASCADE,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    content mediumtext NOT NULL,
    PRIMARY KEY(id, language)
);

CREATE TABLE monolyth_mail_template (
    id varchar(64) NOT NULL,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    description text,
    html text NOT NULL,
    plain text NOT NULL,
    PRIMARY KEY(id, language)
);
CREATE INDEX monolyth_media_template_language_key ON monolyth_mail_template(language);

CREATE TABLE monolyth_mail (
    id varchar(64) NOT NULL,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    template varchar(64) DEFAULT NULL REFERENCES monolyth_mail_template(id) ON DELETE CASCADE,
    templatelanguage integer NOT NULL REFERENCES monolyth_mail_template(language) ON DELETE CASCADE,
    description text,
    sender varchar(255) NOT NULL,
    subject varchar(255) NOT NULL,
    html text NOT NULL,
    plain text NOT NULL,
    PRIMARY KEY(id, language)
);
CREATE INDEX monolyth_mail_template_key ON monolyth_mail(template);
CREATE INDEX monolyth_mail_templatelanguage_key ON monolyth_mail(templatelanguage);

CREATE TABLE monolyth_cookie (
    id varchar(40) NOT NULL PRIMARY KEY,
    ip inet NOT NULL,
    user_agent text NOT NULL,
    settings integer NOT NULL DEFAULT 1,
    datecreated timestamp with time zone NOT NULL default now()
);

CREATE TABLE monolyth_variable (
    name VARCHAR(32) NOT NULL,
    target ENUM('css', 'js'),
    value TEXT NOT NULL,
    media integer REFERENCES monolyth_media(id) ON DELETE SET NULL,
    PRIMARY KEY(name, target)
);
CREATE INDEX monolyth_variable_media_key ON monolyth_variable(media);

CREATE OR REPLACE FUNCTION fn_generate_slug (str TEXT) RETURNS text AS $$
BEGIN
    -- Lowercase the entire thing.
    str = LOWER(str);
    -- Quotes should be replaced by an empty string, since they're commonly
    -- either used for abbreviations, or for actual quoting (and in neither
    -- case do we need them in our slugs).
    str = REPLACE(str, E'\'', '');
    str = REPLACE(str, E'\"', '');
    -- Replace certain HTML entities with "simple" letters.
    str = REPLACE(str, '&amp;', '+');
    str = REPLACE(str, '&aacute;', 'a');
    str = REPLACE(str, '&acirc;', 'a');
    str = REPLACE(str, '&agrave;', 'a');
    str = REPLACE(str, '&aring;', 'a');
    str = REPLACE(str, '&atilde;', 'a');
    str = REPLACE(str, '&auml;', 'a');
    str = REPLACE(str, '&aelig', 'ae');
    str = REPLACE(str, '&ccedil', 'c');
    str = REPLACE(str, '&eacute;', 'e');
    str = REPLACE(str, '&ecirc;', 'e');
    str = REPLACE(str, '&egrave;', 'e');
    str = REPLACE(str, '&euml;', 'e');
    str = REPLACE(str, '&iacute;', 'i');
    str = REPLACE(str, '&icirc;', 'i');
    str = REPLACE(str, '&igrave;', 'i');
    str = REPLACE(str, '&iuml;', 'i');
    str = REPLACE(str, '&eth;', 'd');
    str = REPLACE(str, '&ntilde;', 'n');
    str = REPLACE(str, '&oacute;', 'o');
    str = REPLACE(str, '&ocirc;', 'o');
    str = REPLACE(str, '&ograve;', 'o');
    str = REPLACE(str, '&oslash;', 'o');
    str = REPLACE(str, '&otilde;', 'o');
    str = REPLACE(str, '&ouml;', 'o');
    str = REPLACE(str, '&times;', 'x');
    str = REPLACE(str, '&uacute;', 'u');
    str = REPLACE(str, '&ucirc;', 'u');
    str = REPLACE(str, '&ugrave;', 'u');
    str = REPLACE(str, '&uuml;', 'u');
    str = REPLACE(str, '&yacute;', 'y');
    str = REPLACE(str, '&yuml;', 'y');
    str = REPLACE(str, '&thorn;', 'th');
    str = REPLACE(str, '&szlig;', 'sz');
    str = REPLACE(str, '&divide;', ':');
    str = regexp_replace(str, '[^+a-z0-9-]+', '-', 'g');
    str = regexp_replace(str, '-{2,}', '-', 'g');
    str = regexp_replace(str, '-+?[+]-+?', '+', 'g');
    str = regexp_replace(str, '^-|-$', '', 'g');
    RETURN str;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION fn_increment_slug(str TEXT, incr INTEGER) RETURNS TEXT AS $$
BEGIN
    IF incr > 1 THEN
        str = REPLACE(str, '-' || (incr - 1)::text, '');
    END IF;
    IF LENGTH(str || '-' || incr::text) > 255 THEN
        str = SUBSTRING(str FROM 1 FOR -LENGTH('-' || str));
    END IF;
    RETURN str || '-' || incr::text;
END;
$$ LANGUAGE 'plpgsql';

-- This function is supposed to be used as a template of sorts.
-- CREATE FUNCTION fn_TABLE_unique_slug(str TEXT) RETURNS TEXT AS $$
-- BEGIN
--    @uniq = 0;
--    @incr = 0;
--    REPEAT
--        SELECT COALESCE(id, 0) FROM _TABLE_ WHERE _FIELD_ = @retval INTO @check;
--        IF @check <> 0 THEN
--            @uniq = 1;
--        ELSE
--            @incr = @incr + 1;
--            str = fn_increment_slug(str, @incr);
--        END IF;
--    UNTIL @uniq = 1 END REPEAT;
--    RETURN str;
-- END;
-- $$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION fn_set_commentable() RETURNS INT AS $$
BEGIN
    INSERT INTO monolyth_commentable (comments) VALUES (0);
    RETURN CURRVAL('monolyth_commentable_id_seq');
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION fn_set_media(myowner INT, mydata BLOB, myfilename TEXT, mymd5 TEXT, myfilesize INT, mymimetype TEXT) RETURNS INT AS $$
DECLARE tmpid INT;
BEGIN
    tmpid := SELECT id FROM monolyth_media WHERE md5 = mymd5 AND filesize = myfilesize AND mimetype = mymimetype;
    IF tmpid IS NULL THEN
        INSERT INTO monolyth_media (owner, data, filename, md5, filesize, mimetype) VALUES (myowner, mydata, myfilename, mymd5, myfilesize, mymimetype);
        tmpid := SELECT id FROM monolyth_media WHERE md5 = mymd5 AND filesize = myfilesize AND mimetype = mymimetype;
    END IF;
    RETURN tmpid;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION monolyth_auth_group_after_insert()
RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_group SET members = members + 1 WHERE id = NEW.auth_group;
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_group_after_insert AFTER INSERT ON monolyth_auth_group
FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_group_after_insert();

CREATE OR REPLACE FUNCTION monolyth_auth_insert_after() RETURNS "trigger" AS $$
BEGIN
    INSERT INTO monolyth_auth_group VALUES (NEW.id, 2);
    UPDATE monolyth_counters SET value = value + 1 WHERE name = 'users';
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_insert_after AFTER INSERT ON monolyth_auth
FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_insert_after();

CREATE OR REPLACE FUNCTION monolyth_auth_group_after_delete() RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_group SET members = members - 1 WHERE id = OLD.auth_group;
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_group_after_delete AFTER DELETE ON monolyth_auth_group
FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_group_after_delete();

CREATE OR REPLACE FUNCTION monolyth_auth_delete_after() RETURNS "trigger" AS $$
BEGIN
    INSERT INTO monolyth_auth_deleted VALUES (OLD.id, OLD.name);
    UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users';
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_delete_after AFTER DELETE ON monolyth_auth FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_delete_after();

CREATE OR REPLACE FUNCTION monolyth_session_log() RETURNS "trigger" AS $$
BEGIN
    INSERT INTO monolyth_session_log (userid, ip, user_agent, datecreated) VALUES (OLD.userid, OLD.ip, OLD.user_agent, OLD.datecreated);
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_session_log AFTER DELETE ON monolyth_session
FOR EACH ROW EXECUTE PROCEDURE monolyth_session_log();

CREATE OR REPLACE FUNCTION monolyth_auth_update_before() RETURNS "trigger" AS $$
BEGIN
    NEW.datemodified := NOW();
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION update_people_online() RETURNS "trigger" AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE monolyth_counters SET value = value + 1 WHERE name = 'people_online';
        RETURN NEW;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE monolyth_counters SET value = value - 1 WHERE name = 'people_online';
        IF OLD.userid IS NOT NULL THEN
            UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users_online';
        END IF;
        RETURN OLD;
    ELSIF TG_OP = 'UPDATE' THEN
        IF OLD.userid IS NULL AND NEW.userid IS NOT NULL THEN
            UPDATE monolyth_counters SET value = value + 1 WHERE name = 'users_online';
        ELSIF OLD.userid IS NOT NULL AND NEW.userid IS NULL THEN
            UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users_online';
        END IF;
        IF NEW.userid IS NOT NULL THEN
            UPDATE monolyth_auth SET dateactive = NOW() WHERE id = NEW.userid;
        END IF;
        RETURN NEW;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS update_people_online ON monolyth_session;
CREATE TRIGGER update_people_online AFTER INSERT OR UPDATE OR DELETE ON monolyth_session
FOR EACH ROW EXECUTE PROCEDURE update_people_online();

CREATE OR REPLACE FUNCTION monolyth_language_insert_before() RETURNS "trigger" AS $$
BEGIN
    NEW.title := (SELECT title FROM monolyth_language_all WHERE id = NEW.id);
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_language_insert_before ON monolyth_language;
CREATE TRIGGER monolyth_language_insert_before BEFORE INSERT ON monolyth_language
FOR EACH ROW EXECUTE PROCEDURE monolyth_language_insert_before();

INSERT INTO monolyth_group (parent, name, owner, description)
    VALUES (NULL, 'Anonymous', 1, 'Just visitors - a fake group so rights may be assigned.')
);
INSERT INTO monolyth_group (parent, name, owner, description)
    VALUES (NULL, 'Users', 1, 'Regular users, i.e. everyone listed in auth (except root).');
INSERT INTO monolyth_group (parent, name, owner, description)
    VALUES (2, 'Monad', 1, 'Users with access to MonAd.');
INSERT INTO monolyth_group (parent, name, owner, description)
    VALUES (3, 'Administrators', 'administrators', 1, 'Users with administrative privileges for MonAd.');

INSERT INTO monolyth_auth VALUES (1, 'root', 'md5:' || md5('admin'), NULL, 'root@localhost', NOW(), NULL, NULL, NULL, NULL, NULL, 0, 0, NULL);

INSERT INTO monolyth_auth_group VALUES (1, 3), (1, 4);

INSERT INTO monolyth_counters VALUES ('people_online', 0), ('users_online', 0), ('users', 0);

INSERT INTO monolyth_language_all (code, lc_code, title, fallback) VALUES
    ('en', 'en_EN', 'English', NULL),
    ('nl', 'nl_NL', 'Nederlands', NULL),
    ('fr', 'fr_FR', 'Fran&ccedil;ais', NULL),
    ('de', 'de_DE', 'Deutsch', NULL),
    ('es', 'es_ES', 'Espanyol', NULL);
INSERT INTO monolyth_language (id, sortorder, is_default) VALUES (1, 1, 1);

ALTER TABLE monolyth_auth_group ADD CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE;
-- }}}

