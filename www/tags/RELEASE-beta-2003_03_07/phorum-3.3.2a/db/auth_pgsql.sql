CREATE TABLE auth_members(
  name                          varchar(150) DEFAULT '' NOT NULL,
  pass                          varchar(100) DEFAULT '' NOT NULL,
  forum_id                      int8 DEFAULT '0' NOT NULL
);

CREATE UNIQUE INDEX auth_members_pri_key ON auth_members(name, pass);
