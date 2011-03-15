--
-- Simple Postgres Security:
-- postgres user is the defacto root for postgres
--
-- 1. Step: Set password for postgres user
-- 1.1 modify pg_hba.conf
-- host		all		postgres	127.0.0.1/32	trust
--
-- 1.2 restart postgres
--
-- 1.3 connect to postgres
-- psql -U postgres -h 127.0.0.1
--
-- 1.4 set the password for postgres user
-- ALTER USER postgres WITH PASSWORD 'secret'
-- 
--
-- 2. Step: resecure the database
-- 2.1 modify pg_hba.conf (the same line as above: trust -> md5)
-- host		all		postgres	127.0.0.1/32	md5
--
-- 2.2 restart postgres
--
--
-- 3. Step: Allow cobra users to connect
-- 3.1 modify pg_hba.conf
-- host		cobra		cobra_session	127.0.0.1/32	md5
-- host		cobra		cobra_admin		127.0.0.1/32	md5
-- host		cobra		cobra_gc  		127.0.0.1/32	md5
-- # Optional
-- host		cobra		cobra_user		127.0.0.1/32	md5
-- # Optional lock down
-- host     cobra       postgres        127.0.0.1/32	reject
-- 3.2 restart postgres
--
--
-- 4. Step: Reset the database (run this file on the db)
-- psql -f dbinit.sql -U postgres -h localhost
--
--
-- Remarks:
-- cobra_session user can select tables and select,insert,delete session table, 
-- while cobra_admin is for administation (select,insert,delete) of all the tables.
--
--


-- clean
DROP DATABASE cobra;
-- session user (system)
DROP USER u_session;
DROP USER u_gc;
DROP USER u_admin;
DROP USER u_user;


-- create the database
-- maybe LOCATION = '/path/to/db'
CREATE DATABASE cobra WITH OWNER = postgres ENCODING = 'UTF8';


-- create the users, change the passwords, maybe VALID UNTIL ''
-- session user
CREATE USER u_session WITH PASSWORD 'cobra_session' NOCREATEDB NOCREATEUSER;

-- session garbage collector
CREATE USER u_gc WITH PASSWORD 'cobra_gc' NOCREATEDB NOCREATEUSER;

-- cobra admin user
CREATE USER u_admin WITH PASSWORD 'cobra_admin' NOCREATEDB NOCREATEUSER;

-- cobra general user
CREATE USER u_user WITH PASSWORD 'cobra_user' NOCREATEDB NOCREATEUSER;


-- groups
DROP GROUP g_system;
DROP GROUP g_application;

CREATE GROUP g_system WITH USER u_admin, u_session;
CREATE GROUP g_application WITH USER u_admin, u_user;
