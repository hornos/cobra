--
-- tables
--
GRANT SELECT, INSERT, UPDATE, DELETE ON t_session TO u_admin;
--
-- functions
--
GRANT EXECUTE ON FUNCTION f_t_session_read( varchar )               TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_t_session_eread( varchar )              TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_t_session_expired( varchar )            TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_t_session_write( varchar, int, text )   TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_t_session_change( varchar, varchar )    TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_t_session_destroy( varchar )            TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_t_session_gc()                          TO u_admin;
GRANT EXECUTE ON FUNCTION f_t_session_gc()                          TO u_gc;
GRANT EXECUTE ON FUNCTION f_t_session_clean()                       TO u_admin;
