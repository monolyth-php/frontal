
DROP FUNCTION IF EXISTS fn_generate_slug;
DELIMITER |
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
|
DELIMITER ;

DROP FUNCTION IF EXISTS fn_increment_slug;
DELIMITER |
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
|
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
DELIMITER |
CREATE FUNCTION age(datefield DATE) RETURNS INT
BEGIN
    SET @datenow = NOW();
    SET @age = (YEAR(NOW()) - YEAR(datefield)) - (RIGHT(CURDATE(), 5) < RIGHT(datefield, 5));
    RETURN @age;
END;
|
DELIMITER ;

DROP FUNCTION IF EXISTS random;
DELIMITER |
CREATE FUNCTION random() RETURNS FLOAT
BEGIN
    RETURN RAND();
END;
|
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_voteable;
DELIMITER |
CREATE FUNCTION fn_set_voteable() RETURNS INT
BEGIN
    INSERT INTO monolyth_voteable (votes) VALUES (0);
    RETURN LAST_INSERT_ID();
END;
|
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_likeable;
DELIMITER |
CREATE FUNCTION fn_set_likeable() RETURNS INT
BEGIN
    INSERT INTO monolyth_likeable (likes) VALUES (0);
    RETURN LAST_INSERT_ID();
END;
|
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_commentable;
DELIMITER |
CREATE FUNCTION fn_set_commentable() RETURNS INT
BEGIN
    INSERT INTO monolyth_commentable (comments) VALUES (0);
    RETURN LAST_INSERT_ID();
END;
|
DELIMITER ;

DROP FUNCTION IF EXISTS fn_set_media;
DELIMITER |
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
|
DELIMITER ;

