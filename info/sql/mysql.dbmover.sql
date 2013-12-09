
-- {{{ v4.1.2
CREATE TABLE monolyth_auth (
    id bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name varchar(16),
    pass varchar(255),
    salt varchar(255),
    email varchar(255),
    datecreated timestamp NOT NULL default NOW(),
    ipcreated varchar(39),
    datemodified datetime,
    ipmodified varchar(39),
    dateactive datetime,
    ipactive varchar(39),
    status bigint NOT NULL default 0,
    feature bigint NOT NULL default 0,
    media BIGINT UNSIGNED DEFAULT NULL,
    UNIQUE INDEX(name), UNIQUE INDEX(email), INDEX(datecreated), INDEX(datemodified),
    INDEX(dateactive), INDEX(status), INDEX(feature), UNIQUE INDEX(media)
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_acl_resource (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    parent bigint UNSIGNED DEFAULT NULL,
    name varchar(64) NOT NULL,
    pk varchar(255),
    UNIQUE INDEX(parent, name, pk),
    CONSTRAINT FOREIGN KEY (parent) REFERENCES monolyth_acl_resource(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_auth_group (
    id bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    parent bigint UNSIGNED DEFAULT NULL,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL,
    owner bigint UNSIGNED NOT NULL,
    description mediumtext,
    countmember bigint NOT NULL default 0,
    INDEX(name), UNIQUE INDEX(slug), INDEX(parent), INDEX(owner),
    CONSTRAINT FOREIGN KEY (parent) REFERENCES monolyth_auth_group(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_acl (
    id bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    acl_resource bigint UNSIGNED NOT NULL,
    owner bigint UNSIGNED DEFAULT NULL,
    auth_group bigint UNSIGNED DEFAULT NULL,
    action bigint UNSIGNED NOT NULL default 0,
    UNIQUE INDEX(acl_resource, owner, auth_group), INDEX(action),
    CONSTRAINT FOREIGN KEY (acl_resource) REFERENCES monolyth_acl_resource(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (auth_group) REFERENCES monolyth_auth_group(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_auth_link_auth_group (
    auth bigint UNSIGNED NOT NULL,
    auth_group bigint UNSIGNED NOT NULL,
    CONSTRAINT PRIMARY KEY(auth, auth_group),
    CONSTRAINT FOREIGN KEY (auth) REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (auth_group) REFERENCES monolyth_auth_group(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

-- a backup table for deleted user names
CREATE TABLE monolyth_auth_deleted (
    id bigint UNSIGNED NOT NULL PRIMARY KEY,
    name varchar(255),
    datecreated timestamp NOT NULL DEFAULT NOW(),
    INDEX(name)
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_session (
    id varchar(32) NOT NULL PRIMARY KEY,
    randomid integer NOT NULL,
    userid bigint UNSIGNED DEFAULT NULL,
    ip varchar(39),
    ipforward varchar(39),
    user_agent varchar(255),
    datecreated timestamp NOT NULL DEFAULT NOW(),
    dateactive datetime NOT NULL,
    checksum varchar(32) NOT NULL,
    data longtext,
    INDEX(dateactive), INDEX(randomid), INDEX(userid),
    CONSTRAINT FOREIGN KEY (userid) REFERENCES monolyth_auth(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_session_log (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userid integer,
    ip varchar(39) NOT NULL,
    user_agent varchar(255) NOT NULL,
    datecreated datetime NOT NULL,
    datedeleted timestamp NOT NULL DEFAULT NOW()
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_counters (
    name varchar(32) PRIMARY KEY,
    value bigint,
    UNIQUE INDEX(name)
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_confirm (
    owner bigint UNSIGNED DEFAULT NULL,
    hash varchar(6) NOT NULL,
    datecreated timestamp NOT NULL DEFAULT NOW(),
    datevalid datetime NOT NULL,
    tablename varchar(32) NOT NULL,
    fieldname varchar(32) NOT NULL,
    newvalue varchar(255),
    operation varchar(4) NOT NULL DEFAULT '=',
    conditional varchar(255) NOT NULL,
    INDEX(owner), INDEX(hash), INDEX(datecreated),
    INDEX(tablename, fieldname, newvalue),
    PRIMARY KEY(owner, hash, datecreated, tablename, fieldname, newvalue),
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

CREATE TABLE monolyth_folder (
    id bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    parent bigint UNSIGNED DEFAULT NULL,
    name varchar(64) NOT NULL,
    owner bigint UNSIGNED NOT NULL,
    INDEX(owner), INDEX(parent), UNIQUE INDEX(parent, name),
    CONSTRAINT FOREIGN KEY (parent) REFERENCES monolyth_folder(id) ON DELETE SET NULL,
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_media (
    id bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner bigint UNSIGNED NOT NULL,
    data longblob,
    filename text NOT NULL,
    originalname text NOT NULL,
    md5 varchar(32) NOT NULL,
    filesize integer NOT NULL,
    mimetype varchar(32) NOT NULL,
    folder bigint UNSIGNED DEFAULT NULL,
    INDEX(md5), UNIQUE INDEX(md5, owner, filesize, mimetype),
    INDEX(owner), INDEX(folder),
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (folder) REFERENCES monolyth_folder(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='utf8';

ALTER TABLE monolyth_auth ADD CONSTRAINT FOREIGN KEY(media) REFERENCES monolyth_media(id) ON DELETE SET NULL;

-- The tables monolyth_comment(able), monolyth_vote(able) and
-- monolyth_like(able) store generic comments, votes and likes for random
-- objects. A table supporting one of these should define an 'on delete' trigger
-- to also delete the respective items from these tables, since we can't use
-- foreign keys for that.
-- Additionally, implementing tables should define a field comments, votes
-- and/or likes holding the correct id.

CREATE TABLE monolyth_voteable (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    votes bigint UNSIGNED NOT NULL DEFAULT 0,
    average float NOT NULL DEFAULT 0,
    last bigint UNSIGNED DEFAULT NULL,
    INDEX(votes), INDEX(average), INDEX(last)
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_vote (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reference bigint UNSIGNED NOT NULL,
    owner bigint UNSIGNED NOT NULL,
    vote smallint NOT NULL DEFAULT 0,
    datecreated timestamp NOT NULL DEFAULT NOW(),
    datemodified datetime DEFAULT NULL,
    status bigint UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE INDEX(reference, owner), INDEX(datecreated), INDEX(datemodified), INDEX(vote),
    CONSTRAINT FOREIGN KEY (reference) REFERENCES monolyth_voteable(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';
ALTER TABLE monolyth_voteable ADD FOREIGN KEY(last) REFERENCES monolyth_vote(id) ON DELETE SET NULL;

CREATE TABLE monolyth_likeable (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    likes bigint UNSIGNED NOT NULL,
    last bigint UNSIGNED DEFAULT NULL,
    INDEX(likes), INDEX(last)
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_like (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reference bigint UNSIGNED NOT NULL,
    owner bigint UNSIGNED NOT NULL,
    datecreated timestamp NOT NULL DEFAULT NOW(),
    UNIQUE INDEX(reference, owner), INDEX(datecreated),
    CONSTRAINT FOREIGN KEY (reference) REFERENCES monolyth_likeable(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';
ALTER TABLE monolyth_likeable ADD FOREIGN KEY(last) REFERENCES monolyth_like(id) ON DELETE SET NULL;

CREATE TABLE monolyth_commentable (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    comments bigint UNSIGNED NOT NULL DEFAULT 0,
    last bigint UNSIGNED DEFAULT NULL,
    INDEX(comments), INDEX(last)
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_comment (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    hash varchar(32) NOT NULL,
    reference bigint UNSIGNED NOT NULL,
    replyto bigint UNSIGNED DEFAULT NULL,
    commentindex bigint UNSIGNED,
    owner bigint UNSIGNED DEFAULT NULL,
    name varchar(255) DEFAULT NULL,
    email varchar(255) DEFAULT NULL,
    homepage varchar(255) DEFAULT NULL,
    ip varchar(39) NOT NULL,
    comment mediumtext NOT NULL,
    datecreated timestamp NOT NULL DEFAULT NOW(),
    datemodified datetime DEFAULT NULL,
    status bigint UNSIGNED NOT NULL DEFAULT 0,
    likes BIGINT UNSIGNED NOT NULL,
    INDEX(hash), INDEX(reference), INDEX(commentindex), INDEX(owner), INDEX(ip),
    INDEX(datecreated), INDEX(datemodified), INDEX(status), INDEX(replyto), UNIQUE INDEX(likes),
    CONSTRAINT FOREIGN KEY(likes) REFERENCES monolyth_likeable(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (reference) REFERENCES monolyth_commentable(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (replyto) REFERENCES monolyth_comment(id) ON DELETE SET NULL,
    CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';
ALTER TABLE monolyth_commentable ADD FOREIGN KEY(last) REFERENCES monolyth_comment(id) ON DELETE SET NULL;

CREATE TABLE monolyth_language_all (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code varchar(5) NOT NULL,
    lc_code varchar(10) NOT NULL,
    title varchar(64) NOT NULL,
    fallback bigint UNSIGNED,
    UNIQUE INDEX(code), UNIQUE INDEX(lc_code), UNIQUE INDEX(title),
    INDEX(fallback),
    CONSTRAINT FOREIGN KEY(fallback) REFERENCES monolyth_language_all(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_language (
    id bigint UNSIGNED NOT NULL PRIMARY KEY,
    title varchar(64) NOT NULL,
    sortorder integer UNSIGNED,
    is_default boolean NOT NULL DEFAULT false,
    INDEX(sortorder), INDEX(is_default),
    CONSTRAINT FOREIGN KEY (id) REFERENCES monolyth_language_all(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (title) REFERENCES monolyth_language_all(title) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_country (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(2) NOT NULL,
    status BIGINT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE INDEX(code), INDEX(status)
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_country_i18n (
    id BIGINT UNSIGNED NOT NULL,
    language BIGINT UNSIGNED NOT NULL,
    title varchar(128) NOT NULL,
    PRIMARY KEY(id, language),
    CONSTRAINT FOREIGN KEY(id) REFERENCES monolyth_country(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY(language) REFERENCES monolyth_language(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_country_language (
    country BIGINT UNSIGNED NOT NULL,
    language BIGINT UNSIGNED NOT NULL,
    CONSTRAINT FOREIGN KEY(country) REFERENCES monolyth_country(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY(language) REFERENCES monolyth_language(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_city (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    country BIGINT UNSIGNED NOT NULL,
    language BIGINT UNSIGNED,
    name VARCHAR(64),
    INDEX(country), INDEX(language),
    UNIQUE INDEX(country, language, name),
    CONSTRAINT FOREIGN KEY(country) REFERENCES monolyth_country(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY(language) REFERENCES monolyth_language(id) ON DELETE SET NULL
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_text (
    id varchar(64) NOT NULL PRIMARY KEY,
    status BIGINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_text_i18n (
    id varchar(64) NOT NULL,
    language BIGINT UNSIGNED NOT NULL,
    content mediumtext NOT NULL,
    CONSTRAINT PRIMARY KEY (id, language),
    CONSTRAINT FOREIGN KEY (id) REFERENCES monolyth_text(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY (language) REFERENCES monolyth_language(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_mail_template (
    id varchar(64) NOT NULL,
    language bigint UNSIGNED NOT NULL,
    description text,
    html text NOT NULL,
    plain text NOT NULL,
    PRIMARY KEY(id, language),
    INDEX(language),
    CONSTRAINT FOREIGN KEY (language) REFERENCES monolyth_language(id) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_mail (
    id varchar(64) NOT NULL,
    language bigint UNSIGNED NOT NULL,
    template varchar(64) DEFAULT NULL,
    templatelanguage bigint UNSIGNED NOT NULL,
    description text,
    sender varchar(255) NOT NULL,
    subject varchar(255) NOT NULL,
    html text NOT NULL,
    plain text NOT NULL,
    PRIMARY KEY(id, language),
    INDEX(language), INDEX(template), INDEX(templatelanguage),
    CONSTRAINT FOREIGN KEY (language) REFERENCES monolyth_language(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (template) REFERENCES monolyth_mail_template(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (templatelanguage) REFERENCES monolyth_mail_template(language) ON DELETE CASCADE
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';

CREATE TABLE monolyth_cookie (
    id varchar(40) NOT NULL PRIMARY KEY,
    ip varchar(39) NOT NULL,
    user_agent text NOT NULL,
    settings BIGINT UNSIGNED NOT NULL DEFAULT 1,
    datecreated timestamp NOT NULL default now()
);

CREATE TABLE monolyth_variable (
    name VARCHAR(32) NOT NULL,
    target ENUM('css', 'js'),
    value TEXT NOT NULL,
    media BIGINT UNSIGNED,
    PRIMARY KEY(name, target),
    INDEX(media),
    CONSTRAINT FOREIGN KEY(media) REFERENCES monolyth_media(id) ON DELETE SET NULL
);


DROP FUNCTION IF EXISTS fn_generate_slug;
DELIMITER $$
CREATE FUNCTION fn_generate_slug (str TEXT) RETURNS text
BEGIN
    IF LENGTH(str) = 0 OR str IS NULL THEN
        RETURN str;
    END IF;
    -- Lowercase the entire thing.
    SET str = LOWER(str);

    -- Quotes should be replaced by an empty string, since they're commonly
    -- either used for abbreviations, or for actual quoting (and in neither
    -- case do we need them in our slugs).
    SET str = REPLACE(str, '\'', '');
    SET str = REPLACE(str, '\"', '');

    -- Replace certain HTML entities with "simple" letters.
    SET str = REPLACE(str, '&aacute;', 'a');
    SET str = REPLACE(str, '&acirc;', 'a');
    SET str = REPLACE(str, '&agrave;', 'a');
    SET str = REPLACE(str, '&aring;', 'a');
    SET str = REPLACE(str, '&atilde;', 'a');
    SET str = REPLACE(str, '&auml;', 'a');
    SET str = REPLACE(str, '&aelig', 'ae');
    SET str = REPLACE(str, '&ccedil', 'c');
    SET str = REPLACE(str, '&eacute;', 'e');
    SET str = REPLACE(str, '&ecirc;', 'e');
    SET str = REPLACE(str, '&egrave;', 'e');
    SET str = REPLACE(str, '&euml;', 'e');
    SET str = REPLACE(str, '&iacute;', 'i');
    SET str = REPLACE(str, '&icirc;', 'i');
    SET str = REPLACE(str, '&igrave;', 'i');
    SET str = REPLACE(str, '&iuml;', 'i');
    SET str = REPLACE(str, '&eth;', 'd');
    SET str = REPLACE(str, '&ntilde;', 'n');
    SET str = REPLACE(str, '&oacute;', 'o');
    SET str = REPLACE(str, '&ocirc;', 'o');
    SET str = REPLACE(str, '&ograve;', 'o');
    SET str = REPLACE(str, '&oslash;', 'o');
    SET str = REPLACE(str, '&otilde;', 'o');
    SET str = REPLACE(str, '&ouml;', 'o');
    SET str = REPLACE(str, '&times;', 'x');
    SET str = REPLACE(str, '&uacute;', 'u');
    SET str = REPLACE(str, '&ucirc;', 'u');
    SET str = REPLACE(str, '&ugrave;', 'u');
    SET str = REPLACE(str, '&uuml;', 'u');
    SET str = REPLACE(str, '&yacute;', 'y');
    SET str = REPLACE(str, '&yuml;', 'y');
    SET str = REPLACE(str, '&thorn;', 'th');
    SET str = REPLACE(str, '&szlig;', 'sz');
    SET str = REPLACE(str, '&divide;', ':');

    -- Retval is what we'll be returning eventually. Since MySQL knows REGEXP
    -- but has no way of actually doing something similar to preg_replace, we're
    -- going to have to loop through the entire string char by char.
    SET @retval = '';

    -- Replace all non-valid characters with '-'.
    SET @i = 1;
    REPEAT
        SET @substr = SUBSTRING(str, @i, 1);
        IF @substr REGEXP '^[a-z0-9-]$' THEN
            SET @retval = CONCAT(@retval, @substr);
        ELSE
            SET @retval = CONCAT(@retval, '-');
        END IF;
        SET @i = @i + 1;
    UNTIL @i > LENGTH(str) END REPEAT;

    -- Replace subsequent dashes with a single one.
    SET @repeat = 1;
    REPEAT
        SET @retval = REPLACE(@retval, '--', '-');
        SET @repeat = @retval REGEXP '--';
    UNTIL @repeat = 0  END REPEAT;

    -- Do not begin or end with a dash.
    IF SUBSTRING(@retval, 1, 1) = '-' THEN
        SET @retval = SUBSTRING(@retval, 1);
    END IF;
    IF SUBSTRING(@retval, -1, 1) = '-' THEN
        SET @retval = SUBSTRING(@retval, 1, LENGTH(@retval) - 1);
    END IF;

    RETURN @retval;
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS fn_increment_slug;
DELIMITER $$
CREATE FUNCTION fn_increment_slug(str TEXT, incr INTEGER) RETURNS TEXT
BEGIN
    IF incr > 1 THEN
        SET str = REPLACE(str, CONCAT('-', incr - 1), '');
    END IF;
    IF LENGTH(CONCAT(str, '-', incr)) > 255 THEN
        SET str = SUBSTR(str, 1, -LENGTH(CONCAT('-', str)));
    END IF;
    RETURN CONCAT(str, '-', incr);
END;
$$
DELIMITER ;

-- This function is supposed to be used as a template of sorts,
-- since MySQL won't allow dynamic SQL in functions or procedures.
-- DELIMITER |
-- CREATE FUNCTION fn_TABLE_unique_slug(str TEXT) RETURNS TEXT
-- BEGIN
--    SET @uniq = 0;
--    SET @incr = 0;
--    REPEAT
--        SELECT COALESCE(id, 0) FROM _TABLE_ WHERE _FIELD_ = @retval INTO @check;
--        IF @check <> 0 THEN
--            SET @uniq = 1;
--        ELSE
--            SET @incr = @incr + 1;
--            SET str = fn_increment_slug(str, @incr);
--        END IF;
--    UNTIL @uniq = 1 END REPEAT;
--    RETURN str;
-- END;
-- |
-- DELIMITER ;

DROP FUNCTION IF EXISTS age;
DELIMITER $$
CREATE FUNCTION age(datefield DATE) RETURNS INT
BEGIN
    SET @datenow = NOW();
    SET @age = (YEAR(NOW()) - YEAR(datefield)) - (RIGHT(CURDATE(), 5) < RIGHT(datefield, 5));
    RETURN @age;
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS random;
DELIMITER $$
CREATE FUNCTION random() RETURNS FLOAT
BEGIN
    RETURN RAND();
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_voteable;
DELIMITER $$
CREATE FUNCTION fn_set_voteable() RETURNS INT
BEGIN
    INSERT INTO monolyth_voteable (votes) VALUES (0);
    RETURN LAST_INSERT_ID();
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_likeable;
DELIMITER $$
CREATE FUNCTION fn_set_likeable() RETURNS INT
BEGIN
    INSERT INTO monolyth_likeable (likes) VALUES (0);
    RETURN LAST_INSERT_ID();
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_commentable;
DELIMITER $$
CREATE FUNCTION fn_set_commentable() RETURNS INT
BEGIN
    INSERT INTO monolyth_commentable (comments) VALUES (0);
    RETURN LAST_INSERT_ID();
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_media;
DELIMITER $$
CREATE FUNCTION fn_set_media(
    myowner INT, mydata BLOB, myfilename TEXT, mymd5 TEXT,
    myfilesize INT, mymimetype TEXT
) RETURNS INT
BEGIN
    SELECT id FROM monolyth_media WHERE
        md5 = mymd5 AND filesize = myfilesize AND mimetype = mymimetype INTO @tmpid;
    IF @tmpid IS NULL THEN
        INSERT INTO monolyth_media (owner, data, filename, md5, filesize, mimetype)
            VALUES (myowner, mydata, myfilename, mymd5, myfilesize, mymimetype);
        RETURN LAST_INSERT_ID();
    ELSE
        RETURN @tmpid;
    END IF;
END;
$$
DELIMITER ;


DROP TRIGGER IF EXISTS monolyth_auth_link_auth_group_after_insert;
DELIMITER $$
CREATE TRIGGER monolyth_auth_link_auth_group_after_insert AFTER INSERT ON monolyth_auth_link_auth_group
FOR EACH ROW
BEGIN
    UPDATE monolyth_auth_group SET countmember = countmember + 1
        WHERE id = NEW.auth_group;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_insert_after;
DELIMITER $$
CREATE TRIGGER monolyth_auth_insert_after AFTER INSERT ON monolyth_auth
FOR EACH ROW
BEGIN
    INSERT INTO monolyth_auth_link_auth_group VALUES (NEW.id, 2);
    UPDATE monolyth_counters SET value = value + 1 WHERE name = 'users';
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_link_auth_group_after_delete;
DELIMITER $$
CREATE TRIGGER monolyth_auth_link_auth_group_after_delete AFTER DELETE ON monolyth_auth_link_auth_group
FOR EACH ROW
BEGIN
    UPDATE monolyth_auth_group SET countmember = countmember - 1
        WHERE id = OLD.auth_group;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_update_before;
DELIMITER $$
CREATE TRIGGER monolyth_auth_update_before BEFORE UPDATE ON monolyth_auth
FOR EACH ROW
BEGIN
    SET NEW.datemodified = NOW();
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_delete_after;
DELIMITER $$
CREATE TRIGGER monolyth_auth_delete_after AFTER DELETE ON monolyth_auth
FOR EACH ROW
BEGIN
    INSERT INTO monolyth_auth_deleted VALUES (OLD.id, OLD.name, NOW());
    UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users';
    DELETE FROM monolyth_auth_link_auth_group WHERE auth = OLD.id;
    DELETE FROM monolyth_acl WHERE owner = OLD.id;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_session_mysqlsucks;
DELIMITER $$
CREATE TRIGGER monolyth_session_mysqlsucks BEFORE INSERT ON monolyth_session
FOR EACH ROW
BEGIN
    SET NEW.dateactive = NOW();
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_session_log;
DELIMITER $$
CREATE TRIGGER monolyth_session_log AFTER DELETE ON monolyth_session
FOR EACH ROW
BEGIN
    INSERT INTO monolyth_session_log (userid, ip, user_agent, datecreated)
        VALUES (OLD.userid, OLD.ip, OLD.user_agent, OLD.datecreated);
    UPDATE monolyth_counters SET value = value - 1 WHERE name = 'people_online';
    IF OLD.userid IS NOT NULL THEN
        UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users_online';
    END IF;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS update_people_online_insert;
DELIMITER $$
CREATE TRIGGER update_people_online_insert AFTER INSERT ON monolyth_session
FOR EACH ROW
BEGIN
    UPDATE monolyth_counters SET value = value + 1 WHERE name = 'people_online';
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_session_update;
DELIMITER $$
CREATE TRIGGER monolyth_session_update BEFORE UPDATE ON monolyth_session
FOR EACH ROW
BEGIN
    SET NEW.dateactive = NOW();
    IF NEW.userid IS NOT NULL THEN
        UPDATE monolyth_auth SET dateactive = NEW.dateactive WHERE id = NEW.userid;
    END IF;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS update_people_online_update;
DELIMITER $$
CREATE TRIGGER update_people_online_update AFTER UPDATE ON monolyth_session
FOR EACH ROW
BEGIN
    IF OLD.userid IS NULL AND NEW.userid IS NOT NULL THEN
        UPDATE monolyth_counters SET value = value + 1 WHERE name = 'users_online';
    END IF;
    IF OLD.userid IS NOT NULL AND NEW.userid IS NULL THEN
        UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users_online';
    END IF;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_before;
DELIMITER $$
CREATE TRIGGER monolyth_comment_insert_before BEFORE INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    SET NEW.commentindex = COALESCE((SELECT MAX(commentindex) FROM
        monolyth_comment WHERE reference = NEW.reference), 0) + 1;
    SET NEW.hash = md5(CONCAT(NEW.ip, NEW.reference, NEW.datecreated, NEW.comment));
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_after;
DELIMITER $$
CREATE TRIGGER monolyth_comment_insert_after AFTER INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments + 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = NEW.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_delete_after;
DELIMITER $$
CREATE TRIGGER monolyth_comment_delete_after AFTER DELETE ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments - 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = OLD.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_update_before;
DELIMITER $$
CREATE TRIGGER monolyth_comment_update_before BEFORE UPDATE ON monolyth_comment
FOR EACH ROW
BEGIN
    IF OLD.comment <> NEW.comment THEN
        SET NEW.datemodified = NOW();
    END IF;
    SET NEW.hash = md5(CONCAT(NEW.ip, NEW.reference, NEW.datecreated, NEW.comment));
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_vote_insert_after;
DELIMITER $$
CREATE TRIGGER monolyth_vote_insert_after AFTER INSERT ON monolyth_vote
FOR EACH ROW
BEGIN
    UPDATE monolyth_voteable SET last = (SELECT id FROM monolyth_vote
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1),
        average = (average * votes + NEW.vote) / (votes + 1),
        votes = votes + 1
        WHERE id = NEW.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_vote_delete_after;
DELIMITER $$
CREATE TRIGGER monolyth_vote_delete_after AFTER DELETE ON monolyth_vote
FOR EACH ROW
BEGIN
    UPDATE monolyth_voteable SET last = (SELECT id FROM monolyth_vote
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1),
        average = (average * votes - OLD.vote) / (votes - 1),
        votes = votes - 1
        WHERE id = OLD.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_vote_update_after;
DELIMITER $$
CREATE TRIGGER monolyth_vote_update_after AFTER UPDATE ON monolyth_vote
FOR EACH ROW
BEGIN
    UPDATE monolyth_voteable SET
        average = (average * votes - OLD.vote + NEW.vote) / votes
        WHERE id = NEW.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_before;
DELIMITER $$
CREATE TRIGGER monolyth_comment_insert_before BEFORE INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    IF NEW.status & 1 = 0 THEN
        SET NEW.commentindex = COALESCE((SELECT commentindex FROM
            monolyth_comment WHERE reference = NEW.reference
            ORDER BY commentindex DESC LIMIT 1), 0) + 1;
    END IF;
    SET NEW.hash = md5(CONCAT(NEW.commentindex, NEW.reference, NOW(),
        NEW.comment));
    SET NEW.likes = fn_set_likeable();
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_after;
DELIMITER $$
CREATE TRIGGER monolyth_comment_insert_after AFTER INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments + 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = NEW.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_delete_after;
DELIMITER $$
CREATE TRIGGER monolyth_comment_delete_after AFTER DELETE ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments - 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = OLD.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_update_before;
DELIMITER $$
CREATE TRIGGER monolyth_comment_update_before BEFORE UPDATE ON monolyth_comment
FOR EACH ROW
BEGIN
    SET NEW.datemodified = NOW();
    SET NEW.hash = md5(CONCAT(NEW.commentindex, NEW.reference, NEW.datemodified,
        NEW.comment));
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_update_after;
DELIMITER $$
CREATE TRIGGER monolyth_comment_update_after AFTER UPDATE ON monolyth_comment
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        IF OLD.status & 3 = 0 AND NEW.status & 3 <> 0 THEN
            UPDATE monolyth_commentable SET comments = comments -1
                WHERE id = NEW.reference;
        ELSEIF OLD.status & 3 <> 0 AND NEW.status & 3 = 0 THEN
            UPDATE monolyth_commentable SET comments = comments + 1
                WHERE id = NEW.reference;
        END IF;
    END IF;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_like_insert_after;
DELIMITER $$
CREATE TRIGGER monolyth_like_insert_after AFTER INSERT ON monolyth_like
FOR EACH ROW
BEGIN
    UPDATE monolyth_likeable SET likes = likes + 1, last = NEW.id WHERE id = NEW.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_like_delete_after;
DELIMITER $$
CREATE TRIGGER monolyth_like_delete_after AFTER DELETE ON monolyth_like
FOR EACH ROW
BEGIN
    UPDATE monolyth_likeable SET likes = likes - 1 WHERE id = OLD.reference;
END;
$$
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_language_insert_before;
DELIMITER $$
CREATE TRIGGER monolyth_language_insert_before BEFORE INSERT ON monolyth_language
FOR EACH ROW
BEGIN
    SET NEW.title = (SELECT title FROM monolyth_language_all WHERE id = NEW.id);
END;
$$
DELIMITER ;

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

ALTER TABLE monolyth_auth_group ADD CONSTRAINT FOREIGN KEY (owner) REFERENCES monolyth_auth(id) ON DELETE CASCADE;
-- }}}

