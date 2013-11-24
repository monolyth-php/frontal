
DROP TABLE IF EXISTS monolyth_acl CASCADE;
DROP TABLE IF EXISTS monolyth_acl_resource CASCADE;
DROP TABLE IF EXISTS monolyth_auth CASCADE;
DROP TABLE IF EXISTS monolyth_auth_group CASCADE;
DROP TABLE IF EXISTS monolyth_auth_link_auth_group CASCADE;
DROP TABLE IF EXISTS monolyth_auth_passreset CASCADE;
DROP TABLE IF EXISTS monolyth_auth_emailupdate CASCADE;
DROP TRIGGER IF EXISTS monolyth_auth_link_auth_group_after_insert;
DROP TRIGGER IF EXISTS monolyth_auth_insert_after;
DROP TRIGGER IF EXISTS monolyth_auth_link_auth_group_after_delete;
-- a backup table for deleted user names
DROP TABLE IF EXISTS monolyth_auth_deleted CASCADE;
DROP TRIGGER IF EXISTS monolyth_auth_delete_after;
DROP TABLE IF EXISTS monolyth_confirm CASCADE;
DROP TABLE IF EXISTS monolyth_session CASCADE;
DROP TRIGGER IF EXISTS monolyth_session_mysqlsucks;
DROP TABLE IF EXISTS monolyth_session_actionlog CASCADE;
DROP TRIGGER IF EXISTS monolyth_session_actionlog_mysqlsucks;
DROP TABLE IF EXISTS monolyth_session_log CASCADE;
DROP TRIGGER IF EXISTS monolyth_session_log;
DROP TRIGGER IF EXISTS monolyth_update_people_online_insert;
DROP TRIGGER IF EXISTS monolyth_update_people_online_update;

DROP TABLE IF EXISTS monolyth_counters CASCADE;
DROP TABLE IF EXISTS monolyth_confirm CASCADE;
DROP TABLE IF EXISTS monolyth_media CASCADE;
DROP TABLE IF EXISTS monolyth_commentable CASCADE;
DROP TABLE IF EXISTS monolyth_comment CASCADE;
DROP TABLE IF EXISTS monolyth_language CASCADE;

