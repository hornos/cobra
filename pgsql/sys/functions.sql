-- Language
CREATE LANGUAGE 'plpgsql';

------------------------------------------------------
-- Time Related Functions                           --
------------------------------------------------------

-- Return seconds from now shifted by a time string
CREATE OR REPLACE FUNCTION f_seconds( _shift varchar ) RETURNS int AS $$
  BEGIN
    RETURN extract( epoch FROM now() + _shift::interval )::integer;
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_seconds( varchar ) FROM PUBLIC;


-- Return time from epoch in seconds
CREATE OR REPLACE FUNCTION f_time() RETURNS int AS $$
  BEGIN
    RETURN extract( EPOCH FROM CURRENT_TIMESTAMP(0) );
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_time() FROM PUBLIC;


-- Return time from epoch in seconds with microseconds
CREATE OR REPLACE FUNCTION f_microtime() RETURNS timestamp AS $$
  BEGIN
    RETURN CURRENT_TIMESTAMP(6);
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_microtime() FROM PUBLIC;


-- Return the current timestamp
CREATE OR REPLACE FUNCTION f_timestamp() RETURNS timestamp AS $$
  BEGIN
    RETURN CURRENT_TIMESTAMP(0);
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_timestamp() FROM PUBLIC;
