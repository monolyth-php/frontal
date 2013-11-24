
CREATE OR REPLACE FUNCTION monolyth_auth_link_auth_group_after_insert()
RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_auth_group SET countmember = countmember + 1
        WHERE id = NEW.auth_group;
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_link_auth_group_after_insert
    AFTER INSERT ON monolyth_auth_link_auth_group
    FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_link_auth_group_after_insert();

CREATE OR REPLACE FUNCTION monolyth_auth_insert_after() RETURNS "trigger" AS $$
BEGIN
    INSERT INTO monolyth_auth_link_auth_group VALUES (NEW.id, 2);
    UPDATE monolyth_counters SET value = value + 1 WHERE name = 'users';
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_insert_after AFTER INSERT ON monolyth_auth
    FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_insert_after();

CREATE OR REPLACE FUNCTION monolyth_auth_link_auth_group_after_delete()
RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_auth_group SET countmember = countmember - 1
        WHERE id = OLD.auth_group;
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_link_auth_group_after_delete
    AFTER DELETE ON monolyth_auth_link_auth_group
    FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_link_auth_group_after_delete();

CREATE OR REPLACE FUNCTION monolyth_auth_delete_after() RETURNS "trigger" AS $$
BEGIN
    INSERT INTO monolyth_auth_deleted VALUES (OLD.id, OLD.name);
    UPDATE monolyth_counters SET value = value - 1 WHERE name = 'users';
    DELETE FROM monolyth_auth_link_auth_group WHERE auth = OLD.id;
    DELETE FROM monolyth_acl WHERE owner = OLD.id;
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_auth_delete_after AFTER DELETE ON monolyth_auth
    FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_delete_after();

CREATE OR REPLACE FUNCTION monolyth_session_log() RETURNS "trigger" AS $$
BEGIN
	INSERT INTO monolyth_session_log (userid, ip, user_agent, datecreated)
		VALUES (OLD.userid, OLD.ip, OLD.user_agent, OLD.datecreated);
	RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
CREATE TRIGGER monolyth_session_log AFTER DELETE ON monolyth_session
    FOR EACH ROW EXECUTE PROCEDURE monolyth_session_log();

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

CREATE OR REPLACE FUNCTION monolyth_comment_insert_before() RETURNS "trigger" AS $$
BEGIN
    NEW.commentindex := COALESCE((SELECT MAX(commentindex) FROM
        monolyth_comment WHERE reference = NEW.reference), 0) + 1;
    IF NEW.datecreated IS NULL THEN
        NEW.datecreated := NOW();
    END IF;
    NEW.hash := md5(NEW.ip::text || NEW.reference::text || NEW.datecreated::text || NEW.comment::text);
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_comment_insert_before ON monolyth_comment;
CREATE TRIGGER monolyth_comment_insert_before BEFORE INSERT ON monolyth_comment
    FOR EACH ROW EXECUTE PROCEDURE monolyth_comment_insert_before();

CREATE OR REPLACE FUNCTION monolyth_comment_insert_after() RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_commentable SET comments = comments + 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = NEW.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = NEW.reference;
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_comment_insert_after ON monolyth_comment;
CREATE TRIGGER monolyth_comment_insert_after AFTER INSERT ON monolyth_comment
    FOR EACH ROW EXECUTE PROCEDURE monolyth_comment_insert_after();

CREATE OR REPLACE FUNCTION monolyth_comment_delete_after() RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_commentable SET comments = comments - 1,
        last = (SELECT id FROM monolyth_comment
            WHERE reference = OLD.reference ORDER BY datecreated DESC LIMIT 1)
        WHERE id = OLD.reference;
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_comment_delete_after ON monolyth_comment;
CREATE TRIGGER monolyth_comment_delete_after AFTER DELETE ON monolyth_comment
    FOR EACH ROW EXECUTE PROCEDURE monolyth_comment_delete_after();

CREATE OR REPLACE FUNCTION monolyth_comment_update_before() RETURNS "trigger" AS $$
BEGIN
    IF OLD.comment <> NEW.comment THEN
        NEW.datemodified := NOW();
    END IF;
    NEW.hash := md5(NEW.ip::text || NEW.reference::text || NEW.datecreated::text || NEW.comment::text);
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_comment_update_before ON monolyth_comment;
CREATE TRIGGER monolyth_comment_update_before BEFORE UPDATE ON monolyth_comment
    FOR EACH ROW EXECUTE PROCEDURE monolyth_comment_update_before();

CREATE OR REPLACE FUNCTION monolyth_vote_insert_after() RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_voteable SET
        votes = votes + 1,
        average = ((votes * average) + NEW.vote) / (votes + 1),
        lastvote = NEW.id
    WHERE id = NEW.reference;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS monolyth_vote_insert_after ON monolyth_vote;
CREATE TRIGGER monolyth_vote_insert_after AFTER INSERT ON monolyth_vote
    FOR EACH ROW EXECUTE PROCEDURE monolyth_vote_insert_after();

CREATE OR REPLACE FUNCTION monolyth_vote_delete_after() RETURNS "trigger" AS $$
DECLARE newvote INTEGER;
BEGIN
    IF (SELECT votes FROM monolyth_voteable WHERE id = OLD.reference) = 1 THEN
        UPDATE monolyth_voteable SET votes = 0, average = 0, lastvote = NULL
            WHERE id = OLD.reference;
    ELSE
        UPDATE monolyth_voteable SET
            votes = votes - 1,
            average = ((votes * average) - OLD.vote) / (votes - 1),
            lastvote = (SELECT id FROM monolyth_vote WHERE reference = OLD.reference
                ORDER BY datecreated DESC LIMIT 1)
        WHERE id = OLD.reference;
    END IF;
    INSERT INTO monolyth_vote_log VALUES (
        OLD.id, OLD.reference, OLD.owner, OLD.vote,
        OLD.datecreated, OLD.datemodified, OLD.status
    );
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS monolyth_vote_delete_after ON monolyth_vote;
CREATE TRIGGER monolyth_vote_delete_after AFTER DELETE ON monolyth_vote
    FOR EACH ROW EXECUTE PROCEDURE monolyth_vote_delete_after();

CREATE OR REPLACE FUNCTION monolyth_auth_visit_insert_after() RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_auth SET visits = visits + 1 WHERE id = NEW.owner;
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_auth_visit_insert_after ON monolyth_auth_visit;
CREATE TRIGGER monolyth_auth_visit_insert_after AFTER INSERT ON monolyth_auth_visit
    FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_visit_insert_after();

CREATE OR REPLACE FUNCTION monolyth_auth_visit_delete_after() RETURNS "trigger" AS $$
BEGIN
    UPDATE monolyth_auth SET visits = visits - 1 WHERE id = OLD.owner;
    RETURN OLD;
END;
$$ LANGUAGE 'plpgsql';
DROP TRIGGER IF EXISTS monolyth_auth_visit_delete_after ON monolyth_auth_visit;
CREATE TRIGGER monolyth_auth_visit_delete_after AFTER DELETE ON monolyth_auth_visit
    FOR EACH ROW EXECUTE PROCEDURE monolyth_auth_visit_delete_after();

