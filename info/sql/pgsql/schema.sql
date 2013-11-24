
CREATE TABLE monolyth_auth (
    id serial PRIMARY KEY,
    name varchar(255),
    pass varchar(255),
    salt varchar(255),
    email varchar(255),
    datecreated timestamp with time zone NOT NULL default NOW(),
    ipcreated inet,
    datemodified timestamp with time zone,
    ipmodified inet,
    dateactive timestamp with time zone,
    ipactive inet,
    status bigint not null default 0,
    feature bigint not null default 0,
    media bigint
);
CREATE UNIQUE INDEX monolyth_auth_name_key ON monolyth_auth(LOWER(name));
CREATE UNIQUE INDEX monolyth_auth_email_key ON monolyth_auth(LOWER(email));
CREATE INDEX monolyth_auth_datecreated_key ON monolyth_auth(datecreated);
CREATE INDEX monolyth_auth_datemodified_key ON monolyth_auth(datemodified);
CREATE INDEX monolyth_auth_status_key ON monolyth_auth(status);
CREATE INDEX monolyth_auth_feature_key ON monolyth_auth(feature);
CREATE INDEX monolyth_auth_media_key ON monolyth_auth(media);

CREATE TABLE monolyth_auth_visit (
    owner INTEGER NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    sessionid VARCHAR(32) NOT NULL,
    datecreated TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    PRIMARY KEY(owner, sessionid)
);

CREATE TABLE monolyth_acl_resource (
    id serial PRIMARY KEY,
    parent integer REFERENCES monolyth_acl_resource(id) ON DELETE SET NULL,
    name varchar(255) NOT NULL,
    pk varchar(255)
);
CREATE UNIQUE INDEX monolyth_acl_resource_parent_name_pk_key
    ON monolyth_acl_resource(parent, name, pk);


CREATE TABLE monolyth_auth_group (
    id serial PRIMARY KEY,
    parent integer REFERENCES monolyth_auth_group(id) ON DELETE CASCADE,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL,
    owner integer NOT NULL,
    description text,
    countmember integer NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX monolyth_auth_group_name_key ON monolyth_auth_group(name);
CREATE UNIQUE INDEX monolyth_auth_group_slug_key ON monolyth_auth_group(slug);
CREATE INDEX monolyth_auth_group_parent_key ON monolyth_auth_group(parent);
CREATE INDEX monolyth_auth_group_owner_key ON monolyth_auth_group(owner);

CREATE TABLE monolyth_acl (
    id serial PRIMARY KEY,
    acl_resource integer REFERENCES monolyth_acl_resource(id) ON DELETE CASCADE,
    owner integer REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    auth_group integer REFERENCES monolyth_auth_group(id) ON DELETE CASCADE,
    action integer NOT NULL default 0
);
CREATE INDEX monolyth_acl_action_key ON monolyth_acl(action);
CREATE UNIQUE INDEX monolyth_acl_resource_owner_auth_group_key
    ON monolyth_acl(acl_resource, owner, auth_group);

CREATE TABLE monolyth_auth_link_auth_group (
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
CREATE UNIQUE INDEX monolyth_counters_name_key ON monolyth_counters(name);

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
CREATE INDEX monolyth_confirm_tablename_fieldname_newvalue_key
    ON monolyth_confirm(tablename, fieldname, newvalue);

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
CREATE UNIQUE INDEX monolyth_media_md5_owner_filesize_mimetype_key ON
	monolyth_media(md5, owner, filesize, mimetype);
CREATE INDEX monolyth_media_owner_key ON monolyth_media(owner);
CREATE INDEX monolyth_media_folder_key ON monolyth_media(folder);
ALTER TABLE monolyth_auth ADD FOREIGN KEY(media) REFERENCES monolyth_media(id) ON DELETE SET NULL;

-- The tables monolyth_comment(able), monolyth_vote(able) and
-- monolyth_like(able) store generic comments, votes and likes for random
-- objects. A table supporting one of these should define an 'on delete' trigger
-- to also delete the respective items from these tables, since we can't use
-- foreign keys for that.
-- Additionally, implementing tables should define a field comments, votes
-- and/or likes holding the correct id.

CREATE TABLE monolyth_likeable (
    id serial PRIMARY KEY,
    likes integer NOT NULL,
    last integer DEFAULT NULL
);
CREATE INDEX monolyth_likeable_likes_key ON monolyth_likeable(likes);
CREATE INDEX monolyth_likeable_last_key ON monolyth_likeable(last);

CREATE TABLE monolyth_like (
    id serial PRIMARY KEY,
    reference integer NOT NULL REFERENCES monolyth_likeable(id) ON DELETE CASCADE,
    owner integer NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    datecreated timestamp with time zone NOT NULL DEFAULT NOW()
);
CREATE UNIQUE INDEX monolyth_like_reference_owner_key ON monolyth_like(reference, owner);
CREATE INDEX monolyth_like_datecreated_key ON monolyth_like(datecreated);
ALTER TABLE monolyth_likeable ADD FOREIGN KEY(last) REFERENCES monolyth_like(id) ON DELETE SET NULL;

CREATE TABLE monolyth_commentable (
    id serial PRIMARY KEY,
    comments integer NOT NULL DEFAULT 0,
    last integer DEFAULT NULL
);
CREATE INDEX monolyth_commentable_comments_key ON monolyth_commentable(comments);
CREATE INDEX monolyth_commentable_last_key ON monolyth_commentable(last);

CREATE TABLE monolyth_comment (
    id serial PRIMARY KEY,
    hash varchar(32) NOT NULL,
    reference integer NOT NULL REFERENCES monolyth_commentable(id) ON DELETE CASCADE,
    replyto integer DEFAULT NULL REFERENCES monolyth_comment(id) ON DELETE SET NULL,
    commentindex integer,
    owner integer DEFAULT NULL REFERENCES monolyth_auth(id) ON DELETE SET NULL,
    name varchar(255) DEFAULT NULL,
    email varchar(255) DEFAULT NULL,
    homepage varchar(255) DEFAULT NULL,
    ip inet NOT NULL,
    comment text NOT NULL,
    datecreated timestamp with time zone NOT NULL DEFAULT NOW(),
    datemodified timestamp with time zone DEFAULT NULL,
    status integer NOT NULL DEFAULT 0
);
CREATE INDEX monolyth_comment_hash_key ON monolyth_comment(hash);
CREATE INDEX monolyth_comment_references_key ON monolyth_comment(reference);
CREATE INDEX monolyth_comment_commentindex_key ON monolyth_comment(commentindex);
CREATE INDEX monolyth_comment_owner_key ON monolyth_comment(owner);
CREATE INDEX monolyth_comment_ip_key ON monolyth_comment(ip);
CREATE INDEX monolyth_comment_datecreated_key ON monolyth_comment(datecreated);
CREATE INDEX monolyth_comment_datemodified_key ON monolyth_comment(datemodified);
CREATE INDEX monolyth_comment_status_key ON monolyth_comment(status);
CREATE INDEX monolyth_comment_replyto_key ON monolyth_comment(replyto);
ALTER TABLE monolyth_commentable ADD FOREIGN KEY(last) REFERENCES monolyth_comment(id) ON DELETE SET NULL;

CREATE TABLE monolyth_voteable (
    id serial PRIMARY KEY,
    votes integer NOT NULL DEFAULT 0,
    average float NOT NULL DEFAULT 0,
    last integer DEFAULT NULL
);
CREATE INDEX monolyth_voteable_votes_key ON monolyth_voteable(votes);
CREATE INDEX monolyth_voteable_average_key ON monolyth_voteable(average);
CREATE INDEX monolyth_voteable_last_key ON monolyth_voteable(last);

CREATE TABLE monolyth_vote (
    id serial PRIMARY KEY,
    reference integer NOT NULL REFERENCES monolyth_voteable(id) ON DELETE CASCADE,
    owner integer NOT NULL REFERENCES monolyth_auth(id) ON DELETE CASCADE,
    vote integer NOT NULL DEFAULT 0,
    datecreated timestamp with time zone NOT NULL DEFAULT NOW(),
    datemodified timestamp with time zone DEFAULT NULL,
    status integer NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX monolyth_vote_reference_owner_key ON monolyth_vote(reference, owner);
CREATE INDEX monolyth_vote_datecreated_key ON monolyth_vote(datecreated);
CREATE INDEX monolyth_vote_datemodified_key ON monolyth_vote(datemodified);
CREATE INDEX monolyth_vote_vote_key ON monolyth_vote(vote);
ALTER TABLE monolyth_voteable ADD FOREIGN KEY(last) REFERENCES monolyth_vote(id) ON DELETE SET NULL;

CREATE TABLE monolyth_vote_log (
    id integer NOT NULL PRIMARY KEY,
    reference integer NOT NULL,
    owner integer NOT NULL,
    vote integer NOT NULL DEFAULT 0,
    datecreated timestamp with time zone NOT NULL DEFAULT NOW(),
    datemodified timestamp with time zone DEFAULT NULL,
    status integer NOT NULL DEFAULT 0
);
CREATE INDEX monolyth_vote_log_datecreated_key ON monolyth_vote_log(datecreated);
CREATE INDEX monolyth_vote_log_datemodified_key ON monolyth_vote_log(datemodified);
CREATE INDEX monolyth_vote_log_vote_key ON monolyth_vote_log(vote);

CREATE TABLE monolyth_language_all (
    id SERIAL PRIMARY KEY,
    code varchar(5) NOT NULL,
    lc_code varchar(10) NOT NULL,
    title varchar(64) NOT NULL,
    fallback integer REFERENCES monolyth_language_all(id) ON DELETE SET NULL
);
CREATE UNIQUE INDEX monolyth_language_all_code_key
    ON monolyth_language_all(code);
CREATE UNIQUE INDEX monolyth_language_all_lc_code_key
    ON monolyth_language_all(lc_code);
CREATE UNIQUE INDEX monolyth_language_all_title_key
	ON monolyth_language_all(title);
CREATE INDEX monolyth_language_all_fallback_key
    ON monolyth_language_all(fallback);

CREATE TABLE monolyth_language (
    id integer PRIMARY KEY REFERENCES monolyth_language_all(id) ON DELETE CASCADE,
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
CREATE UNIQUE INDEX monolyth_country_code_key ON monolyth_country(code);
CREATE INDEX monolyth_country_status_key ON monolyth_country(status);

CREATE TABLE monolyth_country_i18n (
    id INTEGER NOT NULL REFERENCES monolyth_country(id) ON DELETE CASCADE,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    title varchar(128) NOT NULL,
    PRIMARY KEY(id, language)
);

CREATE TABLE monolyth_country_language (
    country integer NOT NULL REFERENCES monolyth_country(id) ON DELETE CASCADE,
    language integer NOT NULL REFERENCES monolyth_country(id) ON DELETE CASCADE,
    PRIMARY KEY(country, language)
);

CREATE TABLE monolyth_text (
    id varchar(64) NOT NULL PRIMARY KEY,
    status integer NOT NULL DEFAULT 0
);

CREATE TABLE monolyth_text_i18n (
    id varchar(64) NOT NULL REFERENCES monolyth_text(id) ON DELETE CASCADE ON UPDATE CASCADE,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    content text NOT NULL,
    PRIMARY KEY (id, language)
);

CREATE TABLE monolyth_mail_template (
    id varchar(64) NOT NULL,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    description text,
    html text NOT NULL,
    plain text NOT NULL,
    PRIMARY KEY(id, language)
);

CREATE TABLE monolyth_mail (
    id varchar(64) NOT NULL,
    language integer NOT NULL REFERENCES monolyth_language(id) ON DELETE CASCADE,
    template varchar(64),
    templatelanguage integer NOT NULL,
    description text,
    sender varchar(255) NOT NULL,
    subject varchar(255) NOT NULL,
    html text NOT NULL,
    plain text NOT NULL,
    PRIMARY KEY(id, language),
    FOREIGN KEY(template, templatelanguage) REFERENCES monolyth_mail_template(id, language) ON DELETE SET NULL
);
CREATE INDEX monolyth_mail_template_key ON monolyth_mail(template);

CREATE TABLE monolyth_cookie (
    id varchar(40) NOT NULL PRIMARY KEY,
    ip inet NOT NULL,
    user_agent text NOT NULL,
    settings integer NOT NULL DEFAULT 1,
    datecreated timestamp with time zone not null default now()
);

