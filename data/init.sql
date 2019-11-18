CREATE TABLE users (
  id integer primary key,
  first_name text,
  last_name text,
  settlement_id integer,
  user_id integer
);

CREATE TABLE wall_get (
  id integer primary key,
  attachments text,
  date integer,
  from_id integer,
  owner_id integer,
  post_id integer,
  settlement_id integer,
  text text
);

CREATE TABLE wall_getcomments (
  id integer primary key,
  attachments text,
  comment_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  parent_comment_id integer,
  post_id integer,
  settlement_id integer,
  text text
);
