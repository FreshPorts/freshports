--
-- $Id: POSTGRESQL.sql,v 1.1.2.39 2003-11-14 04:40:15 dan Exp $
--
-- Copyright (c) 1998-2003 DVL Software Limited
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
grant select on commit_log                     to group www;
grant select on commit_log_elements            to group www;
grant select on commit_log_port_elements       to group www;
grant select on commit_log_ports_elements      to group www;
grant select on commit_log_ports               to group www;
grant select on commit_log_ports_ignore        to group www;
grant select on commits_latest                 to group www;
grant select on latest_commits_ports           to group www;
grant select on element                        to group www;
grant select on element_revision               to group www;
grant select on ports                          to group www;
grant select on ports_categories               to group www;
grant select on ports_active                   to group www;
grant select on ports_all                      to group www;
grant select on system                         to group www;
grant select on system_branch                  to group www;
grant select on system_branch_element_revision to group www;
grant select on tasks                          to group www;
grant select on user_tasks                     to group www;

--
-- select, update
--
grant select, update on categories             to group www;

--
-- select, insert, update
--

grant select, insert, update on users          to group www;
grant select,         update on users_id_seq   to group www;

grant select, insert, update on security_notice        to group www;
grant update                 on security_notice_id_seq to group www;

grant select, insert         on security_notice_audit         to group www;
grant select, update         on security_notice_audit_id_seq  to group www;

grant select, insert, update, delete on announcements              to group www;
grant update on announcements_id_seq                               to group www;
grant select, insert, delete, update on committer_notify           to group www;
grant select, insert, delete, update on watch_list                 to group www;
grant select, insert, delete, update on watch_list_staging         to group www;
grant select,                 update on watch_list_staging_id_seq  to group www;

--
-- select, insert, update, delete
--

grant select, insert, update, delete on watch_list_element             to group www;
grant select,         update         on watch_list_id_seq              to group www;

--
-- select, delete
--

grant select, insert,         delete on user_confirmations             to group www;

grant insert                         on watch_list_staging_log         to group www;
grant update                         on watch_list_staging_log_id_seq  to group www;


grant select                         on watch_notice                   to group www;

grant select                         on graphs                         to group www;
grant select                         on daily_stats                    to group www;
grant select                         on daily_stats_data               to group www;

grant select                         on report_frequency               to group www;
grant select                         on reports                        to group www;
grant select, update, delete, insert on report_subscriptions           to group www;
grant select                         on report_log                     to group www;

--
-- no access
--

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
grant select, insert, update, delete on commit_log_ports_elements      to group commits;
grant select, insert, update, delete on commit_log_ports               to group commits;
grant select, insert                 on commit_log_ports_ignore        to group commits;

grant select, insert,         delete on commits_latest                 to group commits;
grant select, insert,         delete on latest_commits_ports           to group commits;
grant select                         on commits_recent                 to group commits;
grant select                         on commits_recent_ports           to group commits;

grant select                         on committer_notify               to group commits;

grant select, insert, update, delete on element                        to group commits;

grant select, update                 on element_id_seq                 to group commits;
grant select, insert, update, delete on element_revision               to group commits;

grant select, insert, update, delete on ports                          to group commits;
grant select, insert, update, delete on ports_categories               to group commits;

--
-- mostly for use only by ~/scripts/ports_categories-populate.pl
--
grant select                         on ports_active                   to group commits;
grant select, update                 on ports_id_seq                   to group commits;

grant select, insert, update, delete on system                         to group commits;
grant select, insert, update, delete on system_branch                  to group commits;
grant select, update                 on system_branch_id_seq           to group commits;
grant select, insert, update, delete on system_branch_element_revision to group commits;

grant select                         on security_notice                to group commits;
grant select                         on security_notice_audit          to group commits;

grant select on users              to group commits;
grant select on watch_list         to group commits;
grant select on watch_list_element to group commits;

grant select, insert, update on watch_notice_log to group commits;
grant select, insert, update on watch_notice     to group commits;

grant insert                         on watch_notice_log               to group commits;
grant update                         on watch_notice_log_id_seq        to group commits;

grant ALL on ports_check           to group commits;
grant update on ports_check_id_seq to group commits;

grant select, insert, delete         on daily_refreshes                to group commits;

grant select                         on daily_stats                    to group commits;
grant insert                         on daily_stats_data               to group commits;
grant update                         on daily_stats_data_seq           to group commits;

grant select                         on report_frequency               to group commits;
grant select                         on reports                        to group commits;
grant select                         on report_subscriptions           to group commits;
grant select, insert                 on report_log                     to group commits;
grant update                         on report_log_id_seq              to group commits;
grant select                         on report_log_latest              to group commits;

--
-- the READING group only needs to read some things.
--

create group reading;
create user  reading with password 'Bifrost1718';

alter group reading add user reading;

grant select                         on report_subscriptions           to group reading;
grant select                         on users                          to group reading;


--
-- For statistics gathering
--
grant insert                         on page_load_detail               to group www;
grant update                         on page_load_detail_id_seq        to group www;
