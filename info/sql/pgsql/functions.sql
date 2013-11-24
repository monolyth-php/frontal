
CREATE OR REPLACE FUNCTION fn_set_likeable() RETURNS integer AS $$
BEGIN
    INSERT INTO monolyth_likeable (likes) VALUES (0);
    RETURN currval('monolyth_likeable_id_seq');
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_set_commentable() RETURNS integer AS $$
BEGIN
    INSERT INTO monolyth_commentable (comments) VALUES (0);
    RETURN currval('monolyth_commentable_id_seq');
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_set_voteable() RETURNS integer AS $$
BEGIN
    INSERT INTO monolyth_voteable (votes) VALUES (0);
    RETURN currval('monolyth_voteable_id_seq');
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_set_media(
    IN myowner INTEGER, IN mydata BYTEA, IN myfilename TEXT, IN mymd5 TEXT,
    IN myfilesize INTEGER, IN mymimetype TEXT
) RETURNS integer AS $$
DECLARE tmpid integer;
BEGIN
    SELECT id FROM monolyth_media WHERE
        md5 = mymd5 AND filesize = myfilesize AND mimetype = mymimetype INTO tmpid;
    IF NOT FOUND THEN
        INSERT INTO monolyth_media (owner, data, filename, md5, filesize, mimetype)
            VALUES (myowner, mydata, myfilename, mymd5, myfilesize, mymimetype);
        RETURN currval('monolyth_media_id_seq');
    END IF;
    RETURN tmpid;
END;
$$ LANGUAGE plpgsql;

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

-- This function is supposed to be used as a template of sorts,
-- since MySQL won't allow dynamic SQL in functions or procedures.
-- DELIMITER |
-- CREATE FUNCTION fn_TABLE_unique_slug(str TEXT) RETURNS TEXT
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
-- |
-- DELIMITER ;

CREATE OR REPLACE FUNCTION fn_log_visit(uid INTEGER, sid TEXT) RETURNS INTEGER AS $$
BEGIN
    DELETE FROM monolyth_auth_visit WHERE datecreated < NOW() - '1 month'::interval
        OR (owner = uid AND sessionid = sid);
    INSERT INTO monolyth_auth_visit (owner, sessionid) VALUES (uid, sid);
    RETURN 1;
END;
$$ LANGUAGE 'plpgsql';

