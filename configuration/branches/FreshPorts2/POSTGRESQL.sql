--
-- $Id: POSTGRESQL.sql,v 1.1.2.1 2002-02-24 03:09:23 dan Exp $
--
-- Copyright (c) 1998-2002 DVL Software Limited
--

--
-- users, groups, permissions for postgresql
--

--
-- for the http server
--


-- DO THIS BEFORE YOU LOAD THE DATA

create group www;
create user www with password 'password';

alter group www add user www;




-- DO THIS AFTER YOU LOAD THE DATA
--
-- select access only
--
grant select on categories                     to group www;
grant select on commit_log                     to group www;
grant select on commit_log_elements            to group www;
grant select on commit_log_port_elements       to group www;
grant select on commit_log_ports               to group www;
grant select on commits_latest                 to group www;
grant select on element                        to group www;
grant select on element_revision               to group www;
grant select on housekeeping                   to group www;
grant select on ports                          to group www;
grant select on security_notice                to group www;
grant select on security_notice_elements       to group www;
grant select on security_notice_log            to group www;
grant select on system                         to group www;
grant select on system_branch                  to group www;
grant select on system_branch_element_revision to group www;
grant select on watch_list                     to group www;

--
-- select, insert, update
--
grant select, insert, update on users          to group www;
grant select,         update on users_id_seq   to group www;

--
-- select, insert, update, delete
--

grant select, insert, update, delete on watch_list_element  to group www;
grant select,         update         on watch_list_id_seq   to group www;

--
-- select, delete
--
grant select, delete on user_confirmations      to group www;

--
-- no access
--
-- watch_notice                   to group www;
-- watch_notice_id_seq            to group www;
-- watch_notice_log               to group www;
-- watch_notice_log_id_seq        to group www;


--
-- for scripts etc
--

create user commits with password 'ld6420uX';
create group commits;
alter group commits add user commits;

--
-- select access only
--
grant select, insert, update         on categories                     to group commits;
grant select, update                 on categories_id_seq              to group commits;
grant select, insert, update, delete on commit_log                     to group commits;
grant select, update                 on commit_log_id_seq              to group commits;

grant select, insert, update, delete on commit_log_elements            to group commits;
grant select, update                 on commit_log_elements_id_seq     to group commits;
grant select, update                 on commit_log_id_seq              to group commits;
grant select, insert, update, delete on commit_log_port_elements       to group commits;
grant select, insert, update, delete on commit_log_ports               to group commits;

grant select, insert,         delete on commits_latest                 to group commits;

grant select, insert, update, delete on element                        to group commits;
grant select, update                 on element_id_seq                 to group commits;
grant select, insert, update, delete on element_revision               to group commits;

grant select, insert, update, delete on ports                          to group commits;
grant select, update                 on ports_id_seq                   to group commits;
grant select, update                 on housekeeping                   to group commits;

grant select, insert, update, delete on system                         to group commits;
grant select, insert, update, delete on system_branch                  to group commits;
grant select, update                 on system_branch_id_seq           to group commits;
grant select, insert, update, delete on system_branch_element_revision to group commits;

grant select, insert, update, delete on security_notice                to group commits;
grant select, insert, update         on security_notice_elements       to group commits;
grant select, insert, update, delete on security_notice_log            to group commits;

grant select on users              to group commits;
grant select on watch_list         to group commits;
grant select on watch_list_element to group commits;

grant select, insert, update on watch_notice_log to group commits;
grant select, insert, update on watch_notice     to group commits;
