CREATE TABLE forums(
  id                            int8 DEFAULT '0' NOT NULL PRIMARY KEY,
  name                          varchar(50) DEFAULT '' NOT NULL,
  active                        int2 DEFAULT 0 NOT NULL,
  description                   varchar(255) DEFAULT '' NOT NULL,
  folder                        char DEFAULT '0' NOT NULL,
  parent                        int8 DEFAULT 0 NOT NULL,
  display                       int8 DEFAULT 0 NOT NULL,
  table_name                    varchar(50) DEFAULT '' NOT NULL,
  moderation                    char DEFAULT 'n' NOT NULL,
  mod_email                     varchar(50) DEFAULT '' NOT NULL,
  mod_pass                      varchar(50) DEFAULT '' NOT NULL,
  email_list                    varchar(50) DEFAULT '' NOT NULL,
  email_return                  varchar(50) DEFAULT '' NOT NULL,
  check_dup                     int2 DEFAULT 0 NOT NULL,
  multi_level                   int2 DEFAULT 0 NOT NULL,
  collapse                      int2 DEFAULT 0 NOT NULL,
  flat                          int2 DEFAULT 0 NOT NULL,
  staff_host                    varchar(50) DEFAULT '' NOT NULL,
  lang                          varchar(50) DEFAULT '' NOT NULL,
  html                          varchar(40) DEFAULT 'N' NOT NULL,
  table_width                   varchar(4) DEFAULT '' NOT NULL,
  table_header_color            varchar(7) DEFAULT '' NOT NULL,
  table_header_font_color       varchar(7) DEFAULT '' NOT NULL,
  table_body_color_1            varchar(7) DEFAULT '' NOT NULL,
  table_body_color_2            varchar(7) DEFAULT '' NOT NULL,
  table_body_font_color_1       varchar(7) DEFAULT '' NOT NULL,
  table_body_font_color_2       varchar(7) DEFAULT '' NOT NULL,
  nav_color                     varchar(7) DEFAULT '' NOT NULL,
  nav_font_color                varchar(7) DEFAULT '' NOT NULL
);

CREATE INDEX forums_name ON forums(name);
CREATE INDEX forums_active ON forums(active);
CREATE INDEX forums_parent ON forums(parent);
