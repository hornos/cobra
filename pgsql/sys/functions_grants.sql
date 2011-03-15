-- function grants
GRANT EXECUTE ON FUNCTION f_seconds( varchar ) TO       u_admin;
GRANT EXECUTE ON FUNCTION f_time()             TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_time()             TO GROUP g_application;
GRANT EXECUTE ON FUNCTION f_time()             TO       u_gc;
GRANT EXECUTE ON FUNCTION f_microtime()        TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_microtime()        TO GROUP g_application;
GRANT EXECUTE ON FUNCTION f_microtime()        TO       u_gc;
GRANT EXECUTE ON FUNCTION f_timestamp()        TO GROUP g_system;
GRANT EXECUTE ON FUNCTION f_timestamp()        TO GROUP g_application;
GRANT EXECUTE ON FUNCTION f_timestamp()        TO       u_gc;
