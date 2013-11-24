
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

