
DROP TRIGGER IF EXISTS monolyth_auth_link_auth_group_after_insert;
DELIMITER |
CREATE TRIGGER monolyth_auth_link_auth_group_after_insert
AFTER INSERT ON monolyth_auth_link_auth_group
FOR EACH ROW
BEGIN
    UPDATE monolyth_auth_group SET countmember = countmember + 1
        WHERE id = NEW.auth_group;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_insert_after;
DELIMITER |
CREATE TRIGGER monolyth_auth_insert_after AFTER INSERT ON monolyth_auth
FOR EACH ROW
BEGIN
    INSERT INTO monolyth_auth_link_auth_group VALUES (NEW.id, 2);
    UPDATE monolyth_counters SET value = value + 1 WHERE name = 'users';
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_link_auth_group_after_delete;
DELIMITER |
CREATE TRIGGER monolyth_auth_link_auth_group_after_delete
AFTER DELETE ON monolyth_auth_link_auth_group
FOR EACH ROW
BEGIN
    UPDATE monolyth_auth_group SET countmember = countmember - 1
        WHERE id = OLD.auth_group;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_update_before;
DELIMITER |
CREATE TRIGGER monolyth_auth_update_before BEFORE UPDATE ON monolyth_auth
FOR EACH ROW
BEGIN
    SET NEW.datemodified = NOW();
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_auth_delete_after;
DELIMITER |
CREATE TRIGGER monolyth_auth_delete_after AFTER DELETE ON monolyth_auth
FOR EACH ROW
BEGIN
    INSERT INTO monolyth_auth_deleted VALUES (OLD.id, OLD.name, NOW());
    UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users';
    DELETE FROM monolyth_auth_link_auth_group WHERE auth = OLD.id;
    DELETE FROM monolyth_acl WHERE owner = OLD.id;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_session_mysqlsucks;
DELIMITER |
CREATE TRIGGER monolyth_session_mysqlsucks BEFORE INSERT ON monolyth_session
FOR EACH ROW
BEGIN
    SET NEW.dateactive = NOW();
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_session_log;
DELIMITER |
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
|
DELIMITER ;

DROP TRIGGER IF EXISTS update_people_online_insert;
DELIMITER |
CREATE TRIGGER update_people_online_insert AFTER INSERT ON monolyth_session
FOR EACH ROW
BEGIN
    UPDATE monolyth_counters SET value = value + 1 WHERE name = 'people_online';
END;
|
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
DELIMITER |
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
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_before;
DELIMITER |
CREATE TRIGGER monolyth_comment_insert_before BEFORE INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    SET NEW.commentindex = COALESCE((SELECT MAX(commentindex) FROM
        monolyth_comment WHERE reference = NEW.reference), 0) + 1;
    SET NEW.hash = md5(CONCAT(NEW.ip, NEW.reference, NEW.datecreated, NEW.comment));
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_after;
DELIMITER |
CREATE TRIGGER monolyth_comment_insert_after AFTER INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments + 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = NEW.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_delete_after;
DELIMITER |
CREATE TRIGGER monolyth_comment_delete_after AFTER DELETE ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments - 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = OLD.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_update_before;
DELIMITER |
CREATE TRIGGER monolyth_comment_update_before BEFORE UPDATE ON monolyth_comment
FOR EACH ROW
BEGIN
    IF OLD.comment <> NEW.comment THEN
        SET NEW.datemodified = NOW();
    END IF;
    SET NEW.hash = md5(CONCAT(NEW.ip, NEW.reference, NEW.datecreated, NEW.comment));
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_vote_insert_after;
DELIMITER |
CREATE TRIGGER monolyth_vote_insert_after AFTER INSERT ON monolyth_vote
FOR EACH ROW
BEGIN
    UPDATE monolyth_voteable SET last = (SELECT id FROM monolyth_vote
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1),
        average = (average * votes + NEW.vote) / (votes + 1),
        votes = votes + 1
        WHERE id = NEW.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_vote_delete_after;
DELIMITER |
CREATE TRIGGER monolyth_vote_delete_after AFTER DELETE ON monolyth_vote
FOR EACH ROW
BEGIN
    UPDATE monolyth_voteable SET last = (SELECT id FROM monolyth_vote
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1),
        average = (average * votes - OLD.vote) / (votes - 1),
        votes = votes - 1
        WHERE id = OLD.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_vote_update_after;
DELIMITER |
CREATE TRIGGER monolyth_vote_update_after AFTER UPDATE ON monolyth_vote
FOR EACH ROW
BEGIN
    UPDATE monolyth_voteable SET
        average = (average * votes - OLD.vote + NEW.vote) / votes
        WHERE id = NEW.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_before;
DELIMITER |
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
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_insert_after;
DELIMITER |
CREATE TRIGGER monolyth_comment_insert_after AFTER INSERT ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments + 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = NEW.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_delete_after;
DELIMITER |
CREATE TRIGGER monolyth_comment_delete_after AFTER DELETE ON monolyth_comment
FOR EACH ROW
BEGIN
    UPDATE monolyth_commentable SET comments = comments - 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = OLD.reference;
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_update_before;
DELIMITER |
CREATE TRIGGER monolyth_comment_update_before BEFORE UPDATE ON monolyth_comment
FOR EACH ROW
BEGIN
    SET NEW.datemodified = NOW();
    SET NEW.hash = md5(CONCAT(NEW.commentindex, NEW.reference, NEW.datemodified,
        NEW.comment));
END;
|
DELIMITER ;

DROP TRIGGER IF EXISTS monolyth_comment_update_after;
DELIMITER |
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
|
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
DELIMITER |
CREATE TRIGGER monolyth_language_insert_before BEFORE INSERT ON monolyth_language
FOR EACH ROW
BEGIN
    SET NEW.title = (SELECT title FROM monolyth_language_all WHERE id = NEW.id);
END;
|
DELIMITER ;

