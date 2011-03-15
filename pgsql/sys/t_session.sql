------------------------------------------------------
-- Sessions                                         --
------------------------------------------------------

-- begin
DROP TABLE t_session CASCADE;
CREATE TABLE t_session (
  sid		varchar(512)	PRIMARY KEY NOT NULL,
  expires	int				NOT NULL DEFAULT '0' CHECK ( expires >= 0 ),
  data		text			NOT NULL
--  client_ip 
--  server_ip
);

-- data

-- procedures
-- read session data
CREATE OR REPLACE FUNCTION f_t_session_read( _sid varchar ) RETURNS text AS $$
  DECLARE
    _exception varchar := 'f_t_session_read';
    _data      text    := '';
    _time      integer := f_time();
  BEGIN
    SELECT INTO _data data FROM t_session WHERE sid = _sid;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN _data;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_read( varchar ) FROM PUBLIC;


-- read not expired session data
CREATE OR REPLACE FUNCTION f_t_session_eread( _sid varchar ) RETURNS text AS $$
  DECLARE
    _exception varchar := 'f_t_session_eread';
    _data      text    := '';
    _time      integer := f_time();
  BEGIN
    SELECT INTO _data f_t_session_read( _sid );
    IF expires._data > _time THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN _data;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_eread( varchar ) FROM PUBLIC;


-- check if the session is expired
CREATE OR REPLACE FUNCTION f_t_session_expired( _sid varchar ) RETURNS int AS $$
  DECLARE
    _exception varchar := 'f_t_session_expired';
    _time      integer := f_time();
    _expires   integer := 0;
  BEGIN
    SELECT INTO _expires expires FROM t_session WHERE sid = _sid;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    IF _expires < _time THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN _expires;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_expired( varchar ) FROM PUBLIC;


-- write session data
-- TODO: consider 35-1 postgres example
CREATE OR REPLACE FUNCTION f_t_session_write( _sid varchar, _expires int, _data text ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'f_t_session_write';
    _time      integer := f_time();
    __expires  integer := _time + _expires;
  BEGIN
    UPDATE t_session SET data = _data, expires = __expires WHERE sid = _sid AND expires > _time;
    IF NOT FOUND THEN
      INSERT INTO t_session (sid, expires, data) VALUES ( _sid, __expires, _data );
      IF NOT FOUND THEN
        RAISE EXCEPTION '%', _exception;
      END IF;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_write( varchar, int, text ) FROM PUBLIC;


-- change session id
CREATE OR REPLACE FUNCTION f_t_session_change( _sid varchar, _new_sid varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'f_t_sessions_change';
    _time      integer := f_time();
  BEGIN
    UPDATE t_session SET sid = _new_sid WHERE sid = _sid AND expires > _time;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_change( varchar, varchar ) FROM PUBLIC;


-- destroy the session
CREATE OR REPLACE FUNCTION f_t_session_destroy( _sid varchar ) RETURNS bool AS $$
  DECLARE
    _exception varchar := 'f_t_session_destroy';
    _time      integer := f_time();
  BEGIN
    UPDATE t_session SET expires = '0' WHERE sid = _sid AND expires > _time;
    IF NOT FOUND THEN
      RAISE EXCEPTION '%', _exception;
    END IF;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_destroy( varchar ) FROM PUBLIC;


-- garbage collector
CREATE OR REPLACE FUNCTION f_t_session_gc() RETURNS int AS $$
  DECLARE
    _exception varchar := 'session_gc';
    _time      integer := f_time();
    _affrows   integer := 0;
  BEGIN
    DELETE FROM t_session WHERE expires < _time;
    GET DIAGNOSTICS _affrows = ROW_COUNT;
    RETURN _affrows;
  END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_gc() FROM PUBLIC;


-- mr proper
CREATE OR REPLACE FUNCTION f_t_session_clean() RETURNS bool AS $$
  BEGIN
    DELETE FROM t_session;
    RETURN FOUND;
  END;
$$ LANGUAGE plpgsql;
-- access grants
REVOKE ALL PRIVILEGES ON FUNCTION f_t_session_clean() FROM PUBLIC;
---- end
