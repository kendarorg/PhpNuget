-- https://gist.github.com/nskondratev/3e1bc68cbd491324f4155e07d22219cb

DELIMITER $$

DROP FUNCTION IF EXISTS `SEMVER_GTE` $$
CREATE FUNCTION `SEMVER_GTE`(_v1 VARCHAR(128), _v2 VARCHAR(128)) RETURNS TINYINT DETERMINISTIC
BEGIN
    DECLARE _next_v1 TEXT DEFAULT NULL;
    DECLARE _next_v2 TEXT DEFAULT NULL;
    DECLARE _nextlen_v1 INT DEFAULT NULL;
    DECLARE _nextlen_v2 INT DEFAULT NULL;
    DECLARE _value_v1 INT DEFAULT NULL;
    DECLARE _value_v2 INT DEFAULT NULL;
    DECLARE _res TINYINT DEFAULT 0;

    IF _v1 IS NULL OR _v2 IS NULL THEN
        RETURN (_res);
END IF;

    SET _res = 1;

    iterator:
    LOOP
        -- exit the loop if the list seems empty or was null;
        -- this extra caution is necessary to avoid an endless loop in the proc.
        IF LENGTH(TRIM(_v1)) = 0 OR _v1 IS NULL OR LENGTH(TRIM(_v2)) = 0 OR _v2 IS NULL THEN
            LEAVE iterator;
END IF;

        -- capture the next value from the list
        SET _next_v1 = SUBSTRING_INDEX(_v1,'.',1);
        SET _next_v2 = SUBSTRING_INDEX(_v2,'.',1);

        -- save the length of the captured value; we will need to remove this
        -- many characters + 1 from the beginning of the string
        -- before the next iteration
        SET _nextlen_v1 = LENGTH(_next_v1);
        SET _nextlen_v2 = LENGTH(_next_v2);

        -- trim the value of leading and trailing spaces, in case of sloppy strings
        SET _value_v1 = CAST(TRIM(_next_v1) AS SIGNED);
        SET _value_v2 = CAST(TRIM(_next_v2) AS SIGNED);

        IF _value_v1 < _value_v2 THEN
            SET _res = 0;
            LEAVE iterator;
END IF;

        -- rewrite the original string using the `INSERT()` string function,
        -- args are original string, start position, how many characters to remove,
        -- and what to "insert" in their place (in this case, we "insert"
        -- an empty string, which removes _nextlen + 1 characters)
        SET _v1 = INSERT(_v1,1,_nextlen_v1 + 1,'');
        SET _v2 = INSERT(_v2,1,_nextlen_v2 + 1,'');
END LOOP;

RETURN (_res);
END $$

DROP FUNCTION IF EXISTS `SEMVER_LT` $$
CREATE FUNCTION `SEMVER_LT`(_v1 VARCHAR(128), _v2 VARCHAR(128)) RETURNS TINYINT DETERMINISTIC
BEGIN
    DECLARE _next_v1 TEXT DEFAULT NULL;
    DECLARE _next_v2 TEXT DEFAULT NULL;
    DECLARE _nextlen_v1 INT DEFAULT NULL;
    DECLARE _nextlen_v2 INT DEFAULT NULL;
    DECLARE _value_v1 INT DEFAULT NULL;
    DECLARE _value_v2 INT DEFAULT NULL;
    DECLARE _res TINYINT DEFAULT 0;

    iterator:
    LOOP
        -- exit the loop if the list seems empty or was null;
        -- this extra caution is necessary to avoid an endless loop in the proc.
        IF LENGTH(TRIM(_v1)) = 0 OR _v1 IS NULL OR LENGTH(TRIM(_v2)) = 0 OR _v2 IS NULL THEN
            LEAVE iterator;
END IF;

        -- capture the next value from the list
        SET _next_v1 = SUBSTRING_INDEX(_v1,'.',1);
        SET _next_v2 = SUBSTRING_INDEX(_v2,'.',1);

        -- save the length of the captured value; we will need to remove this
        -- many characters + 1 from the beginning of the string
        -- before the next iteration
        SET _nextlen_v1 = LENGTH(_next_v1);
        SET _nextlen_v2 = LENGTH(_next_v2);

        -- trim the value of leading and trailing spaces, in case of sloppy strings
        SET _value_v1 = CAST(TRIM(_next_v1) AS SIGNED);
        SET _value_v2 = CAST(TRIM(_next_v2) AS SIGNED);

        IF _value_v1 != _value_v2 THEN
            IF _value_v1 < _value_v2 THEN
                SET _res = 1;
ELSE
                SET _res = 0;
END IF;
            LEAVE iterator;
END IF;

        -- rewrite the original string using the `INSERT()` string function,
        -- args are original string, start position, how many characters to remove,
        -- and what to "insert" in their place (in this case, we "insert"
        -- an empty string, which removes _nextlen + 1 characters)
        SET _v1 = INSERT(_v1,1,_nextlen_v1 + 1,'');
        SET _v2 = INSERT(_v2,1,_nextlen_v2 + 1,'');
END LOOP;

RETURN (_res);
END $$

DELIMITER ;